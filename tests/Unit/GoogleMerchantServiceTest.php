<?php

namespace Tests\Unit;

use App\Exceptions\GoogleMerchantApiException;
use App\Jobs\SyncProductToGoogleMerchant;
use App\Models\CatalogProduct;
use App\Services\GoogleMerchantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleMerchantServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'https://naturalenha.com',
            'merchant.account_id' => '123456789',
            'merchant.data_source_id' => '987654321',
            'merchant.oauth.client_id' => 'client.apps.googleusercontent.com',
            'merchant.oauth.client_secret' => 'secret',
            'merchant.oauth.refresh_token' => 'refresh-token-value',
        ]);
    }

    public function test_health_check_reports_api_connection(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'ya29.test',
                'expires_in' => 3600,
            ]),
            'merchantapi.googleapis.com/datasources/v1/*' => Http::response([
                'dataSources' => [
                    ['name' => 'accounts/123456789/dataSources/987654321', 'displayName' => 'Naturalenha API', 'primaryProductDataSource' => ['countries' => ['PT']]],
                ],
            ]),
        ]);

        $health = app(GoogleMerchantService::class)->healthCheck();

        $this->assertTrue($health['api_connection']);
        $this->assertSame('oauth', $health['auth_mode']);
        $this->assertSame('OK', $health['message']);
    }

    public function test_sync_product_inserts_and_tracks(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'ya29.test',
                'expires_in' => 3600,
            ]),
            'merchantapi.googleapis.com/products/v1/*/productInputs:insert*' => Http::response([
                'name' => 'accounts/123456789/productInputs/pt~PT~lv-5625',
                'offerId' => 'lv-5625',
            ]),
            'merchantapi.googleapis.com/products/v1/*/products/*' => Http::response([
                'name' => 'accounts/123456789/products/pt~PT~lv-5625',
                'offerId' => 'lv-5625',
                'productStatus' => ['itemLevelIssues' => []],
            ], 200),
        ]);

        $product = CatalogProduct::find(5625);
        $this->assertNotNull($product);

        $result = app(GoogleMerchantService::class)->syncProduct($product);

        $this->assertTrue($result['ok']);
        $this->assertSame('lv-5625', $result['offer_id']);
        $this->assertDatabaseHas('google_merchant_products', [
            'product_id' => 5625,
            'offer_id' => 'lv-5625',
            'sync_status' => 'synced',
        ]);
    }

    public function test_retryable_exception_flag_for_429(): void
    {
        $e = GoogleMerchantApiException::fromResponse(
            new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(429, [], json_encode(['error' => ['message' => 'Rate limited']]))
            )
        );

        $this->assertTrue($e->retryable);
        $this->assertSame(429, $e->statusCode);
    }

    public function test_job_unique_id_avoids_duplicates(): void
    {
        $job = new SyncProductToGoogleMerchant(5625, 'sync');
        $this->assertSame('merchant-sync-5625-sync', $job->uniqueId());
    }

    public function test_sanitize_redacts_tokens(): void
    {
        $msg = GoogleMerchantApiException::sanitize('Bearer ya29.secret-token-here failed');
        $this->assertStringNotContainsString('ya29.secret-token-here', $msg);
        $this->assertStringContainsString('[REDACTED]', $msg);
    }

    public function test_rejects_localhost_app_url(): void
    {
        config(['app.url' => 'http://localhost/lenha-viva']);

        $product = CatalogProduct::find(5625);
        $this->expectExceptionMessage('APP_URL must be the public HTTPS shop');
        app(GoogleMerchantService::class)->syncProduct($product);
    }
}
