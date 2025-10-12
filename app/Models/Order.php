<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_cents',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentInfo(): HasOne
    {
        return $this->hasOne(PaymentInfo::class);
    }

    /**
     * Compute total from items without persisting.
     */
    public function computeTotalCents(): int
    {
        return (int) $this->items->sum(fn (OrderItem $item) => $item->unit_price_cents * $item->quantity);
    }

    /**
     * Recalculate and persist the order's total_cents from items.
     */
    public function recalculateTotal(): void
    {
        $this->total_cents = $this->computeTotalCents();
        $this->save();
    }

    public function getTotalFormattedAttribute(): string
    {
        $currency = config('stripe.currency', 'usd');
        $symbol = match (strtolower((string) $currency)) {
            'usd' => '$',
            'eur' => '€',
            'gbp' => '£',
            'jpy' => '¥',
            default => strtoupper((string) $currency).' ',
        };
        return $symbol.' '.number_format(((int) $this->total_cents) / 100, 2);
    }
}
