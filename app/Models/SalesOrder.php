<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'order_number',
        'customer_id',
        'user_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'remarks',
        'additional_data',
        'order_date',
        'shipping_method',
        'shipping_cost',
        'shipping_status',
        'contact_name',
        'contact_phone',
        'address_details',
        'estimated_arrival_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'paid_at' => 'datetime',
        'order_date' => 'datetime',
        'estimated_arrival_date' => 'date',
        'additional_data' => 'array',
    ];

    /**
     * Get the store where the order belongs
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'store_id')->where('is_store', true);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the prescription associated with the sales order.
     */
    // public function prescription(): BelongsTo
    // {
    //    return $this->belongsTo(Prescription::class);
    // }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    // public function payments()
    // {
    //     return $this->hasMany(Payment::class, 'sales_order_id');
    // }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class, 'sales_order_id');
    }

    public function eInvoice()
    {
        return $this->hasOne(EInvoice::class, 'sales_order_id');
    }
} 