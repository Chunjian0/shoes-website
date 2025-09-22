<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReturnRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'return_number',
        'sales_order_id',
        'customer_id',
        'store_id',
        'user_id',
        'reason',
        'status',
        'total_amount',
        'processed_by',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * 获取关联的订单
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    /**
     * 获取关联的客户
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 获取关联的门店
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'store_id')->where('is_store', true);
    }

    /**
     * 获取创建退货申请的用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取处理退货申请的用户
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * 获取退货项目
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }
} 