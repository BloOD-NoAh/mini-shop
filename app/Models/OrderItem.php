<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'unit_price_cents',
        'quantity',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUnitPriceFormattedAttribute(): string
    {
        $currency = config('stripe.currency', 'usd');
        $symbol = match (strtolower((string) $currency)) {
            'usd' => '$',
            'eur' => '€',
            'gbp' => '£',
            'jpy' => '¥',
            default => strtoupper((string) $currency).' ',
        };
        return $symbol.' '.number_format(((int) $this->unit_price_cents) / 100, 2);
    }
}
