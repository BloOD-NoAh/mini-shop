<?php

if (! function_exists('money')) {
    function money(int|float|null $cents, ?string $currency = null): string
    {
        $currency = $currency ?: config('stripe.currency', 'usd');
        $symbol = match (strtolower((string) $currency)) {
            'usd' => '$',
            'eur' => '€',
            'gbp' => '£',
            'jpy' => '¥',
            default => strtoupper((string) $currency).' ',
        };
        $amount = is_null($cents) ? 0 : (int) $cents;
        return $symbol.' '.number_format($amount / 100, 2);
    }
}

