<?php

return [
    'settings' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'), // 'sandbox' or 'live'
        'http.ConnectionTimeOut' => 30,
        'log.LogEnabled' => true,
        'log.FileName' => storage_path() . '/logs/paypal.log',
        'log.LogLevel' => 'DEBUG',
    ],

    'sandbox_client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
    'sandbox_secret' => env('PAYPAL_SANDBOX_SECRET', ''),
    'live_client_id' => env('PAYPAL_LIVE_CLIENT_ID', ''),
    'live_secret' => env('PAYPAL_LIVE_SECRET', ''),
];
