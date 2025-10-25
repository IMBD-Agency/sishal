<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSL Commerce Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SSL Commerce payment gateway integration.
    | Set your credentials in the .env file.
    |
    */

    // Store Credentials
    'store_id' => env('SSL_COMMERCE_STORE_ID', 'testbox'),
    'store_password' => env('SSL_COMMERCE_STORE_PASSWORD', 'qwerty'),

    // API URLs
    'api_url' => env('SSL_COMMERCE_API_URL', 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'),
    'validation_url' => env('SSL_COMMERCE_VALIDATION_URL', 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'),

    // Callback URLs
    'success_url' => env('SSL_COMMERCE_SUCCESS_URL', '/payment/success'),
    'fail_url' => env('SSL_COMMERCE_FAIL_URL', '/payment/failed'),
    'cancel_url' => env('SSL_COMMERCE_CANCEL_URL', '/payment/cancelled'),
    'ipn_url' => env('SSL_COMMERCE_IPN_URL', '/payment/ipn'),

    // Environment Settings
    'environment' => env('SSL_COMMERCE_ENVIRONMENT', 'sandbox'), // sandbox or live
    'currency' => env('SSL_COMMERCE_CURRENCY', 'BDT'),
    'tran_id_prefix' => env('SSL_COMMERCE_TRAN_ID_PREFIX', 'TXN'),

    // Timeout Settings
    'session_timeout' => env('SSL_COMMERCE_SESSION_TIMEOUT', 30), // minutes
    'timeout' => env('SSL_COMMERCE_TIMEOUT', 30), // seconds
    'verify_ssl' => env('SSL_COMMERCE_VERIFY_SSL', true),
];
