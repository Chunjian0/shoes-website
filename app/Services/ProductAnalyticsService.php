<?php

namespace App\Services;

use App\Models\AbTest;
use App\Models\AbTestResult;
use App\Models\ProductClick;
use App\Models\ProductDisplayStat;
use App\Models\ProductImpression;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ProductAnalyticsService
{
    /**
     * 批量记录产品展示
     */
    public function recordImpressions(array $products, string $sectionType, ?string $sectionId = null): void
    {
        try {
            if (empty($products)) {
                return;
            }

            $impressions = [];
            $now = now();
            $sessionId = $this->getSessionId();
            $userId = Auth::id();
            $deviceType = $this->detectDeviceType();

            foreach ($products as $index => $product) {
                // 如果传入的是产品对象而不是ID，提取ID
                $productId = is_object($product) ? $product->id : (is_array($product) ? ($product['id'] ?? null) : $product);
                
                if (!$productId) {
                    continue;
                }

                $impressions[] = [
                    'product_id' => $productId,
                    'section_type' => $sectionType,
                    'section_id' => $sectionId,
                    'position' => $index + 1,
                    'device_type' => $deviceType,
                    'session_id' => $sessionId,
                    'user_id' => $userId,
                    'viewed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // 记录到队列，以便异步处理
                $this->queueImpressionForAggregation($productId, $sectionType, $sectionId);
            }

            // 批量记录到数据库
            if (!empty($impressions)) {
                ProductImpression::recordBatch($impressions);
            }
        } catch (\Exception $e) {
            Log::error('Error recording product impressions: ' . $e->getMessage(), [
                'exception' => $e,
                'products' => $products,
                'section_type' => $sectionType,
                'section_id' => $sectionId,
            ]);
        }
    }

    /**
     * 记录产品点击
     */
    public function recordClick(int $productId, string $sectionType, ?string $sectionId = null, ?int $position = null): void
    {
        try {
            $clickData = [
                'product_id' => $productId,
                'section_type' => $sectionType,
                'section_id' => $sectionId,
                'position' => $position,
                'device_type' => $this->detectDeviceType(),
                'session_id' => $this->getSessionId(),
                'user_id' => Auth::id(),
                'clicked_at' => now(),
            ];

            ProductClick::recordClick($clickData);

            // 更新点击计数器
            $this->incrementClickCounter($productId, $sectionType, $sectionId);
        } catch (\Exception $e) {
            Log::error('Error recording product click: ' . $e->getMessage(), [
                'exception' => $e,
                'product_id' => $productId,
                'section_type' => $sectionType,
                'section_id' => $sectionId,
            ]);
        }
    }

    /**
     * 每日汇总产品统计数据
     */
    public function aggregateDailyStats(string $date = null): void
    {
        try {
            $date = $date ?: now()->toDateString();
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();

            // 获取该日期所有展示统计
            $impressions = DB::table('product_impressions')
                ->select('product_id', 'section_type', 'section_id', DB::raw('COUNT(*) as count'))
                ->whereBetween('viewed_at', [$startDate, $endDate])
                ->groupBy('product_id', 'section_type', 'section_id')
                ->get();

            // 获取该日期所有点击统计
            $clicks = DB::table('product_clicks')
                ->select('product_id', 'section_type', 'section_id', DB::raw('COUNT(*) as count'))
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->groupBy('product_id', 'section_type', 'section_id')
                ->get();
                
            // 转换点击数据为便于查找的格式
            $clicksMap = [];
            foreach ($clicks as $click) {
                $key = "{$click->product_id}:{$click->section_type}:{$click->section_id}";
                $clicksMap[$key] = $click->count;
            }

            // 更新统计
            foreach ($impressions as $impression) {
                $key = "{$impression->product_id}:{$impression->section_type}:{$impression->section_id}";
                $clickCount = $clicksMap[$key] ?? 0;
                
                ProductDisplayStat::updateOrCreateStats(
                    [
                        'product_id' => $impression->product_id,
                        'section_type' => $impression->section_type,
                        'section_id' => $impression->section_id,
                        'date' => $date,
                    ],
                    [
                        'impressions' => $impression->count,
                        'clicks' => $clickCount,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Error aggregating daily stats: ' . $e->getMessage(), [
                'exception' => $e,
                'date' => $date,
            ]);
        }
    }

    /**
     * 获取产品在某一时段的展示和点击数据
     */
    public function getProductStats(int $productId, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subDays(30)->startOfDay();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        $stats = ProductDisplayStat::where('product_id', $productId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $totalImpressions = $stats->sum('impressions');
        $totalClicks = $stats->sum('clicks');
        $averageCtr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;

        $dailyStats = $stats->groupBy('date')->map(function ($items) {
            return [
                'impressions' => $items->sum('impressions'),
                'clicks' => $items->sum('clicks'),
                'ctr' => $items->sum('impressions') > 0 
                    ? round(($items->sum('clicks') / $items->sum('impressions')) * 100, 2) 
                    : 0,
            ];
        })->toArray();

        $sectionStats = $stats->groupBy('section_type')->map(function ($items) {
            return [
                'impressions' => $items->sum('impressions'),
                'clicks' => $items->sum('clicks'),
                'ctr' => $items->sum('impressions') > 0 
                    ? round(($items->sum('clicks') / $items->sum('impressions')) * 100, 2) 
                    : 0,
            ];
        })->toArray();

        return [
            'total_impressions' => $totalImpressions,
            'total_clicks' => $totalClicks,
            'average_ctr' => $averageCtr,
            'daily_stats' => $dailyStats,
            'section_stats' => $sectionStats,
        ];
    }

    /**
     * 获取当前会话ID
     */
    protected function getSessionId(): string
    {
        $sessionId = session()->get('analytics_session_id');
        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
            session()->put('analytics_session_id', $sessionId);
        }
        return $sessionId;
    }

    /**
     * 检测设备类型
     */
    protected function detectDeviceType(): string
    {
        $agent = request()->header('User-Agent');
        
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $agent)) {
            return 'tablet';
        }
        
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $agent)) {
            return 'mobile';
        }
        
        return 'desktop';
    }

    /**
     * 将展示记录加入队列以便异步聚合
     */
    protected function queueImpressionForAggregation(int $productId, string $sectionType, ?string $sectionId): void
    {
        $date = now()->toDateString();
        $key = "impression:{$date}:{$productId}:{$sectionType}:" . ($sectionId ?: 'null');
        
        Redis::incr($key);
        Redis::expire($key, 60 * 60 * 24 * 2); // 保留2天
    }

    /**
     * 增加点击计数器
     */
    protected function incrementClickCounter(int $productId, string $sectionType, ?string $sectionId): void
    {
        $date = now()->toDateString();
        $key = "click:{$date}:{$productId}:{$sectionType}:" . ($sectionId ?: 'null');
        
        Redis::incr($key);
        Redis::expire($key, 60 * 60 * 24 * 2); // 保留2天
    }

    /**
     * 获取A/B测试变体
     */
    public function getAbTestVariant(string $testName, ?string $userId = null): string
    {
        try {
            $test = AbTest::where('name', $testName)
                ->active()
                ->first();
                
            if (!$test) {
                return 'default';
            }
            
            // 如果有用户ID，尝试保持用户看到的变体一致
            if ($userId) {
                $variantKey = "abtest:{$testName}:{$userId}";
                $storedVariant = Redis::get($variantKey);
                
                if ($storedVariant) {
                    return $storedVariant;
                }
                
                $variant = $test->getRandomVariant();
                Redis::set($variantKey, $variant);
                Redis::expire($variantKey, 60 * 60 * 24 * 30); // 保留30天
                
                return $variant;
            }
            
            return $test->getRandomVariant();
        } catch (\Exception $e) {
            Log::error('Error getting AB test variant: ' . $e->getMessage(), [
                'test_name' => $testName,
                'user_id' => $userId,
                'exception' => $e,
            ]);
            
            return 'default';
        }
    }

    /**
     * 记录A/B测试展示
     */
    public function recordAbTestImpression(string $testName, string $variant): void
    {
        try {
            $test = AbTest::where('name', $testName)->first();
            if (!$test) {
                return;
            }
            
            $result = AbTestResult::firstOrCreate([
                'ab_test_id' => $test->id,
                'variant' => $variant,
            ]);
            
            $result->incrementImpressions();
        } catch (\Exception $e) {
            Log::error('Error recording AB test impression: ' . $e->getMessage(), [
                'test_name' => $testName,
                'variant' => $variant,
                'exception' => $e,
            ]);
        }
    }

    /**
     * 记录A/B测试点击
     */
    public function recordAbTestClick(string $testName, string $variant): void
    {
        try {
            $test = AbTest::where('name', $testName)->first();
            if (!$test) {
                return;
            }
            
            $result = AbTestResult::firstOrCreate([
                'ab_test_id' => $test->id,
                'variant' => $variant,
            ]);
            
            $result->incrementClicks();
        } catch (\Exception $e) {
            Log::error('Error recording AB test click: ' . $e->getMessage(), [
                'test_name' => $testName,
                'variant' => $variant,
                'exception' => $e,
            ]);
        }
    }

    /**
     * 记录A/B测试转化
     */
    public function recordAbTestConversion(string $testName, string $variant, float $revenue = 0): void
    {
        try {
            $test = AbTest::where('name', $testName)->first();
            if (!$test) {
                return;
            }
            
            $result = AbTestResult::firstOrCreate([
                'ab_test_id' => $test->id,
                'variant' => $variant,
            ]);
            
            $result->incrementConversions(1, $revenue);
        } catch (\Exception $e) {
            Log::error('Error recording AB test conversion: ' . $e->getMessage(), [
                'test_name' => $testName,
                'variant' => $variant,
                'revenue' => $revenue,
                'exception' => $e,
            ]);
        }
    }
} 