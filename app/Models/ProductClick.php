<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductClick extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'section_type',
        'position',
        'device_type',
        'url',
        'referrer',
        'ip_address',
        'user_agent',
        'clicked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'product_id' => 'integer',
        'position' => 'integer',
        'clicked_at' => 'datetime',
    ];

    /**
     * Get the product that was clicked.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取关联的用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 记录单个产品点击
     */
    public static function recordClick(array $clickData): void
    {
        static::create($clickData);
    }
} 