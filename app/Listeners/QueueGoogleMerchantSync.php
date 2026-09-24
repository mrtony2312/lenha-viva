<?php

namespace App\Listeners;

use App\Events\CatalogProductChanged;
use App\Jobs\SyncProductToGoogleMerchant;
use Illuminate\Support\Facades\Log;

class QueueGoogleMerchantSync
{
    public function handle(CatalogProductChanged $event): void
    {
        if (! filled(config('merchant.account_id')) || ! filled(config('merchant.data_source_id'))) {
            Log::debug('merchant.event.skipped', [
                'product_id' => $event->productId,
                'reason' => 'not_configured',
            ]);

            return;
        }

        SyncProductToGoogleMerchant::dispatch($event->productId, $event->action)
            ->onQueue((string) config('merchant.queue', 'default'));
    }
}
