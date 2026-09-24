<?php

namespace App\Http\Controllers;

use App\Support\MerchantCatalog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FeedController extends Controller
{
    /**
     * Google Merchant Center product feed (RSS 2.0 + g: namespace).
     * Same payload as the Merchant API sync.
     * https://support.google.com/merchants/answer/7052112
     */
    public function googleMerchant()
    {
        try {
            $xml = Cache::remember('seo.feed.google-merchant.v4', now()->addMinutes(30), function () {
                return $this->buildFeedXml();
            });
        } catch (\Throwable $e) {
            Log::error('Google Merchant feed failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Bypass cache on failure so the next hit can rebuild after a fix.
            Cache::forget('seo.feed.google-merchant.v4');

            abort(500, 'Merchant feed temporarily unavailable');
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    private function buildFeedXml(): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;

        $rss = $dom->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:g', 'http://base.google.com/ns/1.0');
        $dom->appendChild($rss);

        $channel = $dom->createElement('channel');
        $rss->appendChild($channel);

        $channel->appendChild($dom->createElement('title', (string) config('app.name', 'Naturalenha')));
        $channel->appendChild($dom->createElement('link', (string) config('company.website')));
        $channel->appendChild($dom->createElement(
            'description',
            'Catálogo de produtos Naturalenha — pellets de madeira, lenha e equipamentos de aquecimento.'
        ));
        $channel->appendChild($dom->createElement('language', (string) config('merchant.content_language', 'pt')));

        $ns = 'http://base.google.com/ns/1.0';

        foreach (MerchantCatalog::eligibleProducts() as $product) {
            $this->appendItem($dom, $channel, $product, $ns);
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
    private function appendItem(\DOMDocument $dom, \DOMElement $channel, array $product, string $ns): void
    {
        $input = MerchantCatalog::toProductInput($product);
        if ($input === null) {
            return;
        }

        $attr = $input['productAttributes'];
        $item = $dom->createElement('item');
        $channel->appendChild($item);

        $this->appendNs($dom, $item, $ns, 'id', (string) $input['offerId']);
        $this->appendCdata($dom, $item, 'title', (string) $attr['title']);
        $this->appendCdata($dom, $item, 'description', (string) $attr['description']);
        $item->appendChild($dom->createElement('link', (string) $attr['link']));
        $this->appendNs($dom, $item, $ns, 'image_link', (string) $attr['imageLink']);

        foreach ($attr['additionalImageLinks'] ?? [] as $extraImage) {
            $this->appendNs($dom, $item, $ns, 'additional_image_link', (string) $extraImage);
        }

        $this->appendNs($dom, $item, $ns, 'availability', $attr['availability'] === 'IN_STOCK' ? 'in_stock' : 'out_of_stock');
        $this->appendNs($dom, $item, $ns, 'condition', 'new');
        $this->appendNs($dom, $item, $ns, 'brand', (string) $attr['brand']);
        $this->appendNs($dom, $item, $ns, 'adult', ! empty($attr['adult']) ? 'yes' : 'no');
        $this->appendNs($dom, $item, $ns, 'google_product_category', (string) $attr['googleProductCategory']);
        $this->appendNs($dom, $item, $ns, 'ships_from_country', (string) ($attr['shipsFromCountry'] ?? 'PT'));

        foreach ($attr['productTypes'] ?? [] as $type) {
            $this->appendNs($dom, $item, $ns, 'product_type', (string) $type);
        }

        $this->appendNs($dom, $item, $ns, 'price', MerchantCatalog::xmlPrice($this->fromMicros($attr['price'])));
        if (! empty($attr['salePrice'])) {
            $this->appendNs($dom, $item, $ns, 'sale_price', MerchantCatalog::xmlPrice($this->fromMicros($attr['salePrice'])));
        }

        if (! empty($attr['mpn'])) {
            $this->appendNs($dom, $item, $ns, 'mpn', (string) $attr['mpn']);
        }

        if (! empty($attr['color'])) {
            $this->appendNs($dom, $item, $ns, 'color', (string) $attr['color']);
        }

        if (array_key_exists('identifierExists', $attr) && $attr['identifierExists'] === false) {
            $this->appendNs($dom, $item, $ns, 'identifier_exists', 'no');
        }

        if (! empty($attr['returnPolicyLabel'])) {
            $this->appendNs($dom, $item, $ns, 'return_policy_label', (string) $attr['returnPolicyLabel']);
        }

        $excludedAds = $attr['shoppingAdsExcludedCountries'] ?? $attr['excludedAdsCountries'] ?? [];
        $excludedFree = $attr['freeListingExcludedCountries'] ?? $excludedAds;
        foreach ($excludedAds as $countryCode) {
            $code = strtoupper(trim((string) $countryCode));
            if ($code === '' || $code === 'PT') {
                continue;
            }
            $this->appendNs($dom, $item, $ns, 'shopping_ads_excluded_country', $code);
        }
        foreach ($excludedFree as $countryCode) {
            $code = strtoupper(trim((string) $countryCode));
            if ($code === '' || $code === 'PT') {
                continue;
            }
            $this->appendNs($dom, $item, $ns, 'free_listing_excluded_country', $code);
        }

        $shipping = $attr['shipping'][0] ?? [];
        $ship = $dom->createElementNS($ns, 'g:shipping');
        $item->appendChild($ship);
        $this->appendNs($dom, $ship, $ns, 'country', (string) ($shipping['country'] ?? 'PT'));
        $this->appendNs($dom, $ship, $ns, 'service', (string) ($shipping['service'] ?? 'Portugal Continental'));
        $this->appendNs($dom, $ship, $ns, 'price', MerchantCatalog::xmlPrice($this->fromMicros($shipping['price'] ?? MerchantCatalog::money(0))));
        $this->appendNs($dom, $ship, $ns, 'min_handling_time', (string) ($shipping['minHandlingTime'] ?? '1'));
        $this->appendNs($dom, $ship, $ns, 'max_handling_time', (string) ($shipping['maxHandlingTime'] ?? '2'));
        $this->appendNs($dom, $ship, $ns, 'min_transit_time', (string) ($shipping['minTransitTime'] ?? '3'));
        $this->appendNs($dom, $ship, $ns, 'max_transit_time', (string) ($shipping['maxTransitTime'] ?? '5'));
    }

    private function appendNs(\DOMDocument $dom, \DOMElement $parent, string $ns, string $localName, string $value): void
    {
        $node = $dom->createElementNS($ns, 'g:'.$localName);
        $node->appendChild($dom->createTextNode($value));
        $parent->appendChild($node);
    }

    private function appendCdata(\DOMDocument $dom, \DOMElement $parent, string $name, string $value): void
    {
        $child = $dom->createElement($name);
        $child->appendChild($dom->createCDATASection($value));
        $parent->appendChild($child);
    }

    /**
     * @param  array{amountMicros: string, currencyCode: string}  $money
     */
    private function fromMicros(array $money): float
    {
        return ((int) $money['amountMicros']) / 1_000_000;
    }
}
