<?php

use App\Models\SiteSetting;

if (! function_exists('bank_account')) {
    /**
     * @return array{account_holder: string, bank_name: string, iban: string, bic: string, note: string}
     */
    function bank_account(): array
    {
        return SiteSetting::bankAccount();
    }
}

if (! function_exists('format_iban')) {
    function format_iban(?string $iban): string
    {
        $iban = strtoupper(preg_replace('/\s+/', '', (string) $iban) ?? '');

        return trim(chunk_split($iban, 4, ' '));
    }
}

if (! function_exists('format_eur')) {
    function format_eur(float|int|string|null $value): string
    {
        return number_format((float) $value, 2, ',', ' ').' €';
    }
}
