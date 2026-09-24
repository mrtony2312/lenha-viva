<?php

namespace App\Console\Commands;

use App\Models\CatalogProduct;
use App\Services\GoogleMerchantService;
use Illuminate\Console\Command;
use Throwable;

class MerchantSyncProduct extends Command
{
    protected $signature = 'merchant:sync-product
        {id : Catalogue product id (e.g. 5625)}
        {--update : Use productInputs.patch}
        {--delete : Delete product input from Merchant}
        {--status : Only fetch processed product status}';

    protected $description = 'Sync, update, delete or inspect a single Naturalenha product in Google Merchant';

    public function handle(GoogleMerchantService $merchant): int
    {
        $id = (int) $this->argument('id');
        $product = CatalogProduct::find($id);

        if ($product === null) {
            $this->error("Product {$id} not found in config/loja_products.php");

            return self::FAILURE;
        }

        $this->line('Product: '.$product->title());
        $this->line('Offer ID: '.$product->offerId());
        $this->line('URL: '.route('product.show', ['slug' => $product->toArray()['slug'] ?? '']));

        try {
            if ($this->option('status')) {
                $status = $merchant->getProduct($product);
                $this->line(json_encode([
                    'name' => $status['name'] ?? null,
                    'offerId' => $status['offerId'] ?? $product->offerId(),
                    'destinationStatuses' => data_get($status, 'productStatus.destinationStatuses'),
                    'itemLevelIssues' => data_get($status, 'productStatus.itemLevelIssues'),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

                return self::SUCCESS;
            }

            if ($this->option('delete')) {
                $result = $merchant->deleteProduct($product);
            } elseif ($this->option('update')) {
                $result = $merchant->updateProduct($product);
            } else {
                $result = $merchant->syncProduct($product);
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($result['ok'] ?? false) {
            $this->info('OK '.$result['offer_id'].' ('.$result['duration_ms'].' ms)');
            if (! empty($result['status'])) {
                $this->line(json_encode($result['status'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }

            return self::SUCCESS;
        }

        $this->error('FAIL '.$result['offer_id'].' — '.($result['error'] ?? 'unknown'));

        return self::FAILURE;
    }
}
