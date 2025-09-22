<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplierPriceAgreement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'product_id',
        'price',
        'start_date',
        'end_date',
        'min_quantity',
        'discount_rate',
        'discount_type',
        'terms',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'min_quantity' => 'integer',
        'discount_rate' => 'decimal:2',
    ];

    // Related Suppliers
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Related Products
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Check if the protocol is valid
    public function isValid(): bool
    {
        $now = now();
        return $now->gte($this->start_date) && 
            ($this->end_date === null || $now->lte($this->end_date));
    }

    // Calculate the price after discount
    public function getDiscountedPrice(float $basePrice = null): float
    {
        if ($this->discount_type === 'fixed_price' && $this->price !== null) {
            return (float)$this->price;
        }

        $basePrice = $basePrice ?? $this->product->pivot->purchase_price ?? 0;
        
        if ($this->discount_type === 'discount_rate' && $this->discount_rate > 0) {
            return (float)($basePrice * (1 - $this->discount_rate / 100));
        }

        return $basePrice;
    }

    // Check if the minimum order quantity is met
    public function checkMinQuantity(int $quantity): bool
    {
        return $quantity >= $this->min_quantity;
    }

    // Get the protocol status
    public function getStatus(): string
    {
        $now = now();
        
        if ($now->lt($this->start_date)) {
            return 'Not effective';
        }
        
        if ($this->end_date && $now->gt($this->end_date)) {
            return 'Extension';
        }
        
        return 'Take effect';
    }

    // Get discount type display text
    public function getDiscountTypeText(): string
    {
        return match($this->discount_type) {
            'fixed_price' => 'Fixed Price',
            'discount_rate' => 'Discount Rate',
            default => 'Unknown'
        };
    }

    // Get a discount description
    public function getDiscountDescription(): string
    {
        if ($this->discount_type === 'fixed_price') {
            return "Fixed price: {$this->price}";
        }
        
        if ($this->discount_type === 'discount_rate') {
            return "Discount rate: {$this->discount_rate}%";
        }
        
        return 'No discount';
    }
} 