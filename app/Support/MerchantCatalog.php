<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Maps loja_products into Google Merchant product payloads.
 * Used by the RSS feed and the Merchant API so both stay aligned with
 * https://support.google.com/merchants/answer/7052112
 */
class MerchantCatalog
{
    /**
     * Manufacturer names that appear in titles. Google requires the maker's
     * brand, not the store name, when the product is a third-party brand.
     *
     * @var list<string>
     */
    private const MANUFACTURER_BRANDS = [
        'Ardenforest',
        'Natural Energie',
        'Green Energy',
        'Bio Energy',
        'DIN Pellets',
        'Nova Lenha',
        'MM Royal',
        'Proxima',
        'Excellent',
        'Limouzi',
        'Helios',
        'Badger',
        'Bear',
        'Gold',
        'Moravia',
        'Hunter',
        'Vulkan',
        'Vulcan',
        'Olimpia',
        'Olympia',
        'Olimp',
        'Temy',
        'MBS',
    ];

    /**
     * Google product taxonomy paths (English). IDs are accepted too, but paths
     * are easier to review in Merchant Center diagnostics.
     *
     * @var array<string, string>
     */
    private const GOOGLE_CATEGORIES = [
        'pellets-de-madeira' => 'Home & Garden > Fireplace & Wood Stove Accessories > Firewood & Fuel',
        'pellets-de-madeira-e-pellets' => 'Home & Garden > Fireplace & Wood Stove Accessories > Firewood & Fuel',
        'madeira-de-fogo' => 'Home & Garden > Fireplace & Wood Stove Accessories > Firewood & Fuel',
        'lenha' => 'Home & Garden > Fireplace & Wood Stove Accessories > Firewood & Fuel',
        'madeira-compactada' => 'Home & Garden > Fireplace & Wood Stove Accessories > Firewood & Fuel',
        'a-granel' => 'Home & Garden > Fireplace & Wood Stove Accessories > Firewood & Fuel',
        'uncategorized' => 'Home & Garden > Fireplace & Wood Stove Accessories > Firewood & Fuel',
        'chef-de-madeira' => 'Home & Garden > Kitchen & Dining > Kitchen Appliances > Ranges, Cooktops & Ovens > Stoves',
        'fogao-a-lenha' => 'Home & Garden > Household Appliances > Climate Control Appliances > Heating, Ventilation & Air Conditioning > Household Heaters',
        'caldeira-de-lenha' => 'Home & Garden > Household Appliances > Climate Control Appliances > Heating, Ventilation & Air Conditioning > Boilers',
    ];

    public static function eligibleProducts(): Collection
    {
        return collect(config('loja_products', []))
            ->filter(fn (array $product) => self::isEligible($product))
            ->values();
    }

    public static function isEligible(array $product): bool
    {
        $images = self::images($product);

        return ! empty($product['slug'])
            && ! empty($images[0])
            && self::cleanPrice($product['price'] ?? 0) > 0;
    }

    /**
     * Merchant API ProductInput body.
     *
     * @return array<string, mixed>|null
     */
    public static function toProductInput(array $product): ?array
    {
        $attributes = self::toProductAttributes($product);

        if ($attributes === null) {
            return null;
        }

        return [
            'offerId' => self::offerId($product),
            'contentLanguage' => (string) config('merchant.content_language', 'pt'),
            'feedLabel' => (string) config('merchant.feed_label', 'PT'),
            'productAttributes' => $attributes,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function toProductAttributes(array $product): ?array
    {
        if (! self::isEligible($product)) {
            return null;
        }

        $price = self::cleanPrice($product['price'] ?? 0);
        $oldPrice = isset($product['old_price']) ? self::cleanPrice($product['old_price']) : 0;
        $images = self::images($product);
        $brand = self::brand($product);
        $hasManufacturerBrand = strcasecmp($brand, (string) config('company.brand', 'Naturalenha')) !== 0;
        $mpn = trim((string) ($product['ref'] ?? ''));

        $attributes = [
            'title' => self::truncate((string) ($product['title'] ?? ''), 150),
            'description' => self::truncate(self::plainText((string) ($product['description'] ?? ($product['short_description'] ?? ''))), 5000),
            'link' => route('product.show', ['slug' => $product['canonical_slug'] ?? $product['slug']]),
            'imageLink' => asset($images[0]),
            'availability' => ! empty($product['in_stock']) ? 'IN_STOCK' : 'OUT_OF_STOCK',
            'condition' => 'NEW',
            'brand' => $brand,
            'adult' => false,
            'googleProductCategory' => self::GOOGLE_CATEGORIES[$product['category'] ?? '']
                ?? self::GOOGLE_CATEGORIES['uncategorized'],
            'productTypes' => array_values(array_filter([
                CategoryLabels::label($product['category'] ?? null),
            ])),
            'shipping' => [[
                'country' => (string) config('merchant.target_country', 'PT'),
                'service' => 'Portugal Continental',
                'price' => self::money(0),
                'minHandlingTime' => (string) config('merchant.min_handling_time', 1),
                'maxHandlingTime' => (string) config('merchant.max_handling_time', 2),
                'minTransitTime' => (string) config('merchant.min_transit_time', 3),
                'maxTransitTime' => (string) config('merchant.max_transit_time', 5),
            ]],
        ];

        $extraImages = array_slice(array_values(array_diff($images, [$images[0]])), 0, 10);
        if ($extraImages !== []) {
            $attributes['additionalImageLinks'] = array_map(fn (string $image) => asset($image), $extraImages);
        }

        if ($oldPrice > $price) {
            $attributes['price'] = self::money($oldPrice);
            $attributes['salePrice'] = self::money($price);
        } else {
            $attributes['price'] = self::money($price);
        }

        if ($mpn !== '') {
            $attributes['mpn'] = $mpn;
        }

        // Branded goods need brand + GTIN or MPN. Generic firewood/pellets
        // without a maker code may declare that no identifier exists.
        if (! $hasManufacturerBrand && $mpn === '') {
            $attributes['identifierExists'] = false;
        }

        return $attributes;
    }

    public static function offerId(array $product): string
    {
        return 'lv-'.$product['id'];
    }

    public static function brand(array $product): string
    {
        $title = (string) ($product['title'] ?? '');
        $store = (string) config('company.brand', 'Naturalenha');

        $brands = self::MANUFACTURER_BRANDS;
        usort($brands, fn (string $a, string $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($brands as $maker) {
            if (stripos($title, $maker) !== false) {
                return $maker;
            }
        }

        return $store;
    }

    /**
     * @return list<string>
     */
    public static function images(array $product): array
    {
        $images = array_values(array_filter($product['images'] ?? []));
        $hover = trim((string) ($product['hover_image'] ?? ''));

        if ($hover !== '' && ! in_array($hover, $images, true)) {
            $images[] = $hover;
        }

        return $images;
    }

    public static function cleanPrice(mixed $price): float
    {
        if (is_numeric($price)) {
            return (float) $price;
        }

        if (empty($price)) {
            return 0.0;
        }

        $price = str_replace(',', '', (string) $price);
        $price = preg_replace('/[^\d.]/', '', $price);

        return (float) $price;
    }

    public static function plainText(string $text): string
    {
        return trim((string) preg_replace('/\s+/', ' ', strip_tags($text)));
    }

    public static function truncate(string $text, int $length): string
    {
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length - 1).'…' : $text;
    }

    /**
     * @return array{amountMicros: string, currencyCode: string}
     */
    public static function money(float $amount): array
    {
        return [
            'amountMicros' => (string) (int) round($amount * 1_000_000),
            'currencyCode' => 'EUR',
        ];
    }

    public static function xmlPrice(float $amount): string
    {
        return number_format($amount, 2, '.', '').' EUR';
    }
}
