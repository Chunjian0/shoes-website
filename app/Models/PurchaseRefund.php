<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRefund extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_return_id',
        'refund_number',
        'refund_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'refund_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the associated return order
     */
    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }
} 