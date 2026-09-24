<?php

namespace App\Console\Commands;

use App\Models\CatalogProduct;
use App\Services\GoogleMerchantService;
use Illuminate\Console\Command;
use Throwable;

class MerchantSyncProducts extends Command
{
    protected $signature = 'merchant:sync
        {--dry-run : Print payloads without calling Google}
        {--limit= : Max number of products to send}
        {--chunk=25 : Chunk size}
        {--update : Prefer productInputs.patch (falls back to insert)}';

    protected $description = 'Sync Naturalenha catalogue to Google Merchant API (productInputs.insert)';

    public function handle(GoogleMerchantService $merchant): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = filled($this->option('limit')) ? (int) $this->option('limit') : null;
        $chunk = max(1, (int) $this->option('chunk'));

        if ($dryRun) {
            $count = 0;
            CatalogProduct::chunkEligible($chunk, function ($products) use (&$count, $limit) {
                foreach ($products as $product) {
                    if ($limit !== null && $count >= $limit) {
                        return false;
                    }
                    $this->line('[dry-run] '.$product->offerId().' — '.$product->title());
                    $count++;
                }

                return true;
            });
            $this->info("Products detected: {$count}");

            return self::SUCCESS;
        }

        try {
            $summary = $merchant->syncAllProducts(
                chunkSize: $chunk,
                limit: $limit,
                useUpdate: (bool) $this->option('update'),
            );
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('Products detected: '.$summary['detected']);
        $this->line('Submitted: '.$summary['submitted']);
        $this->info('Success: '.$summary['success']);
        if ($summary['failed'] > 0) {
            $this->error('Failed: '.$summary['failed']);
            foreach (array_slice($summary['errors'], 0, 20) as $error) {
                $this->line('  #'.$error['product_id'].' '.$error['offer_id'].' — '.$error['error']);
            }
        }

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
