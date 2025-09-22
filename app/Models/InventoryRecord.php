<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'type',
        'source_type',
        'source_id',
        'batch_number',
        'expiry_date',
        'location',
        'status',
        'additional_data',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'additional_data' => 'array',
    ];

    // Type constants
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';

    // Source type constant
    public const SOURCE_PURCHASE = 'purchase';
    public const SOURCE_SALE = 'sale';
    public const SOURCE_LOSS = 'loss';
    public const SOURCE_CHECK = 'check';

    // State constants
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_LOCKED = 'locked';

    // Related Products
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Source of association
    public function source(): MorphTo
    {
        return $this->morphTo();
    }
} 