<?php

namespace App\Services;

use App\Exceptions\GoogleMerchantApiException;
use App\Models\CatalogProduct;
use App\Models\GoogleMerchantProduct;
use App\Support\MerchantCatalog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * High-level Google Merchant API orchestration for Naturalenha.
 *
 * XML feed remains available at /feed/google-merchant.xml until cutover.
 */
class GoogleMerchantService
{
    public function __construct(
        private readonly GoogleMerchantClient $client
    ) {}

    public function healthCheck(): array
    {
        $result = [
            'configured' => $this->client->configured(),
            'auth_mode' => $this->client->authMode(),
            'account_id' => config('merchant.account_id') ? 'set' : null,
            'data_source_id' => config('merchant.data_source_id') ? 'set' : null,
            'data_source_name' => null,
            'api_connection' => false,
            'message' => null,
        ];

        if (! $this->client->configured()) {
            $result['message'] = 'Missing GOOGLE_MERCHANT_ACCOUNT_ID and OAuth (GOOGLE_CLIENT_ID/SECRET/REFRESH_TOKEN) or service-account JSON.';

            return $result;
        }

        try {
            $sources = $this->client->listDataSources();
            $result['api_connection'] = true;
            $result['data_sources_count'] = count($sources['dataSources'] ?? []);

            if ($this->client->dataSourceConfigured()) {
                $result['data_source_name'] = $this->client->dataSourceName();
            }

            $result['message'] = 'OK';
        } catch (Throwable $e) {
            $result['message'] = GoogleMerchantApiException::sanitize($e->getMessage());
        }

        return $result;
    }

    /**
     * Ensure a primary API data source exists; reuse if found. Does not call list on every sync.
     *
     * @return array{name: string, id: string, created: bool}
     */
    public function ensureDataSource(string $displayName = 'Naturalenha API'): array
    {
        if ($this->client->dataSourceConfigured()) {
            return [
                'name' => $this->client->dataSourceName(),
                'id' => (string) config('merchant.data_source_id'),
                'created' => false,
            ];
        }

        $existing = $this->client->findPrimaryApiDataSource($displayName);
        if ($existing === null) {
            $existing = $this->client->findPrimaryApiDataSource(null);
        }

        if (is_array($existing) && filled($existing['name'] ?? null)) {
            $name = (string) $existing['name'];
            $id = preg_replace('#^.*/dataSources/#', '', $name);

            return ['name' => $name, 'id' => (string) $id, 'created' => false];
        }

        $created = $this->client->createPrimaryDataSource($displayName);
        $name = (string) ($created['name'] ?? '');
        $id = preg_replace('#^.*/dataSources/#', '', $name);

        return ['name' => $name, 'id' => (string) $id, 'created' => true];
    }

    /**
     * Insert (or replace) a product via productInputs.insert.
     *
     * @return array{ok: bool, offer_id: string, response?: array<string, mixed>, status?: array<string, mixed>|null, error?: string, duration_ms: int}
     */
    public function syncProduct(CatalogProduct $product): array
    {
        return $this->runProductOperation($product, 'insert', function (array $payload) {
            return $this->client->insertProduct($payload);
        });
    }

    /**
     * Update via productInputs.patch; falls back to insert if not found (404).
     *
     * @return array{ok: bool, offer_id: string, response?: array<string, mixed>, status?: array<string, mixed>|null, error?: string, duration_ms: int}
     */
    public function updateProduct(CatalogProduct $product): array
    {
        return $this->runProductOperation($product, 'update', function (array $payload) {
            try {
                return $this->client->updateProduct($payload);
            } catch (GoogleMerchantApiException $e) {
                if ($e->statusCode === 404) {
                    return $this->client->insertProduct($payload);
                }

                throw $e;
            }
        });
    }

    /**
     * @return array{ok: bool, offer_id: string, error?: string, duration_ms: int}
     */
    public function deleteProduct(CatalogProduct $product): array
    {
        $this->assertReadyForWrites();
        $started = hrtime(true);
        $offerId = $product->offerId();

        try {
            $this->client->deleteProduct($offerId);
            $duration = (int) ((hrtime(true) - $started) / 1_000_000);

            $this->persistTracking($product, [
                'sync_status' => GoogleMerchantProduct::STATUS_DELETED,
                'last_error' => null,
                'last_issues' => null,
                'google_product_name' => null,
                'operation' => 'delete',
                'duration_ms' => $duration,
            ]);

            Log::info('merchant.sync.delete', [
                'product_id' => $product->id(),
                'offer_id' => $offerId,
                'duration_ms' => $duration,
            ]);

            return ['ok' => true, 'offer_id' => $offerId, 'duration_ms' => $duration];
        } catch (Throwable $e) {
            $duration = (int) ((hrtime(true) - $started) / 1_000_000);
            $message = GoogleMerchantApiException::sanitize($e->getMessage());

            $this->persistTracking($product, [
                'sync_status' => GoogleMerchantProduct::STATUS_ERROR,
                'last_error' => $message,
                'operation' => 'delete',
                'duration_ms' => $duration,
            ]);

            return ['ok' => false, 'offer_id' => $offerId, 'error' => $message, 'duration_ms' => $duration];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getProduct(CatalogProduct $product): array
    {
        return $this->client->getProduct($product->offerId());
    }

    /**
     * Full catalogue sync in chunks.
     *
     * @return array{detected: int, submitted: int, success: int, failed: int, errors: list<array{product_id: int, offer_id: string, error: string}>}
     */
    public function syncAllProducts(int $chunkSize = 25, ?int $limit = null, bool $useUpdate = false): array
    {
        $this->assertReadyForWrites();
        $this->assertPublicHttps();

        $summary = [
            'detected' => 0,
            'submitted' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $processed = 0;

        CatalogProduct::chunkEligible($chunkSize, function ($chunk) use (&$summary, &$processed, $limit, $useUpdate) {
            foreach ($chunk as $product) {
                /** @var CatalogProduct $product */
                if ($limit !== null && $processed >= $limit) {
                    return false;
                }

                $summary['detected']++;
                $processed++;
                $summary['submitted']++;

                $result = $useUpdate
                    ? $this->updateProduct($product)
                    : $this->syncProduct($product);

                if ($result['ok']) {
                    $summary['success']++;
                } else {
                    $summary['failed']++;
                    $summary['errors'][] = [
                        'product_id' => $product->id(),
                        'offer_id' => $result['offer_id'],
                        'error' => $result['error'] ?? 'unknown',
                    ];
                }

                usleep(120_000);
            }

            return true;
        });

        Cache::put('merchant.last_full_sync', [
            'at' => now()->toIso8601String(),
            'summary' => [
                'detected' => $summary['detected'],
                'submitted' => $summary['submitted'],
                'success' => $summary['success'],
                'failed' => $summary['failed'],
            ],
        ], now()->addDays(30));

        Log::info('merchant.sync.all', $summary);

        return $summary;
    }

    /**
     * Fetch processed product status after insert (with short retries).
     *
     * @return array<string, mixed>|null
     */
    public function fetchProcessedStatus(string $offerId, int $attempts = 3, int $delayMs = 1500): ?array
    {
        for ($i = 0; $i < $attempts; $i++) {
            try {
                $product = $this->client->getProduct($offerId);

                return [
                    'name' => $product['name'] ?? null,
                    'offerId' => $product['offerId'] ?? $offerId,
                    'destinationStatuses' => data_get($product, 'productStatus.destinationStatuses'),
                    'itemLevelIssues' => data_get($product, 'productStatus.itemLevelIssues'),
                ];
            } catch (GoogleMerchantApiException $e) {
                if ($e->statusCode === 404 && $i < $attempts - 1) {
                    usleep($delayMs * 1000);

                    continue;
                }

                if ($e->statusCode === 404) {
                    return null;
                }

                throw $e;
            }
        }

        return null;
    }

    /**
     * @return array{laravel_eligible: int, tracked: int, synced: int, error: int, deleted: int, last_sync: mixed, recent_errors: list<array<string, mixed>>}
     */
    public function statusSnapshot(): array
    {
        $tracked = GoogleMerchantProduct::query();

        return [
            'laravel_eligible' => MerchantCatalog::eligibleProducts()->count(),
            'tracked' => (clone $tracked)->count(),
            'synced' => (clone $tracked)->where('sync_status', GoogleMerchantProduct::STATUS_SYNCED)->count(),
            'error' => (clone $tracked)->where('sync_status', GoogleMerchantProduct::STATUS_ERROR)->count(),
            'deleted' => (clone $tracked)->where('sync_status', GoogleMerchantProduct::STATUS_DELETED)->count(),
            'last_sync' => Cache::get('merchant.last_full_sync'),
            'recent_errors' => GoogleMerchantProduct::query()
                ->where('sync_status', GoogleMerchantProduct::STATUS_ERROR)
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get(['product_id', 'offer_id', 'last_error', 'updated_at'])
                ->toArray(),
        ];
    }

    /**
     * @param  callable(array<string, mixed>): array<string, mixed>  $operation
     * @return array{ok: bool, offer_id: string, response?: array<string, mixed>, status?: array<string, mixed>|null, error?: string, duration_ms: int}
     */
    private function runProductOperation(CatalogProduct $product, string $operation, callable $operationFn): array
    {
        $this->assertReadyForWrites();
        $this->assertPublicHttps();

        $started = hrtime(true);
        $offerId = $product->offerId();

        if (! $product->isEligible()) {
            $duration = (int) ((hrtime(true) - $started) / 1_000_000);
            $error = 'Product not eligible (slug, image ≥1, price > 0 required).';

            $this->persistTracking($product, [
                'sync_status' => GoogleMerchantProduct::STATUS_ERROR,
                'last_error' => $error,
                'operation' => $operation,
                'duration_ms' => $duration,
            ]);

            return ['ok' => false, 'offer_id' => $offerId, 'error' => $error, 'duration_ms' => $duration];
        }

        $payload = MerchantCatalog::toProductInput($product->toArray());
        if ($payload === null) {
            return [
                'ok' => false,
                'offer_id' => $offerId,
                'error' => 'Could not build Merchant product input.',
                'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000),
            ];
        }

        try {
            $response = $operationFn($payload);
            $status = null;

            try {
                $status = $this->fetchProcessedStatus($offerId, 2, 800);
            } catch (Throwable) {
                $status = null;
            }

            $duration = (int) ((hrtime(true) - $started) / 1_000_000);

            $this->persistTracking($product, [
                'sync_status' => GoogleMerchantProduct::STATUS_SYNCED,
                'google_product_name' => $response['name'] ?? ($response['product'] ?? null) ?? data_get($status, 'name'),
                'data_source_name' => $this->client->dataSourceName(),
                'last_error' => null,
                'last_issues' => data_get($status, 'itemLevelIssues'),
                'operation' => $operation,
                'duration_ms' => $duration,
            ]);

            Log::info('merchant.sync.'.$operation, [
                'product_id' => $product->id(),
                'offer_id' => $offerId,
                'duration_ms' => $duration,
                'has_issues' => ! empty(data_get($status, 'itemLevelIssues')),
            ]);

            return [
                'ok' => true,
                'offer_id' => $offerId,
                'response' => $response,
                'status' => $status,
                'duration_ms' => $duration,
            ];
        } catch (Throwable $e) {
            $duration = (int) ((hrtime(true) - $started) / 1_000_000);
            $message = GoogleMerchantApiException::sanitize($e->getMessage());

            $this->persistTracking($product, [
                'sync_status' => GoogleMerchantProduct::STATUS_ERROR,
                'last_error' => $message,
                'operation' => $operation,
                'duration_ms' => $duration,
            ]);

            Log::error('merchant.sync.'.$operation.'.failed', [
                'product_id' => $product->id(),
                'offer_id' => $offerId,
                'duration_ms' => $duration,
                'error' => $message,
            ]);

            return [
                'ok' => false,
                'offer_id' => $offerId,
                'error' => $message,
                'duration_ms' => $duration,
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function persistTracking(CatalogProduct $product, array $attrs): void
    {
        $row = GoogleMerchantProduct::query()->firstOrNew([
            'product_id' => $product->id(),
        ]);

        $row->offer_id = $product->offerId();
        $row->attempts = (int) $row->attempts + 1;
        $row->fill($attrs);

        if (($attrs['sync_status'] ?? null) === GoogleMerchantProduct::STATUS_SYNCED
            || ($attrs['sync_status'] ?? null) === GoogleMerchantProduct::STATUS_DELETED) {
            $row->last_synced_at = now();
        }

        $row->save();
    }

    private function assertReadyForWrites(): void
    {
        if (! $this->client->dataSourceConfigured()) {
            throw new RuntimeException(
                'Merchant API not ready. Set GOOGLE_MERCHANT_ACCOUNT_ID, GOOGLE_MERCHANT_DATA_SOURCE_ID and OAuth or service-account credentials.'
            );
        }
    }

    private function assertPublicHttps(): void
    {
        $url = (string) config('app.url');

        if (! str_starts_with($url, 'https://')) {
            throw new RuntimeException(
                'APP_URL must be the public HTTPS shop (https://naturalenha.com). Google rejects localhost product/image URLs.'
            );
        }
    }
}
