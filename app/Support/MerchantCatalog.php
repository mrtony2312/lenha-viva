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
     * Manufacturer names that appear in titles / short descriptions.
     * Google requires the maker's brand, not the store name, for branded goods.
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
        'Olímpia',
        'Olimp',
        'Olymp',
        'Temy',
        'MBS',
        'Vimasol',
        'Super Thermo Magnum',
        'Thermo Magnum',
        'Solid',
        'Sólida',
        'Maciça',
    ];

    /**
     * Normalize Portuguese marketing spellings to the manufacturer brand Google expects.
     *
     * @var array<string, string>
     */
    private const BRAND_ALIASES = [
        'Sólida' => 'Solid',
        'Maciça' => 'Solid',
        'Olímpia' => 'Olympia',
        'Olimpia' => 'Olympia',
        'Olimp' => 'Olymp',
        'Vulcan' => 'Vulkan',
    ];

    public static function eligibleProducts(): Collection
    {
        $products = collect(config('loja_products', []))
            ->filter(fn (array $product) => self::isEligible($product))
            ->values();

        return self::preferUniquePrimaryImages($products);
    }

    /**
     * Prefer a distinct primary image per offer when galleries allow it.
     * Shared hero photos across different SKUs trigger Merchant image issues.
     *
     * @param  Collection<int, array<string, mixed>>  $products
     * @return Collection<int, array<string, mixed>>
     */
    private static function preferUniquePrimaryImages(Collection $products): Collection
    {
        $claimed = [];

        return $products->map(function (array $product) use (&$claimed) {
            $images = self::images($product);
            if ($images === []) {
                return $product;
            }

            $chosen = $images[0];
            foreach ($images as $candidate) {
                if (! isset($claimed[$candidate])) {
                    $chosen = $candidate;
                    break;
                }
            }

            $claimed[$chosen] = true;
            $rest = array_values(array_filter($images, fn (string $image) => $image !== $chosen));
            $product['merchant_images'] = array_merge([$chosen], $rest);

            return $product;
        })->values();
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
        $color = trim((string) ($product['color'] ?? ''));

        $description = self::truncate(self::plainText((string) ($product['description'] ?? ($product['short_description'] ?? ''))), 4500);
        $complianceNote = trim((string) config('company.sales_territory_note', ''));
        if ($complianceNote !== '' && ! str_contains(mb_strtolower($description), 'espanha')) {
            $description = self::truncate(trim($description.' '.$complianceNote), 5000);
        }

        $attributes = [
            'title' => self::truncate((string) ($product['title'] ?? ''), 150),
            'description' => $description,
            'link' => route('product.show', ['slug' => $product['canonical_slug'] ?? $product['slug']]),
            'imageLink' => asset($images[0]),
            'availability' => ! empty($product['in_stock']) ? 'IN_STOCK' : 'OUT_OF_STOCK',
            'condition' => 'NEW',
            'brand' => $brand,
            'adult' => false,
            'googleProductCategory' => self::googleProductCategory($product),
            'productTypes' => array_values(array_filter([
                CategoryLabels::label(CategoryLabels::normalizeInternal($product['category'] ?? null) ?? ($product['category'] ?? null)),
            ])),
            'shipping' => [[
                'country' => 'PT',
                'service' => 'Portugal Continental',
                'price' => self::money(0),
                'minHandlingTime' => (string) config('merchant.min_handling_time', 1),
                'maxHandlingTime' => (string) config('merchant.max_handling_time', 2),
                'minTransitTime' => (string) config('merchant.min_transit_time', 3),
                'maxTransitTime' => (string) config('merchant.max_transit_time', 5),
            ]],
            'returnPolicyLabel' => (string) config('merchant.return_policy_label', 'portugal-14-dias'),
            'shoppingAdsExcludedCountries' => array_values(array_filter(
                array_map('strtoupper', (array) config('merchant.excluded_ads_countries', ['ES']))
            )),
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

        if ($mpn !== '' && mb_strlen($mpn) <= 70 && ! preg_match('/\s{2,}/', $mpn) && ! str_contains($mpn, "\n")) {
            $attributes['mpn'] = $mpn;
        }

        if ($color !== '') {
            $attributes['color'] = self::truncate($color, 100);
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
        // Stable catalogue id — never random. MPN/ref is sent separately as mpn.
        return 'lv-'.(int) ($product['id'] ?? 0);
    }

    public static function googleProductCategory(array $product): string
    {
        $map = (array) config('merchant_categories.map', []);
        $default = (string) config(
            'merchant_categories.default',
            'Home & Garden > Fireplace & Wood Stove Accessories > Firewood & Fuel'
        );

        $internal = CategoryLabels::normalizeInternal($product['category'] ?? null)
            ?? ($product['category'] ?? null);

        if (is_string($internal) && isset($map[$internal])) {
            return (string) $map[$internal];
        }

        return $default;
    }

    public static function brand(array $product): string
    {
        $haystack = trim(
            (string) ($product['title'] ?? '').' '.
            (string) ($product['short_description'] ?? '')
        );
        $store = (string) config('company.brand', 'Naturalenha');

        // Compact form catches "termomagnum" / "ThermoMagnum" without spaces.
        $compact = mb_strtolower((string) preg_replace('/[\s\-]+/u', '', $haystack.' '.(string) ($product['description'] ?? '')));
        if (str_contains($compact, 'superthermomagnum')) {
            return 'Super Thermo Magnum';
        }
        if (str_contains($compact, 'thermomagnum') || str_contains($compact, 'termomagnum')) {
            return 'Thermo Magnum';
        }

        $brands = self::MANUFACTURER_BRANDS;
        usort($brands, fn (string $a, string $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($brands as $maker) {
            if (preg_match('/\b'.preg_quote($maker, '/').'\b/iu', $haystack)) {
                return self::BRAND_ALIASES[$maker] ?? $maker;
            }
        }

        // Descriptions often say "modelo Solid 40 kW" without putting Solid in the title.
        if (preg_match('/\bSolid\b/iu', (string) ($product['description'] ?? ''))) {
            return 'Solid';
        }

        if (preg_match('/\bVimasol\b/iu', $haystack.' '.(string) ($product['description'] ?? ''))) {
            return 'Vimasol';
        }

        return $store;
    }

    /**
     * @return list<string>
     */
    public static function images(array $product): array
    {
        if (! empty($product['merchant_images']) && is_array($product['merchant_images'])) {
            return array_values(array_filter($product['merchant_images']));
        }

        $images = array_values(array_filter($product['images'] ?? []));
        $hover = trim((string) ($product['hover_image'] ?? ''));

        if ($hover !== '' && ! in_array($hover, $images, true)) {
            $images[] = $hover;
        }

        $resolved = [];
        foreach ($images as $image) {
            $best = self::bestAvailableImage((string) $image);
            if ($best !== '' && ! in_array($best, $resolved, true)) {
                $resolved[] = $best;
            }
        }

        if (count($resolved) < 2) {
            return $resolved;
        }

        // Put the strongest Merchant-ready image first (≥ 500×500 preferred).
        usort($resolved, function (string $a, string $b) {
            return self::imageScore($b) <=> self::imageScore($a);
        });

        return $resolved;
    }

    private static function imageScore(string $path): int
    {
        $absolute = public_path($path);
        if (! is_file($absolute)) {
            return -1;
        }

        $size = @getimagesize($absolute);
        if ($size === false) {
            return 0;
        }

        [$width, $height] = $size;
        $meets500 = ($width >= 500 && $height >= 500) ? 1_000_000_000 : 0;

        return $meets500 + ($width * $height);
    }

    /**
     * Prefer the largest on-disk variant (≥ 500×500 when available).
     * WordPress stores catalog thumbs as *-480x480.*; full files usually exist without the suffix.
     */
    public static function bestAvailableImage(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if ($path === '') {
            return '';
        }

        $candidates = [$path];

        // Strip every trailing "-{w}x{h}" WordPress size token.
        $withoutSizes = $path;
        while (preg_match('/-\d+x\d+(?=\.[a-z0-9]+$)/i', $withoutSizes)) {
            $withoutSizes = preg_replace('/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $withoutSizes, 1);
            $candidates[] = $withoutSizes;
        }

        // Also try dropping a trailing "-1" / "-2" duplicate marker after size strip.
        if (preg_match('/^(.+)-\d+(\.[a-z0-9]+)$/i', $withoutSizes, $m)) {
            $candidates[] = $m[1].$m[2];
        }

        $bestPath = $path;
        $bestScore = -1;

        foreach (array_unique($candidates) as $candidate) {
            $absolute = public_path($candidate);
            if (! is_file($absolute)) {
                continue;
            }

            $size = @getimagesize($absolute);
            if ($size === false) {
                continue;
            }

            [$width, $height] = $size;
            $meets500 = ($width >= 500 && $height >= 500) ? 1_000_000_000 : 0;
            $score = $meets500 + ($width * $height);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestPath = $candidate;
            }
        }

        return $bestPath;
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
