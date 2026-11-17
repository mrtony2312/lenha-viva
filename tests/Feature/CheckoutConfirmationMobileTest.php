<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutConfirmationMobileTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmation_page_has_responsive_styles_and_portuguese_file_picker(): void
    {
        SiteSetting::setValue('bank_iban', 'PT50018900060642601000555');

        $order = Order::create([
            'reference' => 'NE-MOBILE01',
            'status' => 'pending',
            'payment_method' => 'bacs',
            'email' => 'cliente@example.com',
            'shipping_first_name' => 'Ana',
            'shipping_last_name' => 'Silva',
            'shipping_address_1' => 'Rua 1',
            'shipping_city' => 'Palmela',
            'shipping_postcode' => '2950-436',
            'shipping_country' => 'PT',
            'billing_first_name' => 'Ana',
            'billing_last_name' => 'Silva',
            'billing_address_1' => 'Rua 1',
            'billing_city' => 'Palmela',
            'billing_postcode' => '2950-436',
            'billing_country' => 'PT',
            'subtotal' => 10,
            'shipping' => 0,
            'total' => 10,
            'currency' => 'EUR',
        ]);

        $html = $this->get(route('checkout.confirmation', $order->reference))->assertOk()->getContent();

        $this->assertStringContainsString('lang="pt"', $html);
        $this->assertStringContainsString('Escolher ficheiro', $html);
        $this->assertStringContainsString('Nenhum ficheiro selecionado', $html);
        $this->assertStringContainsString('@media', $html);
        $this->assertStringContainsString('max-width: 720px', $html);
    }
}
