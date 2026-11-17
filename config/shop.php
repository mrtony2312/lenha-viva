<?php

return [

    'currency' => 'EUR',

    'bank' => [
        'account_holder' => env('SHOP_BANK_ACCOUNT_HOLDER', 'MARIA NEVES ALVES MAIA'),
        'bank_name' => env('SHOP_BANK_NAME', ''),
        'iban' => env('SHOP_BANK_IBAN', 'PT50018900060642601000555'),
        'bic' => env('SHOP_BANK_BIC', 'BAPAPTPL'),
        'note' => env(
            'SHOP_BANK_NOTE',
            'Indique o número da encomenda no descritivo da transferência. O envio inicia-se após a confirmação do pagamento.'
        ),
    ],

    /*
    | Secret admin path for bank account editing only.
    | Not linked in the public navigation.
    */
    'admin_secret_path' => env('ADMIN_SECRET_PATH', 'ne-bank-admin-k8p3m7qx2r'),

];
