<?php

return [

    /*
    | Google Merchant API (products/v1) — not the legacy Content API for Shopping.
    | Docs: https://developers.google.com/merchant/api/
    |
    | CRITICAL — markets: Portugal (PT) only. Never enable ES/FR/DE/IT/BE in
    | Merchant Center "Countries of sale" unless company.sales_countries says so.
    */

    'account_id' => env('GOOGLE_MERCHANT_ACCOUNT_ID'),
    'data_source_id' => env('GOOGLE_MERCHANT_DATA_SOURCE_ID'),

    /*
    | Full resource name once known, e.g. accounts/123/dataSources/456
    | Prefer storing IDs in env; name is derived at runtime.
    */
    'data_source_name' => env('GOOGLE_MERCHANT_DATA_SOURCE_NAME'),

    'credentials' => env('GOOGLE_MERCHANT_CREDENTIALS', storage_path('app/google/merchant-service-account.json')),

    'oauth' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_REFRESH_TOKEN'),
    ],

    'content_language' => env('GOOGLE_MERCHANT_LANGUAGE', 'pt'),
    'feed_label' => env('GOOGLE_MERCHANT_FEED_LABEL', 'PT'),
    'target_country' => env('GOOGLE_MERCHANT_COUNTRY', 'PT'),

    'excluded_ads_countries' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('GOOGLE_MERCHANT_EXCLUDED_COUNTRIES', 'ES,FR,DE,IT,GB,US,AD,BE'))
    ))),

    'return_policy_label' => env('GOOGLE_MERCHANT_RETURN_POLICY_LABEL', 'portugal-14-dias'),
    'return_policy_url' => null,

    'min_handling_time' => (int) env('GOOGLE_MERCHANT_MIN_HANDLING', 1),
    'max_handling_time' => (int) env('GOOGLE_MERCHANT_MAX_HANDLING', 2),
    'min_transit_time' => (int) env('GOOGLE_MERCHANT_MIN_TRANSIT', 3),
    'max_transit_time' => (int) env('GOOGLE_MERCHANT_MAX_TRANSIT', 5),

    'queue' => env('GOOGLE_MERCHANT_QUEUE', 'default'),

    /*
    | Periodic full sync (catches catalogue edits that did not fire events).
    | Events + jobs still preferred for immediate updates when available.
    */
    'schedule' => env('GOOGLE_MERCHANT_SCHEDULE', 'daily'),

];
