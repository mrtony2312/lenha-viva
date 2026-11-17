<?php

namespace Tests\Feature;

use App\Mail\AdminOrderNotification;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutBankTransferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::setValue('bank_account_holder', 'MARIA NEVES ALVES MAIA');
        SiteSetting::setValue('bank_iban', 'PT50018900060642601000555');
        SiteSetting::setValue('bank_bic', 'BAPAPTPL');
    }

    public function test_checkout_creates_order_and_sends_mails(): void
    {
        Mail::fake();

        $product = collect(config('loja_products'))->first(fn ($p) => ! empty($p['in_stock']) && (float) str_replace(',', '', (string) $p['price']) > 0);
        $this->assertNotNull($product);

        $this->withSession([
            'cart' => [
                $product['id'] => [
                    'id' => $product['id'],
                    'title' => $product['title'],
                    'price' => $product['price'],
                    'quantity' => 1,
                    'slug' => $product['slug'],
                ],
            ],
        ]);

        $response = $this->post(route('checkout.store'), $this->validPayload());

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('NE-', $order->reference);
        $this->assertSame('bacs', $order->payment_method);
        $this->assertSame(1, $order->items()->count());

        $response->assertRedirect(route('checkout.confirmation', $order->reference));

        Mail::assertSent(OrderConfirmation::class);
        Mail::assertSent(AdminOrderNotification::class);
    }

    public function test_confirmation_shows_bank_details(): void
    {
        $order = Order::create([
            'reference' => 'NE-TESTBANK1',
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
            'subtotal' => 100,
            'shipping' => 0,
            'total' => 100,
            'currency' => 'EUR',
        ]);

        $response = $this->get(route('checkout.confirmation', $order->reference));

        $response->assertOk();
        $response->assertSee('MARIA NEVES ALVES MAIA', false);
        $response->assertSee('PT50', false);
        $response->assertSee('NE-TESTBANK1', false);
        $response->assertSee('Escolher ficheiro', false);
        $response->assertSee('Nenhum ficheiro selecionado', false);
        $response->assertDontSee('Choose file', false);
    }

    /** @return array<string, mixed> */
    private function validPayload(): array
    {
        return [
            'shipping_method' => 'free_shipping:3',
            'payment_method' => 'bacs',
            'terms_checkbox' => '1',
            'email' => 'cliente@example.com',
            'shipping-country' => 'PT',
            'shipping-first_name' => 'Ana',
            'shipping-last_name' => 'Silva',
            'shipping-address_1' => 'Rua da Figueira 1',
            'shipping-city' => 'Palmela',
            'shipping-postcode' => '2950-436',
            'billing-country' => 'PT',
            'billing-first_name' => 'Ana',
            'billing-last_name' => 'Silva',
            'billing-address_1' => 'Rua da Figueira 1',
            'billing-city' => 'Palmela',
            'billing-postcode' => '2950-436',
        ];
    }
}
