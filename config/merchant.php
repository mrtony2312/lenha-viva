<?php

return [

    /*
    | Google Merchant API (products/v1). Same product policies as the XML feed:
    | https://support.google.com/merchants/answer/7052112
    | Misrepresentation: https://support.google.com/merchants/answer/6150127
    |
    | CRITICAL — Merchant Center markets:
    | This store sells and ships ONLY to Portugal (PT). Never enable Spain (ES)
    | or other countries in Merchant Center "Countries of sale". Doing so causes
    | automated Misrepresentation disapprovals because checkout rejects non-PT
    | addresses and shipping policies cover Portugal Continental only.
    */

    'account_id' => env('GOOGLE_MERCHANT_ACCOUNT_ID'),
    'data_source_id' => env('GOOGLE_MERCHANT_DATA_SOURCE_ID'),
    'credentials' => env('GOOGLE_MERCHANT_CREDENTIALS', storage_path('app/google/merchant-service-account.json')),

    'content_language' => env('GOOGLE_MERCHANT_LANGUAGE', 'pt'),
    'feed_label' => env('GOOGLE_MERCHANT_FEED_LABEL', 'PT'),
    'target_country' => env('GOOGLE_MERCHANT_COUNTRY', 'PT'),

    /*
    | Countries that must never receive Shopping ads / free listings for this
    | catalogue. Spain (ES) is listed first — the store does not deliver there.
    */
    'excluded_ads_countries' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('GOOGLE_MERCHANT_EXCLUDED_COUNTRIES', 'ES,FR,DE,IT,GB,US,AD'))
    ))),

    'return_policy_label' => env('GOOGLE_MERCHANT_RETURN_POLICY_LABEL', 'portugal-14-dias'),
    'return_policy_url' => null, // resolved at runtime via route('politicaDeReembolso')

    /*
    | Handling after bank-transfer confirmation (business days).
    | Must stay consistent with /politica-de-entrega.
    */
    'min_handling_time' => (int) env('GOOGLE_MERCHANT_MIN_HANDLING', 1),
    'max_handling_time' => (int) env('GOOGLE_MERCHANT_MAX_HANDLING', 2),
    'min_transit_time' => (int) env('GOOGLE_MERCHANT_MIN_TRANSIT', 3),
    'max_transit_time' => (int) env('GOOGLE_MERCHANT_MAX_TRANSIT', 5),

];
