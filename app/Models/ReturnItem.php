<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_request_id',
        'sales_order_item_id',
        'product_id',
        'quantity',
        'price',
        'total',
        'reason',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * 获取关联的退货申请
     */
    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    /**
     * 获取关联的订单项目
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class, 'sales_order_item_id');
    }

    /**
     * 获取关联的产品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
} 