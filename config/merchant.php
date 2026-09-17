<?php

return [

    /*
    | Google Merchant API (products/v1). Same product policies as the XML feed:
    | https://support.google.com/merchants/answer/7052112
    | API guide: https://developers.google.com/merchant/api/guides/products/add-manage
    */

    'account_id' => env('GOOGLE_MERCHANT_ACCOUNT_ID'),
    'data_source_id' => env('GOOGLE_MERCHANT_DATA_SOURCE_ID'),
    'credentials' => env('GOOGLE_MERCHANT_CREDENTIALS', storage_path('app/google/merchant-service-account.json')),

    'content_language' => env('GOOGLE_MERCHANT_LANGUAGE', 'pt'),
    'feed_label' => env('GOOGLE_MERCHANT_FEED_LABEL', 'PT'),
    'target_country' => env('GOOGLE_MERCHANT_COUNTRY', 'PT'),

    /*
    | Handling after bank-transfer confirmation (business days).
    | Must stay consistent with /politica-de-entrega.
    */
    'min_handling_time' => (int) env('GOOGLE_MERCHANT_MIN_HANDLING', 1),
    'max_handling_time' => (int) env('GOOGLE_MERCHANT_MAX_HANDLING', 2),
    'min_transit_time' => (int) env('GOOGLE_MERCHANT_MIN_TRANSIT', 3),
    'max_transit_time' => (int) env('GOOGLE_MERCHANT_MAX_TRANSIT', 5),

];
