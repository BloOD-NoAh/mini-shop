<?php

return [
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'secret' => env('PAYPAL_SECRET'),
    // 'sandbox' or 'live'
    'mode' => env('PAYPAL_MODE', 'sandbox'),
];

