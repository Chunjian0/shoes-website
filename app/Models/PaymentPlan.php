<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentPlan extends Model
{
    protected $fillable = [
        'purchase_id',
        'amount',
        'due_date',
        'status',
        'description',
    ];

    protected $casts = [
        'amount' => 'float',
        'due_date' => 'datetime',
    ];

    /**
     * Get associated purchase orders
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
} 