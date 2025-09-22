<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PurchaseStatus;
use App\Enums\PurchaseInspectionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class Purchase extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

    /**
     * Attributes that can be assigned in batches
     *
     * @var array<string>
     */
    protected $fillable = [
        'warehouse_id',
        'purchase_number',
        'purchase_date',
        'total_amount',
        'tax_amount',
        'shipping_fee',
        'discount_amount',
        'adjustment_amount',
        'final_amount',
        'payment_terms',
        'payment_method',
        'payment_status',
        'purchase_status',
        'inspection_status',
        'notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'received_at',
        'auto_generated',
        'generated_by'
    ];

    /**
     * Type conversion
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'datetime',
        'received_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'purchase_status' => PurchaseStatus::class,
        'payment_status' => PaymentStatus::class,
        'inspection_status' => PurchaseInspectionStatus::class,
        'auto_generated' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['purchase_status', 'payment_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the warehouse where the purchase order belongs
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get Purchase Order Details
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Obtain purchase order payment history
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Get the supplier details for purchase orders
     */
    public function supplierItems(): HasMany
    {
        return $this->hasMany(PurchaseSupplierItem::class);
    }

    /**
     * All suppliers who obtain purchase orders
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'purchase_supplier_items')
            ->withPivot(['total_amount', 'tax_amount', 'shipping_fee', 'final_amount', 'email_sent', 'email_sent_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Calculate the total purchase order
     */
    public function calculateTotalAmount(): void
    {
        $this->total_amount = $this->supplierItems->sum('total_amount');
        $this->tax_amount = $this->supplierItems->sum('tax_amount');
        $this->shipping_fee = $this->supplierItems->sum('shipping_fee');
        $this->final_amount = $this->total_amount + $this->tax_amount + $this->shipping_fee;
        $this->save();
    }

    /**
     * Update the purchase status
     */
    public function updatePurchaseStatus(PurchaseStatus $status): void
    {
        $this->purchase_status = $status;
        $this->save();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(PaymentStatus $status): void
    {
        $this->payment_status = $status;
        $this->save();
    }

    /**
     * Check if it can be edited
     */
    public function isEditable(): bool
    {
        return in_array($this->purchase_status, [
            PurchaseStatus::DRAFT,
            PurchaseStatus::PENDING
        ]);
    }

    /**
     * Determine whether the purchase order can be cancelled
     */
    public function isCancellable(): bool
    {
        // Only orders with status pending review can be cancelled
        return $this->purchase_status->value === PurchaseStatus::PENDING->value;
    }

    /**
     * Generate a purchase order number
     * Format:PO + Year, month, day + 4Bit number
     * For example:PO202502020001
     */
    public static function generatePurchaseNumber(): string
    {
        $today = Carbon::now();
        $prefix = 'PO' . $today->format('Ymd');
        
        // Get the last order number today
        $lastPurchase = self::where('purchase_number', 'like', $prefix . '%')
            ->orderBy('purchase_number', 'desc')
            ->first();
        
        if ($lastPurchase) {
            // Extract the serial number and add1
            $sequence = (int) substr($lastPurchase->purchase_number, -4);
            $sequence++;
        } else {
            $sequence = 1;
        }
        
        // Formatted as4digits
        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
} 