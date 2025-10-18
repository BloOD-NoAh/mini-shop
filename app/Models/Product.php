<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_cents',
        'stock',
        'image_path',
        'category',
        'net_price_cents',
        'tax_cents',
        'selling_price_cents',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getHasVariantsAttribute(): bool
    {
        return $this->relationLoaded('variants')
            ? $this->variants->isNotEmpty()
            : (bool) ($this->variants()->count() > 0);
    }

    public function getPriceFormattedAttribute(): string
    {
        $currency = config('stripe.currency', 'usd');
        $symbol = match (strtolower((string) $currency)) {
            'usd' => '$',
            'eur' => '€',
            'gbp' => '£',
            'jpy' => '¥',
            default => strtoupper((string) $currency).' ',
        };
        // Products table uses integer cents for price_cents, but decimal for selling/net/tax
        if ($this->selling_price_cents !== null) {
            $amount = (float) $this->selling_price_cents; // already decimal(10,2)
        } else {
            $amount = ((int) $this->price_cents) / 100; // integer cents fallback
        }
        return $symbol.' '.number_format($amount, 2);
    }
}

