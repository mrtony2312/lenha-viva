<?php

namespace App\Console\Commands;

use App\Services\GoogleMerchantClient;
use App\Services\GoogleMerchantService;
use Illuminate\Console\Command;
use Throwable;

class MerchantProductStatus extends Command
{
    protected $signature = 'merchant:status
        {offerId? : Optional offer id (lv-5625) — if set, fetch that product}
        {--delete : Delete this product input from the API data source}
        {--health : API health check only}';

    protected $description = 'Merchant API connection status, sync counters, or a single product';

    public function handle(GoogleMerchantService $merchant, GoogleMerchantClient $client): int
    {
        if ($this->option('health') || (! $this->argument('offerId') && ! $this->option('delete'))) {
            $health = $merchant->healthCheck();
            $this->table(['Key', 'Value'], collect($health)->map(fn ($v, $k) => [
                $k,
                is_scalar($v) || $v === null ? (string) ($v ?? 'null') : json_encode($v),
            ])->values()->all());

            try {
                $snap = $merchant->statusSnapshot();
                $this->newLine();
                $this->info('Catalogue / tracking');
                $this->table(['Metric', 'Count'], [
                    ['Laravel eligible', $snap['laravel_eligible']],
                    ['Tracked rows', $snap['tracked']],
                    ['Synced', $snap['synced']],
                    ['Errors', $snap['error']],
                    ['Deleted', $snap['deleted']],
                ]);

                if (! empty($snap['last_sync'])) {
                    $this->line('Last full sync: '.json_encode($snap['last_sync'], JSON_UNESCAPED_UNICODE));
                }

                if ($snap['recent_errors'] !== []) {
                    $this->warn('Recent errors:');
                    foreach ($snap['recent_errors'] as $row) {
                        $this->line('  #'.$row['product_id'].' '.$row['offer_id'].' — '.$row['last_error']);
                    }
                }
            } catch (Throwable $e) {
                $this->warn('Tracking table unavailable (run migrations): '.$e->getMessage());
            }

            if ($this->argument('offerId') === null) {
                return ($health['api_connection'] ?? false) ? self::SUCCESS : self::FAILURE;
            }
        }

        $offerId = (string) $this->argument('offerId');

        if (! $client->configured()) {
            $this->error('Merchant API is not configured.');

            return self::FAILURE;
        }

        try {
            if ($this->option('delete')) {
                $client->deleteProduct($offerId);
                $this->info('Deleted product input '.$offerId);

                return self::SUCCESS;
            }

            $product = $client->getProduct($offerId);
            $status = data_get($product, 'productStatus', []);
            $this->line(json_encode([
                'name' => $product['name'] ?? null,
                'offerId' => $product['offerId'] ?? $offerId,
                'destinationStatuses' => $status['destinationStatuses'] ?? null,
                'itemLevelIssues' => $status['itemLevelIssues'] ?? null,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
