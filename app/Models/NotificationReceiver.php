<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationReceiver extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['notification_setting_id', 'email'];

    /**
     * 获取此接收者关联的通知设置
     */
    public function notificationSetting()
    {
        return $this->belongsTo(NotificationSetting::class);
    }
} 