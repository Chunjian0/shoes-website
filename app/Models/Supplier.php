<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Attributes that can be assigned in batches
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'credit_limit',
        'payment_term',
        'remarks',
        'is_active',
    ];

    /**
     * Type conversion
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credit_limit' => 'decimal:2',
        'payment_term' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Generate a supplier number
     */
    public static function generateCode(): string
    {
        $prefix = 'SP';
        $date = date('Ym');
        $sequence = str_pad((string) (static::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count() + 1), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $date . $sequence;
    }

    /**
     * Contact
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class);
    }

    /**
     * Related Products
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'supplier_products')
            ->withPivot([
                'supplier_product_code',
                'purchase_price',
                'tax_rate',
                'min_order_quantity',
                'lead_time',
                'is_preferred',
                'remarks'
            ])
            ->withTimestamps();
    }

    /**
     * Related price agreement
     */
    public function priceAgreements(): HasMany
    {
        return $this->hasMany(SupplierPriceAgreement::class);
    }

    /**
     * Purchase Order
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the main contact person
     */
    public function getPrimaryContact()
    {
        return $this->contacts()->where('is_primary', true)->first();
    }

    /**
     * Get a valid price agreement
     */
    public function getActiveAgreements()
    {
        return $this->priceAgreements()
            ->where('start_date', '<=', now())
            ->where(function ($query) {
                $query->where('end_date', '>=', now())
                    ->orWhereNull('end_date');
            })
            ->get();
    }

    /**
     * Get the latest price agreement for the product
     */
    public function getLatestAgreementForProduct($productId)
    {
        return $this->priceAgreements()
            ->where('product_id', $productId)
            ->where('start_date', '<=', now())
            ->where(function ($query) {
                $query->where('end_date', '>=', now())
                    ->orWhereNull('end_date');
            })
            ->latest('start_date')
            ->first();
    }

    /**
     * Check if the credit limit exceeds
     */
    public function isCreditLimitExceeded(): bool
    {
        $unpaidAmount = $this->purchases()
            ->whereNull('paid_at')
            ->sum('total_amount');
            
        return $unpaidAmount > $this->credit_limit;
    }

    /**
     * Get the total amount not paid
     */
    public function getUnpaidAmount(): float
    {
        return $this->purchases()
            ->whereNull('paid_at')
            ->sum('total_amount');
    }

    /**
     * Get available credit limits
     */
    public function getAvailableCreditLimit(): float
    {
        $unpaidAmount = $this->getUnpaidAmount();
        return max(0, $this->credit_limit - $unpaidAmount);
    }
} 