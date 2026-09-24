<?php

namespace App\Events;

use App\Models\CatalogProduct;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a catalogue product should be pushed to Google Merchant.
 * Catalogue lives in config — dispatch this after editing loja_products or via artisan.
 */
class CatalogProductChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $productId,
        public readonly string $action = 'sync', // sync|update|delete
    ) {}

    public function product(): ?CatalogProduct
    {
        return CatalogProduct::find($this->productId);
    }
}
