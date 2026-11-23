<?php

return [
    'api_key' => env('JANGOPAY_API_KEY', ''),
    'enable_payments' => env('JANGOPAY_ENABLE_PAYMENTS', false),
    'private_key_path' => env('JANGOPAY_RSA_PRIVATE_KEY_PATH', ''),
    'fx_min_usd_per_cny' => env('JANGOPAY_FX_MIN_USD_PER_CNY', 0.05),
    'fx_max_usd_per_cny' => env('JANGOPAY_FX_MAX_USD_PER_CNY', 0.30),
    'fx_reference_usd_per_cny' => env('JANGOPAY_FX_REFERENCE_USD_PER_CNY', ''),
    'fx_reference_verified_at' => env('JANGOPAY_FX_REFERENCE_VERIFIED_AT', ''),
    'fx_max_deviation' => env('JANGOPAY_FX_MAX_DEVIATION', 0.05),
    // Do not configure these until validated against an authentic merchant test vector.
    'webhook_tag_format' => env('JANGOPAY_WEBHOOK_TAG_FORMAT', ''),
    'final_invoice_statuses' => env('JANGOPAY_FINAL_INVOICE_STATUSES', ''),
];
