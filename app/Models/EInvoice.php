<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'sales_order_id',
        'customer_id',
        'store_id',
        'user_id',
        'invoice_date',
        'due_date',
        'total_amount',
        'tax_amount',
        'status',
        'submission_id',
        'qr_code',
        'pdf_path',
        'xml_data',
        'response_data',
        'error_message',
        'submission_count',
        'last_submitted_at',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'xml_data' => 'array',
        'response_data' => 'array',
        'last_submitted_at' => 'datetime',
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
     * 获取创建发票的用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 生成发票编号
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $lastInvoice = self::whereDate('created_at', today())->latest()->first();
        
        if ($lastInvoice) {
            $lastNumber = substr($lastInvoice->invoice_number, -4);
            $nextNumber = str_pad((int)$lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        
        return $prefix . '-' . $date . '-' . $nextNumber;
    }

    /**
     * 获取发票状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'draft' => '草稿',
            'pending' => '待提交',
            'submitted' => '已提交',
            'approved' => '已批准',
            'rejected' => '已拒绝',
            'error' => '错误',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * 检查发票是否可以编辑
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'error', 'rejected']);
    }

    /**
     * 检查发票是否可以提交
     */
    public function isSubmittable(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'error', 'rejected']);
    }

    /**
     * 检查发票是否已经最终确认
     */
    public function isFinalized(): bool
    {
        return in_array($this->status, ['submitted', 'approved']);
    }
} 