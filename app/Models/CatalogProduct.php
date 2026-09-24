<?php

namespace App\Models;

use App\Support\MerchantCatalog;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * Read-only product from config/loja_products.php (no Eloquent Product table).
 * Used as the stable domain object for Google Merchant sync.
 *
 * @implements Arrayable<string, mixed>
 */
class CatalogProduct implements Arrayable
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public readonly array $attributes
    ) {}

    public static function find(int|string $id): ?self
    {
        $id = (int) $id;
        $row = collect(config('loja_products', []))
            ->first(fn (array $product) => (int) ($product['id'] ?? 0) === $id);

        return $row ? new self($row) : null;
    }

    /**
     * @return Collection<int, self>
     */
    public static function allEligible(): Collection
    {
        return MerchantCatalog::eligibleProducts()
            ->map(fn (array $row) => new self($row))
            ->values();
    }

    /**
     * @return Collection<int, self>
     */
    public static function chunkEligible(int $size, callable $callback): void
    {
        MerchantCatalog::eligibleProducts()
            ->chunk($size)
            ->each(function (Collection $chunk) use ($callback) {
                $callback($chunk->map(fn (array $row) => new self($row))->values());
            });
    }

    public function id(): int
    {
        return (int) ($this->attributes['id'] ?? 0);
    }

    public function getKey(): int
    {
        return $this->id();
    }

    public function offerId(): string
    {
        return MerchantCatalog::offerId($this->attributes);
    }

    public function title(): string
    {
        return (string) ($this->attributes['title'] ?? '');
    }

    public function isEligible(): bool
    {
        return MerchantCatalog::isEligible($this->attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
