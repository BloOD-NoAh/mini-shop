<?php

return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'currency' => env('CURRENCY', 'usd'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
