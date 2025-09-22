<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryCheckItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_check_id',
        'product_id',
        'system_count',
        'actual_count',
        'difference',
        'notes',
    ];

    // Related inventory orders
    public function check(): BelongsTo
    {
        return $this->belongsTo(InventoryCheck::class, 'inventory_check_id');
    }

    // Related Products
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
} 