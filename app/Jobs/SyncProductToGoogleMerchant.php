<?php

namespace App\Jobs;

use App\Exceptions\GoogleMerchantApiException;
use App\Models\CatalogProduct;
use App\Services\GoogleMerchantService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncProductToGoogleMerchant implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [30, 90, 180, 300];

    public int $uniqueFor = 120;

    public function __construct(
        public readonly int $productId,
        public readonly string $action = 'sync',
    ) {
        $this->onQueue((string) config('merchant.queue', 'default'));
    }

    public function uniqueId(): string
    {
        return 'merchant-sync-'.$this->productId.'-'.$this->action;
    }

    public function handle(GoogleMerchantService $merchant): void
    {
        $product = CatalogProduct::find($this->productId);

        if ($product === null) {
            Log::warning('merchant.job.product_missing', ['product_id' => $this->productId]);

            return;
        }

        $result = match ($this->action) {
            'delete' => $merchant->deleteProduct($product),
            'update' => $merchant->updateProduct($product),
            default => $merchant->syncProduct($product),
        };

        if (! ($result['ok'] ?? false)) {
            $error = (string) ($result['error'] ?? 'sync failed');

            // Permanent client errors: do not retry forever.
            if (preg_match('/\b(400|401|403)\b/', $error)) {
                Log::error('merchant.job.permanent_failure', [
                    'product_id' => $this->productId,
                    'offer_id' => $result['offer_id'] ?? null,
                    'error' => $error,
                ]);

                return;
            }

            throw new GoogleMerchantApiException($error, 0, true);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('merchant.job.failed', [
            'product_id' => $this->productId,
            'action' => $this->action,
            'error' => $exception
                ? GoogleMerchantApiException::sanitize($exception->getMessage())
                : 'unknown',
        ]);
    }
}
