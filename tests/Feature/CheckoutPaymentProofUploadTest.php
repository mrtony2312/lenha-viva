<?php

namespace Tests\Feature;

use App\Mail\PaymentProofSubmittedMail;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutPaymentProofUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_upload_payment_proof(): void
    {
        Mail::fake();
        Storage::fake('local');

        $order = Order::create([
            'reference' => 'NE-PROOF001',
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
            'subtotal' => 50,
            'shipping' => 0,
            'total' => 50,
            'currency' => 'EUR',
        ]);

        $file = UploadedFile::fake()->create('comprovativo.pdf', 100, 'application/pdf');

        $response = $this->post(route('checkout.payment-proof', $order->reference), [
            'payment_proof' => $file,
        ]);

        $response->assertRedirect(route('checkout.confirmation', $order->reference));
        $order->refresh();
        $this->assertNotNull($order->payment_proof_path);
        Storage::disk('local')->assertExists($order->payment_proof_path);
        Mail::assertSent(PaymentProofSubmittedMail::class);
    }
}
