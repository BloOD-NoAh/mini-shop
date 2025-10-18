<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'attributes',
        'price_cents',
        'net_price_cents',
        'tax_cents',
        'selling_price_cents',
        'price',
        'net_price',
        'tax',
        'stock',
        'image_path',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'net_price' => 'decimal:2',
        'tax' => 'decimal:2',
    ];

    public function getSellingAmountAttribute(): ?float
    {
        if ($this->price !== null) return (float) $this->price;
        if ($this->net_price !== null || $this->tax !== null) {
            return (float) (($this->net_price ?? 0) + ($this->tax ?? 0));
        }
        if ($this->selling_price_cents !== null) return ((int) $this->selling_price_cents) / 100.0;
        if ($this->price_cents !== null) return ((int) $this->price_cents) / 100.0;
        return null;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
