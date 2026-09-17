<?php

namespace App\Support;

class Seo
{
    /** Query params that create thin or duplicate listing URLs. */
    private const THIN_PARAMS = [
        'orderby',
        's',
        'product_cat',
        'stock',
        'colors',
        'min_price',
        'max_price',
        'display',
        'product_visibility',
        'swoof',
        'price_range',
    ];

    public static function isThinListingQuery(): bool
    {
        foreach (self::THIN_PARAMS as $key) {
            if (! request()->query->has($key)) {
                continue;
            }

            $value = request()->query($key);

            if ($key === 'orderby' && in_array($value, ['menu_order', '', null], true)) {
                continue;
            }

            return true;
        }

        return false;
    }

    public static function listingCanonical(string $baseUrl): string
    {
        $baseUrl = strtok($baseUrl, '?') ?: $baseUrl;

        if (self::isThinListingQuery()) {
            return $baseUrl;
        }

        $page = (int) request('page', 1);

        if ($page > 1) {
            return $baseUrl.'?page='.$page;
        }

        return $baseUrl;
    }

    public static function robots(?string $forced = null): string
    {
        $forced = trim((string) $forced);

        if ($forced !== '') {
            return $forced;
        }

        if (self::isThinListingQuery()) {
            return 'noindex, follow';
        }

        return 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    }

    public static function category(string $internalSlug): array
    {
        $defaults = [
            'title' => CategoryLabels::label($internalSlug).' — Loja Naturalenha',
            'h1' => CategoryLabels::label($internalSlug),
            'description' => 'Compre '.CategoryLabels::label($internalSlug).' na Naturalenha. Envio grátis em Portugal Continental.',
            'intro' => '',
        ];

        return array_merge($defaults, config('seo.categories.'.$internalSlug, []));
    }
}
