<?php

return [

    'brand' => 'Naturalenha',
    'legal_name' => 'Naturalenha, Unipessoal, Lda',
    'legal_form' => 'Sociedade Unipessoal por Quotas',
    'nif' => '508162599',
    'vat' => 'PT508162599',
    'incorporated_at' => '16/05/2007',
    'share_capital' => '53 500 €',
    'status' => 'Ativa',
    'cae' => '02200 – Exploração florestal',
    'activity' => 'Produção e venda de lenha e derivados, incluindo o transporte; venda a retalho ao cliente final',
    'address_line' => 'Estrada Nacional 379/2, Rua da Figueira, n.º 1, Vale de Touros, 2950-436 Palmela, Setúbal, Portugal',
    'address' => [
        'line_1' => 'Estrada Nacional 379/2',
        'line_2' => 'Rua da Figueira, n.º 1',
        'locality' => 'Vale de Touros',
        'postcode' => '2950-436',
        'city' => 'Palmela',
        'district' => 'Setúbal',
        'country' => 'Portugal',
    ],
    'email' => 'contacto@naturalenha.com',
    'phone' => '+351 912 026 453',
    'phone_tel' => '+351912026453',
    'whatsapp' => '351912026453',
    'website' => rtrim((string) env('APP_URL', 'http://localhost'), '/'),
    'livro_reclamacoes' => 'https://www.livroreclamacoes.pt/Inicio/',
    'cnpd' => 'https://www.cnpd.pt',
    'odr' => 'https://ec.europa.eu/consumers/odr',
    'ral_list' => 'https://www.consumidor.gov.pt/parceiros/sistema-de-defesa-do-consumidor/entidades-de-resolucao-alternativa-de-litigios-de-consumo',
    'logo' => 'images/logo-naturalenha.png',
    'topbar_messages' => [
        [
            'text' => 'Envio grátis em Portugal Continental',
            'route' => 'politicaDeEntrega',
        ],
        [
            'text' => 'Pagamento por transferência bancária',
            'route' => 'politicaDePagamento',
        ],
        [
            'text' => 'Levantamento em Palmela',
            'route' => 'contacto',
        ],
        [
            'text' => '14 dias para devolver',
            'route' => 'politicaDeReembolso',
        ],
    ],

];
