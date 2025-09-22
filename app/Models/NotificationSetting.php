<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'type',
        'template',
        'is_enabled',
        'settings',
        'last_sent_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'settings' => 'array',
        'last_sent_at' => 'datetime',
    ];

    /**
     * Get notification settings to storefront
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'store_id')->where('is_store', true);
    }

    /**
     * 获取此通知设置的接收者
     */
    public function receivers()
    {
        return $this->hasMany(NotificationReceiver::class);
    }
}
