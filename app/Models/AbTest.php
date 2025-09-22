<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'variants',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'variants' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * 获取测试结果
     */
    public function results(): HasMany
    {
        return $this->hasMany(AbTestResult::class);
    }

    /**
     * 随机选择一个测试变体
     */
    public function getRandomVariant(): string
    {
        $variants = $this->variants;
        if (empty($variants)) {
            return 'default';
        }

        // 如果变体包含权重，按权重选择
        if (isset($variants[0]['name']) && isset($variants[0]['weight'])) {
            $totalWeight = array_sum(array_column($variants, 'weight'));
            $randomNumber = mt_rand(1, $totalWeight);
            
            $currentWeight = 0;
            foreach ($variants as $variant) {
                $currentWeight += $variant['weight'];
                if ($randomNumber <= $currentWeight) {
                    return $variant['name'];
                }
            }
            
            return $variants[0]['name'];
        }
        
        // 否则随机选择
        return $variants[array_rand($variants)];
    }

    /**
     * 活跃测试范围查询
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }
} 