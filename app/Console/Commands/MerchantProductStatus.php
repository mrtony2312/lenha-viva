<?php

namespace App\Console\Commands;

use App\Services\GoogleMerchantClient;
use App\Support\MerchantCatalog;
use Illuminate\Console\Command;
use Throwable;

class MerchantProductStatus extends Command
{
    protected $signature = 'merchant:status
        {offerId? : Offer id (lv-5625). Defaults to the first catalogue product}
        {--delete : Delete this product input from the API data source}';

    protected $description = 'Get (or delete) a processed Merchant Center product';

    public function handle(GoogleMerchantClient $merchant): int
    {
        if (! $merchant->configured()) {
            $this->error('Merchant API is not configured.');

            return self::FAILURE;
        }

        $offerId = $this->argument('offerId');
        if (! filled($offerId)) {
            $first = MerchantCatalog::eligibleProducts()->first();
            $offerId = $first ? MerchantCatalog::offerId($first) : null;
        }

        if (! filled($offerId)) {
            $this->error('No offer id.');

            return self::FAILURE;
        }

        try {
            if ($this->option('delete')) {
                $merchant->deleteProduct((string) $offerId);
                $this->info('Deleted product input '.$offerId);

                return self::SUCCESS;
            }

            $product = $merchant->getProduct((string) $offerId);
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
