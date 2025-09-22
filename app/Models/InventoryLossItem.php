<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryLossItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_loss_id',
        'product_id',
        'quantity',
        'cost_price',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Related loss statement
    public function loss(): BelongsTo
    {
        return $this->belongsTo(InventoryLoss::class, 'inventory_loss_id');
    }

    // Related Products
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
} 