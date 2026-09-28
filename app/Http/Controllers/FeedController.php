<?php

namespace App\Http\Controllers;

use App\Support\MerchantCatalog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Google Merchant Center RSS 2.0 product feed.
 *
 * Spec: https://support.google.com/merchants/answer/160589
 * Attributes: https://support.google.com/merchants/answer/7052112
 *
 * CRITICAL (Google): removing a product from this feed removes it from Merchant Center.
 * Therefore we never overwrite a known-good cached feed with an empty or failed build.
 */
class FeedController extends Controller
{
    private const CACHE_KEY = 'seo.feed.google-merchant.v5';

    private const CACHE_TTL_MINUTES = 60;

    private const NS = 'http://base.google.com/ns/1.0';

    public function googleMerchant()
    {
        $previous = Cache::get(self::CACHE_KEY);

        try {
            $built = $this->buildFeedXml();
            $itemCount = substr_count($built, '<item>');

            // Never publish an empty catalogue if we still have a previous good feed.
            if ($itemCount === 0 && is_string($previous) && substr_count($previous, '<item>') > 0) {
                Log::critical('merchant.feed.refused_empty', [
                    'message' => 'Build returned 0 items — serving previous good feed to avoid wiping Merchant Center.',
                ]);

                return $this->xmlResponse($previous, stale: true);
            }

            if ($itemCount === 0) {
                Log::critical('merchant.feed.empty_no_fallback', [
                    'message' => 'Feed has 0 items and no previous cache.',
                ]);
            }

            Cache::put(self::CACHE_KEY, $built, now()->addMinutes(self::CACHE_TTL_MINUTES));

            return $this->xmlResponse($built);
        } catch (\Throwable $e) {
            Log::error('merchant.feed.build_failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Serve last good feed instead of 500 when possible — repeated 500s can expire products.
            if (is_string($previous) && substr_count($previous, '<item>') > 0) {
                Log::warning('merchant.feed.serving_stale_after_error', [
                    'items' => substr_count($previous, '<item>'),
                ]);

                return $this->xmlResponse($previous, stale: true);
            }

            abort(500, 'Merchant feed temporarily unavailable');
        }
    }

    private function xmlResponse(string $xml, bool $stale = false)
    {
        $headers = [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => $stale
                ? 'public, max-age=300'
                : 'public, max-age=3600',
            'X-Merchant-Feed-Items' => (string) substr_count($xml, '<item>'),
        ];

        if ($stale) {
            $headers['X-Merchant-Feed-Stale'] = '1';
        }

        return response($xml, 200, $headers);
    }

    private function buildFeedXml(): string
    {
        $this->forcePublicUrls();

        $products = MerchantCatalog::eligibleProducts();
        $base = $this->publicBaseUrl();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;

        $rss = $dom->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:g', self::NS);
        $dom->appendChild($rss);

        $channel = $dom->createElement('channel');
        $rss->appendChild($channel);

        $channel->appendChild($dom->createElement('title', (string) config('app.name', 'Naturalenha')));
        $channel->appendChild($dom->createElement('link', $base.'/'));
        $channel->appendChild($dom->createElement(
            'description',
            'Catálogo Naturalenha — pellets, lenha e equipamentos de aquecimento. Vendas apenas em Portugal Continental.'
        ));
        $channel->appendChild($dom->createElement('language', (string) config('merchant.content_language', 'pt')));
        $channel->appendChild($dom->createElement('lastBuildDate', now()->toRfc2822String()));

        foreach ($products as $product) {
            $this->appendItem($dom, $channel, $product);
        }

        $xml = $dom->saveXML();
        if ($xml === false || $xml === '') {
            throw new \RuntimeException('Failed to serialize Google Merchant feed XML');
        }

        return $xml;
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function appendItem(\DOMDocument $dom, \DOMElement $channel, array $product): void
    {
        $input = MerchantCatalog::toProductInput($product);
        if ($input === null) {
            return;
        }

        $attr = $input['productAttributes'];
        $item = $dom->createElement('item');
        $channel->appendChild($item);

        // Required / core attributes (Google RSS 2.0 + product data spec).
        $this->appendG($dom, $item, 'id', (string) $input['offerId']);
        $this->appendGCdata($dom, $item, 'title', (string) $attr['title']);
        $this->appendGCdata($dom, $item, 'description', (string) $attr['description']);
        $this->appendG($dom, $item, 'link', $this->absolutize((string) $attr['link']));
        $this->appendG($dom, $item, 'image_link', $this->absolutize((string) $attr['imageLink']));

        foreach ($attr['additionalImageLinks'] ?? [] as $extraImage) {
            $this->appendG($dom, $item, 'additional_image_link', $this->absolutize((string) $extraImage));
        }

        $this->appendG(
            $dom,
            $item,
            'availability',
            ($attr['availability'] ?? '') === 'IN_STOCK' ? 'in_stock' : 'out_of_stock'
        );
        $this->appendG($dom, $item, 'condition', 'new');
        $this->appendG($dom, $item, 'brand', (string) $attr['brand']);
        $this->appendG($dom, $item, 'adult', ! empty($attr['adult']) ? 'yes' : 'no');
        $this->appendG($dom, $item, 'google_product_category', (string) $attr['googleProductCategory']);

        foreach ($attr['productTypes'] ?? [] as $type) {
            if (trim((string) $type) !== '') {
                $this->appendG($dom, $item, 'product_type', (string) $type);
            }
        }

        $this->appendG($dom, $item, 'price', MerchantCatalog::xmlPrice($this->fromMicros($attr['price'])));
        if (! empty($attr['salePrice'])) {
            $this->appendG($dom, $item, 'sale_price', MerchantCatalog::xmlPrice($this->fromMicros($attr['salePrice'])));
        }

        if (! empty($attr['mpn'])) {
            $this->appendG($dom, $item, 'mpn', (string) $attr['mpn']);
        }

        if (! empty($attr['color'])) {
            $this->appendG($dom, $item, 'color', (string) $attr['color']);
        }

        if (array_key_exists('identifierExists', $attr) && $attr['identifierExists'] === false) {
            $this->appendG($dom, $item, 'identifier_exists', 'no');
        }

        if (! empty($attr['returnPolicyLabel'])) {
            $this->appendG($dom, $item, 'return_policy_label', (string) $attr['returnPolicyLabel']);
        }

        // Exclude non-PT markets from Shopping ads (store ships PT only).
        foreach ($attr['shoppingAdsExcludedCountries'] ?? [] as $countryCode) {
            $code = strtoupper(trim((string) $countryCode));
            if ($code === '' || $code === 'PT') {
                continue;
            }
            $this->appendG($dom, $item, 'shopping_ads_excluded_country', $code);
        }

        $shipping = $attr['shipping'][0] ?? [];
        $ship = $dom->createElementNS(self::NS, 'g:shipping');
        $item->appendChild($ship);
        $this->appendG($dom, $ship, 'country', (string) ($shipping['country'] ?? 'PT'));
        $this->appendG($dom, $ship, 'service', (string) ($shipping['service'] ?? 'Portugal Continental'));
        $this->appendG(
            $dom,
            $ship,
            'price',
            MerchantCatalog::xmlPrice($this->fromMicros($shipping['price'] ?? MerchantCatalog::money(0)))
        );
        $this->appendG($dom, $ship, 'min_handling_time', (string) ($shipping['minHandlingTime'] ?? '1'));
        $this->appendG($dom, $ship, 'max_handling_time', (string) ($shipping['maxHandlingTime'] ?? '2'));
        $this->appendG($dom, $ship, 'min_transit_time', (string) ($shipping['minTransitTime'] ?? '3'));
        $this->appendG($dom, $ship, 'max_transit_time', (string) ($shipping['maxTransitTime'] ?? '5'));
    }

    private function forcePublicUrls(): void
    {
        $base = $this->publicBaseUrl();
        URL::forceRootUrl($base);
        URL::forceScheme('https');
    }

    private function publicBaseUrl(): string
    {
        $configured = rtrim((string) (
            config('merchant.public_base_url')
            ?: config('company.website')
            ?: config('app.url')
        ), '/');

        if (! str_starts_with($configured, 'https://')) {
            // Prefer production shop URL for Merchant — Google rejects localhost.
            $configured = 'https://naturalenha.com';
        }

        return $configured;
    }

    private function absolutize(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return $url;
        }

        if (str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, 'http://')) {
            return 'https://'.substr($url, strlen('http://'));
        }

        return $this->publicBaseUrl().'/'.ltrim($url, '/');
    }

    private function appendG(\DOMDocument $dom, \DOMElement $parent, string $localName, string $value): void
    {
        if ($value === '') {
            return;
        }

        $node = $dom->createElementNS(self::NS, 'g:'.$localName);
        $node->appendChild($dom->createTextNode($value));
        $parent->appendChild($node);
    }

    private function appendGCdata(\DOMDocument $dom, \DOMElement $parent, string $localName, string $value): void
    {
        if ($value === '') {
            return;
        }

        $node = $dom->createElementNS(self::NS, 'g:'.$localName);
        $node->appendChild($dom->createCDATASection($value));
        $parent->appendChild($node);
    }

    /**
     * @param  array{amountMicros: string, currencyCode: string}  $money
     */
    private function fromMicros(array $money): float
    {
        return ((int) $money['amountMicros']) / 1_000_000;
    }
}
