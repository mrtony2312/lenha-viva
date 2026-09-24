<?php

namespace App\Services;

use App\Exceptions\GoogleMerchantApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Low-level REST client for Google Merchant API products/v1 + datasources/v1.
 *
 * Auth (in order):
 * 1. OAuth 2.0 refresh token (GOOGLE_CLIENT_ID / SECRET / REFRESH_TOKEN)
 * 2. Service-account JWT (GOOGLE_MERCHANT_CREDENTIALS JSON)
 *
 * Never logs tokens or secrets.
 */
class GoogleMerchantClient
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const SCOPE = 'https://www.googleapis.com/auth/content';
    private const PRODUCTS = 'https://merchantapi.googleapis.com/products/v1';
    private const DATASOURCES = 'https://merchantapi.googleapis.com/datasources/v1';

    private const MAX_ATTEMPTS = 4;

    public function configured(): bool
    {
        if (! filled(config('merchant.account_id'))) {
            return false;
        }

        return $this->oauthConfigured() || $this->serviceAccountConfigured();
    }

    public function oauthConfigured(): bool
    {
        return filled(config('merchant.oauth.client_id'))
            && filled(config('merchant.oauth.client_secret'))
            && filled(config('merchant.oauth.refresh_token'));
    }

    public function serviceAccountConfigured(): bool
    {
        $path = (string) config('merchant.credentials');

        return $path !== '' && is_readable($path);
    }

    public function dataSourceConfigured(): bool
    {
        return $this->configured() && filled(config('merchant.data_source_id'));
    }

    public function authMode(): string
    {
        if ($this->oauthConfigured()) {
            return 'oauth';
        }

        if ($this->serviceAccountConfigured()) {
            return 'service_account';
        }

        return 'none';
    }

    public function accountName(): string
    {
        $id = config('merchant.account_id');

        if (! filled($id)) {
            throw new RuntimeException('GOOGLE_MERCHANT_ACCOUNT_ID is missing.');
        }

        return 'accounts/'.$id;
    }

    public function dataSourceName(): string
    {
        $id = config('merchant.data_source_id');

        if (! filled($id)) {
            throw new RuntimeException('GOOGLE_MERCHANT_DATA_SOURCE_ID is missing. Run php artisan merchant:datasource');
        }

        return $this->accountName().'/dataSources/'.$id;
    }

    /**
     * @return array<string, mixed>
     */
    public function createPrimaryDataSource(string $displayName = 'Naturalenha API'): array
    {
        return $this->request(
            'post',
            self::DATASOURCES.'/'.$this->accountName().'/dataSources',
            [
                'displayName' => $displayName,
                'primaryProductDataSource' => [
                    'countries' => [(string) config('merchant.target_country', 'PT')],
                ],
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function listDataSources(): array
    {
        return $this->request('get', self::DATASOURCES.'/'.$this->accountName().'/dataSources');
    }

    /**
     * Find an existing primary API data source by display name, or return first primary.
     *
     * @return array<string, mixed>|null
     */
    public function findPrimaryApiDataSource(?string $displayName = null): ?array
    {
        $payload = $this->listDataSources();
        $sources = collect($payload['dataSources'] ?? []);

        if ($displayName) {
            $match = $sources->first(function (array $source) use ($displayName) {
                return ($source['displayName'] ?? '') === $displayName
                    && isset($source['primaryProductDataSource']);
            });

            if (is_array($match)) {
                return $match;
            }
        }

        $primary = $sources->first(fn (array $source) => isset($source['primaryProductDataSource']));

        return is_array($primary) ? $primary : null;
    }

    /**
     * Insert or replace a product input (upsert semantics).
     *
     * @param  array<string, mixed>  $productInput
     * @return array<string, mixed>
     */
    public function insertProduct(array $productInput): array
    {
        $url = sprintf('%s/%s/productInputs:insert', self::PRODUCTS, $this->accountName());

        return $this->request('post', $url, $productInput, [
            'dataSource' => $this->dataSourceName(),
        ]);
    }

    /**
     * Partial update of an existing product input.
     *
     * @param  array<string, mixed>  $productInput
     * @return array<string, mixed>
     */
    public function updateProduct(array $productInput, ?string $updateMask = null): array
    {
        $offerId = (string) ($productInput['offerId'] ?? '');
        $name = $this->productInputResourceName($offerId);
        $body = array_merge($productInput, ['name' => $name]);

        $query = ['dataSource' => $this->dataSourceName()];
        if (filled($updateMask)) {
            $query['updateMask'] = $updateMask;
        }

        return $this->request('patch', self::PRODUCTS.'/'.$name, $body, $query);
    }

    /**
     * @return array<string, mixed>
     */
    public function getProduct(string $offerId): array
    {
        $productId = $this->productIdSegment($offerId);

        return $this->request('get', self::PRODUCTS.'/'.$this->accountName().'/products/'.$productId);
    }

    public function deleteProduct(string $offerId): void
    {
        $productInputId = $this->productIdSegment($offerId);

        $this->request('delete', self::PRODUCTS.'/'.$this->accountName().'/productInputs/'.$productInputId, null, [
            'dataSource' => $this->dataSourceName(),
        ]);
    }

    public function productInputResourceName(string $offerId): string
    {
        return $this->accountName().'/productInputs/'.$this->productIdSegment($offerId);
    }

    public function productIdSegment(string $offerId): string
    {
        $plain = sprintf(
            '%s~%s~%s',
            config('merchant.content_language', 'pt'),
            config('merchant.feed_label', 'PT'),
            $offerId
        );

        // Encode when offerId contains reserved characters.
        if (preg_match('/[~\/%]/', $offerId)) {
            return rtrim(strtr(base64_encode($plain), '+/', '-_'), '=');
        }

        return $plain;
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, string>  $query
     * @return array<string, mixed>
     */
    private function request(string $method, string $url, ?array $body = null, array $query = []): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < self::MAX_ATTEMPTS) {
            $attempt++;

            try {
                return $this->sendOnce($method, $url, $body, $query);
            } catch (GoogleMerchantApiException $e) {
                $lastException = $e;

                if (! $e->retryable || $attempt >= self::MAX_ATTEMPTS) {
                    throw $e;
                }

                $sleepMs = (int) (min(8000, 250 * (2 ** ($attempt - 1))) + random_int(0, 200));
                Log::warning('merchant.api.retry', [
                    'attempt' => $attempt,
                    'status' => $e->statusCode,
                    'sleep_ms' => $sleepMs,
                    'message' => $e->getMessage(),
                ]);
                usleep($sleepMs * 1000);
            } catch (ConnectionException $e) {
                $lastException = new GoogleMerchantApiException(
                    GoogleMerchantApiException::sanitize($e->getMessage()),
                    0,
                    true
                );

                if ($attempt >= self::MAX_ATTEMPTS) {
                    throw $lastException;
                }

                usleep((int) (min(8000, 250 * (2 ** ($attempt - 1))) * 1000));
            }
        }

        throw $lastException ?? new GoogleMerchantApiException('Merchant API request failed.');
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, string>  $query
     * @return array<string, mixed>
     */
    private function sendOnce(string $method, string $url, ?array $body, array $query): array
    {
        $pending = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(45);

        $queryString = $query === [] ? '' : '?'.http_build_query($query);

        try {
            $response = match (strtolower($method)) {
                'get' => $pending->get($url.$queryString),
                'delete' => $pending->delete($url.$queryString),
                'patch' => $pending->asJson()->patch($url.$queryString, $body ?? []),
                default => $pending->asJson()->post($url.$queryString, $body ?? []),
            };
        } catch (RequestException $e) {
            if ($e->response) {
                throw GoogleMerchantApiException::fromResponse($e->response);
            }

            throw new GoogleMerchantApiException(
                GoogleMerchantApiException::sanitize($e->getMessage()),
                0,
                true
            );
        }

        if ($response->failed()) {
            throw GoogleMerchantApiException::fromResponse($response);
        }

        // DELETE may return empty body.
        if ($response->body() === '' || $response->body() === 'null') {
            return [];
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    private function accessToken(): string
    {
        $cacheKey = 'merchant.google.access_token.'.$this->authMode();
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $token = $this->oauthConfigured()
            ? $this->fetchOAuthAccessToken()
            : $this->fetchServiceAccountAccessToken();

        return $token;
    }

    private function fetchOAuthAccessToken(): string
    {
        $response = Http::asForm()
            ->timeout(30)
            ->post(self::TOKEN_URL, [
                'grant_type' => 'refresh_token',
                'client_id' => (string) config('merchant.oauth.client_id'),
                'client_secret' => (string) config('merchant.oauth.client_secret'),
                'refresh_token' => (string) config('merchant.oauth.refresh_token'),
            ]);

        if ($response->failed()) {
            throw new GoogleMerchantApiException(
                'OAuth token refresh failed (HTTP '.$response->status().'). Check GOOGLE_CLIENT_ID / SECRET / REFRESH_TOKEN.',
                $response->status(),
                in_array($response->status(), [429, 500, 502, 503, 504], true)
            );
        }

        $json = $response->json();
        $token = $json['access_token'] ?? null;
        $expires = (int) ($json['expires_in'] ?? 3600);

        if (! is_string($token) || $token === '') {
            throw new GoogleMerchantApiException('Google OAuth did not return an access token.');
        }

        Cache::put(
            'merchant.google.access_token.oauth',
            $token,
            max(60, $expires - 60)
        );

        return $token;
    }

    private function fetchServiceAccountAccessToken(): string
    {
        $credentials = $this->credentials();
        $now = time();
        $jwt = $this->jwt(
            (string) $credentials['client_email'],
            (string) $credentials['private_key'],
            $now
        );

        $response = Http::asForm()->timeout(30)->post(self::TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if ($response->failed()) {
            throw new GoogleMerchantApiException(
                'Service-account token exchange failed (HTTP '.$response->status().').',
                $response->status(),
                in_array($response->status(), [429, 500, 502, 503, 504], true)
            );
        }

        $json = $response->json();
        $token = $json['access_token'] ?? null;
        $expires = (int) ($json['expires_in'] ?? 3600);

        if (! is_string($token) || $token === '') {
            throw new GoogleMerchantApiException('Google OAuth did not return an access token.');
        }

        Cache::put(
            'merchant.google.access_token.service_account',
            $token,
            max(60, $expires - 60)
        );

        return $token;
    }

    /**
     * @return array{client_email: string, private_key: string}
     */
    private function credentials(): array
    {
        $path = (string) config('merchant.credentials');

        if (! is_readable($path)) {
            throw new RuntimeException('Service account JSON not readable. Set GOOGLE_MERCHANT_CREDENTIALS or OAuth env vars.');
        }

        $json = json_decode((string) file_get_contents($path), true);

        if (! is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            throw new RuntimeException('Invalid service account JSON: need client_email and private_key.');
        }

        return $json;
    }

    private function jwt(string $email, string $privateKey, int $now): string
    {
        $header = $this->b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->b64url(json_encode([
            'iss' => $email,
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URL,
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        $unsigned = $header.'.'.$payload;
        $ok = openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $ok || ! is_string($signature)) {
            throw new RuntimeException('Could not sign the Google service-account JWT.');
        }

        return $unsigned.'.'.$this->b64url($signature);
    }

    private function b64url(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
}
