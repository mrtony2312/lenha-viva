<?php

namespace Tests\Unit;

use App\Support\MerchantCatalog;
use Tests\TestCase;

class MerchantCatalogMappingTest extends TestCase
{
    public function test_offer_id_is_stable_from_catalogue_id(): void
    {
        $this->assertSame('lv-5625', MerchantCatalog::offerId(['id' => 5625]));
        $this->assertSame('lv-5625', MerchantCatalog::offerId(['id' => 5625, 'ref' => '53745625']));
    }

    public function test_portuguese_unicode_is_preserved_in_title(): void
    {
        $product = [
            'id' => 999001,
            'title' => 'Lenha seca à granel — caldeira a lenha 25 kW',
            'price' => '199.00',
            'slug' => 'lenha-seca-teste-unicode',
            'in_stock' => true,
            'category' => 'madeira-de-fogo',
            'images' => ['images/logo-naturalenha.png'],
            'description' => 'Aquecimento eficiente com lenha seca. Portugal Continental. € preço claro.',
            'ref' => '',
        ];

        $input = MerchantCatalog::toProductInput($product);
        $this->assertNotNull($input);
        $this->assertStringContainsString('à', $input['productAttributes']['title']);
        $this->assertStringContainsString('€', $input['productAttributes']['description']);
        $this->assertSame('pt', $input['contentLanguage']);
        $this->assertSame('PT', $input['feedLabel']);
    }

    public function test_promotional_price_maps_to_sale_price(): void
    {
        $product = [
            'id' => 999002,
            'title' => 'Ardenforest Pellets teste',
            'price' => '308.00',
            'old_price' => '565.00',
            'slug' => 'ardenforest-teste-promo',
            'in_stock' => true,
            'category' => 'pellets-de-madeira',
            'images' => ['images/logo-naturalenha.png'],
            'description' => 'Pellets',
            'ref' => 'TESTMPN1',
        ];

        $attrs = MerchantCatalog::toProductAttributes($product);
        $this->assertNotNull($attrs);
        $this->assertSame('565000000', $attrs['price']['amountMicros']);
        $this->assertSame('308000000', $attrs['salePrice']['amountMicros']);
        $this->assertSame('EUR', $attrs['price']['currencyCode']);
    }

    public function test_out_of_stock_availability(): void
    {
        $product = [
            'id' => 999003,
            'title' => 'Produto esgotado',
            'price' => '10.00',
            'slug' => 'produto-esgotado',
            'in_stock' => false,
            'category' => 'lenha',
            'images' => ['images/logo-naturalenha.png'],
            'description' => 'x',
        ];

        $attrs = MerchantCatalog::toProductAttributes($product);
        $this->assertSame('OUT_OF_STOCK', $attrs['availability']);
    }

    public function test_product_without_gtin_sets_identifier_exists_false_for_store_brand(): void
    {
        $product = [
            'id' => 999004,
            'title' => 'Lenha em palete Naturalenha',
            'price' => '120.00',
            'slug' => 'lenha-palete-sem-gtin',
            'in_stock' => true,
            'category' => 'lenha',
            'images' => ['images/logo-naturalenha.png'],
            'description' => 'Lenha',
            'ref' => '',
        ];

        $attrs = MerchantCatalog::toProductAttributes($product);
        $this->assertArrayNotHasKey('gtin', $attrs);
        $this->assertFalse($attrs['identifierExists']);
    }

    public function test_manufacturer_brand_and_mpn_without_inventing_gtin(): void
    {
        $product = [
            'id' => 5625,
            'title' => 'Ardenforest Pellets – Paletes de 70 sacos',
            'price' => '308.00',
            'slug' => 'ardenforest-pellets',
            'in_stock' => true,
            'category' => 'pellets-de-madeira',
            'images' => ['images/logo-naturalenha.png'],
            'description' => 'Ardenforest',
            'ref' => '53745625',
        ];

        $attrs = MerchantCatalog::toProductAttributes($product);
        $this->assertSame('Ardenforest', $attrs['brand']);
        $this->assertSame('53745625', $attrs['mpn']);
        $this->assertArrayNotHasKey('gtin', $attrs);
        $this->assertArrayNotHasKey('identifierExists', $attrs);
    }

    public function test_google_category_comes_from_config(): void
    {
        $cat = MerchantCatalog::googleProductCategory(['category' => 'caldeira-de-lenha']);
        $this->assertStringContainsString('Boilers', $cat);
    }

    public function test_ineligible_without_price(): void
    {
        $this->assertFalse(MerchantCatalog::isEligible([
            'id' => 1,
            'slug' => 'x',
            'price' => 0,
            'images' => ['images/logo-naturalenha.png'],
        ]));
    }
}
