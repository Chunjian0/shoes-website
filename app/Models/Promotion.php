<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'button_text',
        'button_link',
        'background_color',
        'text_color',
        'start_date',
        'end_date',
        'is_active',
        'priority',
        'image'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'priority' => 'integer'
    ];

    /**
     * 获取当前有效的促销活动
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActive()
    {
        $now = Carbon::now();
        return self::where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * 判断促销活动是否有效
     *
     * @return bool
     */
    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();
        
        if ($this->start_date && $this->start_date->gt($now)) {
            return false;
        }
        
        if ($this->end_date && $this->end_date->lt($now)) {
            return false;
        }
        
        return true;
    }

    /**
     * 获取状态文本
     *
     * @return string
     */
    public function getStatusText()
    {
        if (!$this->is_active) {
            return '已禁用';
        }
        
        $now = Carbon::now();
        
        if ($this->start_date && $this->start_date->gt($now)) {
            return '未开始';
        }
        
        if ($this->end_date && $this->end_date->lt($now)) {
            return '已结束';
        }
        
        return '进行中';
    }
}
