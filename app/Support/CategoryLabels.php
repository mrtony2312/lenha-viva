<?php

namespace App\Support;

/**
 * Category internal keys (stored on products) stay stable so data/filters
 * keep working. Public URLs use Portuguese slugs via urlSlug()/fromUrlSlug().
 */
class CategoryLabels
{
    protected static array $labels = [
        'pellets-de-madeira' => 'Pellets de madeira',
        'chef-de-madeira' => 'Fogões de lenha',
        'fogao-a-lenha' => 'Salamandras a lenha',
        'caldeira-de-lenha' => 'Caldeiras a lenha',
        'madeira-de-fogo' => 'Lenha seca',
        'lenha' => 'Lenha em palete',
        'madeira-compactada' => 'Madeira densificada',
        'a-granel' => 'Venda a granel',
    ];

    /** Internal key => Portuguese URL segment */
    protected static array $urlSlugs = [
        'pellets-de-madeira' => 'pellets-de-madeira',
        'chef-de-madeira' => 'fogoes-de-lenha',
        'fogao-a-lenha' => 'salamandras-a-lenha',
        'caldeira-de-lenha' => 'caldeiras-a-lenha',
        'madeira-de-fogo' => 'madeira-de-fogo',
        'lenha' => 'lenha',
        'madeira-compactada' => 'madeira-densificada',
        'a-granel' => 'a-granel',
    ];

    /**
     * Retired URL segments: former Spanish slugs plus category keys that used to
     * duplicate a live category. Kept so their URLs answer with a 301, not a 404.
     */
    protected static array $retired = [
        'pellets-de-madera' => 'pellets-de-madeira',
        'cocinas-de-lena' => 'chef-de-madeira',
        'estufas-de-lena' => 'fogao-a-lenha',
        'calderas-de-lena' => 'caldeira-de-lenha',
        'lena-de-fuego' => 'madeira-de-fogo',
        'lena' => 'lenha',
        'madera-densificada' => 'madeira-compactada',
        'pellets-de-madeira-e-pellets' => 'pellets-de-madeira',
        'pellets-de-madera-y-pellets' => 'pellets-de-madeira',
        'uncategorized' => 'madeira-de-fogo',
        'otros' => 'madeira-de-fogo',
        'outros' => 'madeira-de-fogo',
    ];

    public static function label(?string $slug): string
    {
        if (! $slug) {
            return '';
        }

        $internal = self::normalizeInternal($slug) ?? $slug;

        return self::$labels[$internal] ?? ucwords(str_replace('-', ' ', $internal));
    }

    public static function urlSlug(?string $internal): string
    {
        if (! $internal) {
            return '';
        }

        return self::$urlSlugs[$internal] ?? $internal;
    }

    public static function fromUrlSlug(string $urlSlug): ?string
    {
        $flipped = array_flip(self::$urlSlugs);

        return $flipped[$urlSlug]
            ?? (isset(self::$labels[$urlSlug]) ? $urlSlug : null)
            ?? (self::$retired[$urlSlug] ?? null);
    }

    /**
     * Resolve any product/URL category key to the canonical internal slug.
     * Merges retired duplicates (e.g. uncategorized → madeira-de-fogo).
     */
    public static function normalizeInternal(?string $slug): ?string
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        if (isset(self::$labels[$slug])) {
            return $slug;
        }

        $resolved = self::fromUrlSlug($slug);

        return ($resolved !== null && isset(self::$labels[$resolved])) ? $resolved : null;
    }

    /**
     * Product `category` keys that belong to a canonical internal slug
     * (including retired aliases still stored on products).
     *
     * @return list<string>
     */
    public static function productKeysFor(string $internal): array
    {
        $keys = [$internal];

        foreach (self::$retired as $alias => $target) {
            if ($target === $internal) {
                $keys[] = $alias;
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * Categories for nav/footer/search: unique Portuguese labels, no Woo leftovers.
     *
     * @return array<string, string|array{name: string, count: int}>
     */
    public static function forNavigation(bool $withCounts = false): array
    {
        $counts = [];

        foreach (config('loja_products', []) as $product) {
            $internal = self::normalizeInternal($product['category'] ?? null);
            if ($internal === null) {
                continue;
            }
            $counts[$internal] = ($counts[$internal] ?? 0) + 1;
        }

        $out = [];
        foreach (self::$labels as $internal => $label) {
            $count = $counts[$internal] ?? 0;
            if ($count === 0) {
                continue;
            }
            $out[$internal] = $withCounts
                ? ['name' => $label, 'count' => $count]
                : $label;
        }

        return $out;
    }

    public static function all(): array
    {
        return self::$labels;
    }

    public static function allUrlSlugs(): array
    {
        return self::$urlSlugs;
    }

    public static function route(string $internal): string
    {
        $internal = self::normalizeInternal($internal) ?? $internal;

        return route('category', ['category' => self::urlSlug($internal)]);
    }
}
