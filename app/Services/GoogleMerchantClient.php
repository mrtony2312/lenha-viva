<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * REST client for Merchant API v1 (not the legacy Content API for Shopping).
 *
 * Auth: service account JWT, scope https://www.googleapis.com/auth/content
 * Writes only work on an API-type data source.
 */
class GoogleMerchantClient
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const SCOPE = 'https://www.googleapis.com/auth/content';
    private const PRODUCTS = 'https://merchantapi.googleapis.com/products/v1';
    private const DATASOURCES = 'https://merchantapi.googleapis.com/datasources/v1';

    public function configured(): bool
    {
        return filled(config('merchant.account_id'))
            && is_readable((string) config('merchant.credentials'));
    }

    public function dataSourceConfigured(): bool
    {
        return $this->configured() && filled(config('merchant.data_source_id'));
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
     * @param  array<string, mixed>  $productInput
     * @return array<string, mixed>
     */
    public function insertProduct(array $productInput): array
    {
        $url = sprintf(
            '%s/%s/productInputs:insert',
            self::PRODUCTS,
            $this->accountName()
        );

        return $this->request('post', $url, $productInput, [
            'dataSource' => $this->dataSourceName(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getProduct(string $offerId): array
    {
        $productId = sprintf(
            '%s~%s~%s',
            config('merchant.content_language', 'pt'),
            config('merchant.feed_label', 'PT'),
            $offerId
        );

        return $this->request('get', self::PRODUCTS.'/'.$this->accountName().'/products/'.$productId);
    }

    public function deleteProduct(string $offerId): void
    {
        $productInputId = sprintf(
            '%s~%s~%s',
            config('merchant.content_language', 'pt'),
            config('merchant.feed_label', 'PT'),
            $offerId
        );

        $this->request('delete', self::PRODUCTS.'/'.$this->accountName().'/productInputs/'.$productInputId, null, [
            'dataSource' => $this->dataSourceName(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function listDataSources(): array
    {
        return $this->request('get', self::DATASOURCES.'/'.$this->accountName().'/dataSources');
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, string>  $query
     * @return array<string, mixed>
     */
    private function request(string $method, string $url, ?array $body = null, array $query = []): array
    {
        $pending = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(30);

        $queryString = $query === [] ? '' : '?'.http_build_query($query);

        $response = match ($method) {
            'get' => $pending->get($url.$queryString),
            'delete' => $pending->delete($url.$queryString),
            default => $pending->asJson()->post($url.$queryString, $body ?? []),
        };

        $response->throw();

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    private function accessToken(): string
    {
        $cached = Cache::get('merchant.google.access_token');
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $credentials = $this->credentials();
        $now = time();
        $jwt = $this->jwt(
            (string) $credentials['client_email'],
            (string) $credentials['private_key'],
            $now
        );

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ])->throw()->json();

        $token = $response['access_token'] ?? null;
        $expires = (int) ($response['expires_in'] ?? 3600);

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Google OAuth did not return an access token.');
        }

        Cache::put('merchant.google.access_token', $token, max(60, $expires - 60));

        return $token;
    }

    /**
     * @return array{client_email: string, private_key: string}
     */
    private function credentials(): array
    {
        $path = (string) config('merchant.credentials');

        if (! is_readable($path)) {
            throw new RuntimeException('Service account JSON not readable at '.$path);
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
