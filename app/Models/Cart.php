<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'customer_id',
        'name',
        'type',
        'is_default',
    ];

    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer that owns the cart
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the items in the cart
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate the total price of all items in the cart
     */
    public function total()
    {
        return $this->items->sum(function ($item) {
            $price = $item->product ? $item->product->selling_price : 0;
            return $price * $item->quantity;
        });
    }

    /**
     * Calculate the subtotal of the cart
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            $price = $item->product ? $item->product->selling_price : 0;
            return $price * $item->quantity;
        });
    }

    /**
     * Calculate the total tax of the cart
     */
    public function getTaxAmountAttribute(): float
    {
        // Implement tax calculation logic based on your requirements
        return 0;
    }

    /**
     * Calculate the total discount of the cart
     */
    public function getDiscountAmountAttribute(): float
    {
        // Implement discount calculation logic based on your requirements
        return 0;
    }

    /**
     * Calculate the total of the cart
     */
    public function getTotalAttribute(): float
    {
        return $this->subtotal + $this->tax_amount - $this->discount_amount;
    }

    /**
     * Get the total number of items in the cart
     */
    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }
} 