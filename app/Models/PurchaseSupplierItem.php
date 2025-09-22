<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseSupplierItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'total_amount',
        'tax_amount',
        'shipping_fee',
        'final_amount',
        'email_sent',
        'email_sent_at',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get associated purchase orders
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get associated vendors
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the procurement details of this supplier
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'supplier_id', 'supplier_id')
            ->where('purchase_id', $this->purchase_id);
    }

    /**
     * Calculate the total amount of purchases from suppliers
     */
    public function calculateTotalAmount(): void
    {
        $this->total_amount = $this->items->sum('total_amount');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->final_amount = $this->total_amount + $this->tax_amount + $this->shipping_fee;
        $this->save();
    }

    /**
     * Mark the email sent
     */
    public function markEmailSent(): void
    {
        $this->update([
            'email_sent' => true,
            'email_sent_at' => now()
        ]);
    }
} 