<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'ab_test_id',
        'variant',
        'impressions',
        'clicks',
        'conversions',
        'revenue',
    ];

    protected $casts = [
        'impressions' => 'integer',
        'clicks' => 'integer',
        'conversions' => 'integer',
        'revenue' => 'float',
    ];

    /**
     * 获取关联的A/B测试
     */
    public function abTest(): BelongsTo
    {
        return $this->belongsTo(AbTest::class);
    }

    /**
     * 记录展示
     */
    public function incrementImpressions(int $count = 1): self
    {
        $this->increment('impressions', $count);
        return $this;
    }

    /**
     * 记录点击
     */
    public function incrementClicks(int $count = 1): self
    {
        $this->increment('clicks', $count);
        return $this;
    }

    /**
     * 记录转化
     */
    public function incrementConversions(int $count = 1, float $revenue = 0): self
    {
        $this->increment('conversions', $count);
        if ($revenue > 0) {
            $this->increment('revenue', $revenue);
        }
        return $this;
    }

    /**
     * 获取点击率
     */
    public function getCtr(): float
    {
        if ($this->impressions === 0) {
            return 0;
        }
        return round(($this->clicks / $this->impressions) * 100, 2);
    }

    /**
     * 获取转化率
     */
    public function getConversionRate(): float
    {
        if ($this->clicks === 0) {
            return 0;
        }
        return round(($this->conversions / $this->clicks) * 100, 2);
    }
} 