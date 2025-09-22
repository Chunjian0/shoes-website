<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'return_number',
        'return_date',
        'status',
        'total_amount',
        'tax_amount',
        'final_amount',
        'refund_status',
        'reason',
        'notes',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    /**
     * Get associated purchase orders
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get a return item
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    /**
     * Get a refund history
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(PurchaseRefund::class);
    }

    /**
     * Calculate the total refund amount
     */
    public function getRefundedAmountAttribute(): float
    {
        return $this->refunds()->sum('amount');
    }

    /**
     * Calculate the remaining refund amount
     */
    public function getRemainingRefundAmountAttribute(): float
    {
        return $this->final_amount - $this->refunded_amount;
    }
} 