<?php

if (! function_exists('money')) {
    function money(int|float|null $amount, ?string $currency = null, bool $isCents = true): string
    {
        $currency = $currency ?: config('stripe.currency', 'usd');
        $symbol = match (strtolower((string) $currency)) {
            'usd' => '$',
            'eur' => '€',
            'gbp' => '£',
            'jpy' => '¥',
            default => strtoupper((string) $currency).' ',
        };
        $val = is_null($amount) ? 0 : ($isCents ? ((float) $amount) / 100 : (float) $amount);
        return $symbol.' '.number_format($val, 2);
    }
}
