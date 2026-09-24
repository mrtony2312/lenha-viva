<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class GoogleMerchantApiException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly bool $retryable = false,
        public readonly ?array $payload = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function fromResponse(Response $response): self
    {
        $json = $response->json();
        $message = is_array($json)
            ? (string) data_get($json, 'error.message', $response->body())
            : $response->body();

        $status = $response->status();
        $retryable = in_array($status, [429, 500, 502, 503, 504], true);

        return new self(
            self::sanitize($message !== '' ? $message : 'Google Merchant API error '.$status),
            $status,
            $retryable,
            is_array($json) ? self::redact($json) : null,
        );
    }

    public static function sanitize(string $message): string
    {
        return (string) preg_replace(
            '/(Bearer\s+)[A-Za-z0-9._\-]+|(refresh_token|access_token|client_secret)=[^\s&]+/i',
            '$1[REDACTED]',
            $message
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function redact(array $data): array
    {
        $sensitive = ['access_token', 'refresh_token', 'client_secret', 'private_key', 'id_token'];

        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $sensitive, true)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = self::redact($value);
            }
        }

        return $data;
    }
}
