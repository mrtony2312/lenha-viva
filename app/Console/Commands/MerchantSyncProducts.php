<?php

namespace App\Console\Commands;

use App\Services\GoogleMerchantClient;
use App\Support\MerchantCatalog;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Throwable;

class MerchantSyncProducts extends Command
{
    protected $signature = 'merchant:sync
        {--dry-run : Print payloads without calling Google}
        {--limit= : Max number of products to send}
        {--id= : Sync a single catalogue id}';

    protected $description = 'Insert or replace Naturalenha products in Google Merchant Center via the Merchant API';

    public function handle(GoogleMerchantClient $merchant): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! $merchant->dataSourceConfigured()) {
            $this->error('Configure GOOGLE_MERCHANT_ACCOUNT_ID, GOOGLE_MERCHANT_DATA_SOURCE_ID and the service-account JSON.');
            $this->line('Create the API data source with: php artisan merchant:datasource');

            return self::FAILURE;
        }

        $storeUrl = (string) config('app.url');
        if (! $dryRun && ! str_starts_with($storeUrl, 'https://')) {
            $this->error('APP_URL must be the public HTTPS shop (e.g. https://naturalenha.com). Google rejects localhost landing pages and images.');

            return self::FAILURE;
        }

        $products = MerchantCatalog::eligibleProducts();
        $onlyId = $this->option('id');
        if (filled($onlyId)) {
            $products = $products->where('id', (int) $onlyId)->values();
        }

        $limit = $this->option('limit');
        if (filled($limit)) {
            $products = $products->take((int) $limit);
        }

        if ($products->isEmpty()) {
            $this->warn('No eligible products (need slug, image and a price > 0).');

            return self::SUCCESS;
        }

        $ok = 0;
        $failed = 0;

        foreach ($products as $product) {
            $payload = MerchantCatalog::toProductInput($product);
            if ($payload === null) {
                continue;
            }

            $label = $payload['offerId'].' — '.$payload['productAttributes']['title'];

            if ($dryRun) {
                $this->line('[dry-run] '.$label);
                $ok++;

                continue;
            }

            try {
                $response = $merchant->insertProduct($payload);
                $this->info('OK '.$label);
                $this->line('  '.$response['product'] ?? ($response['name'] ?? ''));
                $ok++;
                usleep(150_000);
            } catch (RequestException $e) {
                $failed++;
                $this->error('FAIL '.$label);
                $this->line('  '.$this->apiError($e));
            } catch (Throwable $e) {
                $failed++;
                $this->error('FAIL '.$label.' — '.$e->getMessage());
            }
        }

        $this->newLine();
        $this->line("Synced: {$ok} · failed: {$failed} · total: {$products->count()}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function apiError(RequestException $e): string
    {
        $json = $e->response?->json();
        if (is_array($json)) {
            return (string) data_get($json, 'error.message', $e->response->body());
        }

        return $e->getMessage();
    }
}
