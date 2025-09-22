<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'product_id',
        'supplier_product_code',
        'purchase_price',
        'tax_rate',
        'min_order_quantity',
        'lead_time',
        'is_preferred',
        'remarks'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'min_order_quantity' => 'integer',
        'lead_time' => 'integer',
        'is_preferred' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
} 