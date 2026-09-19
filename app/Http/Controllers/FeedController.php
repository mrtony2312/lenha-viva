<?php

namespace App\Http\Controllers;

use App\Support\MerchantCatalog;

class FeedController extends Controller
{
    /**
     * Google Merchant Center product feed (RSS 2.0 + g: namespace).
     * Same payload as the Merchant API sync.
     * https://support.google.com/merchants/answer/7052112
     */
    public function googleMerchant()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss/>');
        $xml->addAttribute('version', '2.0');
        $xml->addAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $channel = $xml->addChild('channel');
        $channel->addChild('title', htmlspecialchars(config('app.name', 'Naturalenha')));
        $channel->addChild('link', config('company.website'));
        $channel->addChild('description', 'Catálogo de produtos Naturalenha — pellets de madeira, lenha e equipamentos de aquecimento.');
        $channel->addChild('language', (string) config('merchant.content_language', 'pt'));

        foreach (MerchantCatalog::eligibleProducts() as $product) {
            $this->addItem($channel, $product);
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function addItem(\SimpleXMLElement $channel, array $product): void
    {
        $input = MerchantCatalog::toProductInput($product);
        if ($input === null) {
            return;
        }

        $attr = $input['productAttributes'];
        $ns = 'http://base.google.com/ns/1.0';
        $item = $channel->addChild('item');

        $item->addChild('g:id', $input['offerId'], $ns);
        $this->addCdataChild($item, 'title', $attr['title']);
        $this->addCdataChild($item, 'description', $attr['description']);
        $item->addChild('link', $attr['link']);
        $item->addChild('g:image_link', htmlspecialchars($attr['imageLink']), $ns);

        foreach ($attr['additionalImageLinks'] ?? [] as $extraImage) {
            $item->addChild('g:additional_image_link', htmlspecialchars($extraImage), $ns);
        }

        $item->addChild('g:availability', $attr['availability'] === 'IN_STOCK' ? 'in_stock' : 'out_of_stock', $ns);
        $item->addChild('g:condition', 'new', $ns);
        $item->addChild('g:brand', htmlspecialchars($attr['brand']), $ns);
        $item->addChild('g:adult', ! empty($attr['adult']) ? 'yes' : 'no', $ns);
        $item->addChild('g:google_product_category', htmlspecialchars($attr['googleProductCategory']), $ns);
        $item->addChild('g:ships_from_country', htmlspecialchars($attr['shipsFromCountry'] ?? 'PT'), $ns);

        foreach ($attr['productTypes'] ?? [] as $type) {
            $item->addChild('g:product_type', htmlspecialchars($type), $ns);
        }

        $item->addChild('g:price', MerchantCatalog::xmlPrice($this->fromMicros($attr['price'])), $ns);
        if (! empty($attr['salePrice'])) {
            $item->addChild('g:sale_price', MerchantCatalog::xmlPrice($this->fromMicros($attr['salePrice'])), $ns);
        }

        if (! empty($attr['mpn'])) {
            $item->addChild('g:mpn', htmlspecialchars($attr['mpn']), $ns);
        }

        if (! empty($attr['color'])) {
            $item->addChild('g:color', htmlspecialchars($attr['color']), $ns);
        }

        if (array_key_exists('identifierExists', $attr) && $attr['identifierExists'] === false) {
            $item->addChild('g:identifier_exists', 'no', $ns);
        }

        $shipping = $attr['shipping'][0] ?? [];
        $ship = $item->addChild('g:shipping', null, $ns);
        $ship->addChild('g:country', $shipping['country'] ?? 'PT', $ns);
        $ship->addChild('g:service', $shipping['service'] ?? 'Portugal Continental', $ns);
        $ship->addChild('g:price', MerchantCatalog::xmlPrice($this->fromMicros($shipping['price'] ?? MerchantCatalog::money(0))), $ns);
        $ship->addChild('g:min_handling_time', (string) ($shipping['minHandlingTime'] ?? '1'), $ns);
        $ship->addChild('g:max_handling_time', (string) ($shipping['maxHandlingTime'] ?? '2'), $ns);
        $ship->addChild('g:min_transit_time', (string) ($shipping['minTransitTime'] ?? '3'), $ns);
        $ship->addChild('g:max_transit_time', (string) ($shipping['maxTransitTime'] ?? '5'), $ns);
    }

    /**
     * @param  array{amountMicros: string, currencyCode: string}  $money
     */
    private function fromMicros(array $money): float
    {
        return ((int) $money['amountMicros']) / 1_000_000;
    }

    private function addCdataChild(\SimpleXMLElement $parent, string $name, string $value): void
    {
        $node = dom_import_simplexml($parent);
        $document = $node->ownerDocument;
        $child = $document->createElement($name);
        $child->appendChild($document->createCDATASection($value));
        $node->appendChild($child);
    }
}
