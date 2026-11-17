<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBankAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_bank_admin(): void
    {
        $path = trim((string) config('shop.admin_secret_path'), '/');

        $this->get('/'.$path)
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_update_bank_account_immediately(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@naturalenha.com',
            'password' => 'secret123',
        ]);

        SiteSetting::setValue('bank_account_holder', 'Old Holder');
        SiteSetting::setValue('bank_iban', 'PT50000000000000000000000');

        $response = $this->actingAs($user)->put(route('admin.bank.update'), [
            'account_holder' => 'MARIA NEVES ALVES MAIA',
            'bank_name' => 'Banco BPI',
            'iban' => 'PT50 0189 0006 0642 6010 0055 5',
            'bic' => 'BAPAPTPL',
            'note' => 'Indique a referência da encomenda.',
        ]);

        $response->assertRedirect();
        $bank = SiteSetting::bankAccount();
        $this->assertSame('MARIA NEVES ALVES MAIA', $bank['account_holder']);
        $this->assertSame('PT50018900060642601000555', $bank['iban']);
        $this->assertSame('BAPAPTPL', $bank['bic']);
        $this->assertSame('Banco BPI', $bank['bank_name']);
    }
}
