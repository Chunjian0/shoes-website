<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDisplayStat;
use App\Models\ProductImpression;
use App\Models\ProductClick;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductAnalyticsController extends Controller
{
    /**
     * 记录批量产品展示数据
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordImpressionBatch(Request $request)
    {
        try {
            $validated = $request->validate([
                'impressions' => 'required|array',
                'impressions.*.product_id' => 'required|exists:products,id',
                'impressions.*.section_type' => 'nullable|string',
                'impressions.*.position' => 'nullable|integer',
                'impressions.*.device_type' => 'nullable|string',
                'impressions.*.url' => 'nullable|string',
                'impressions.*.timestamp' => 'nullable|date',
            ]);
            
            $impressions = [];
            $today = Carbon::today()->format('Y-m-d');
            $stats = [];
            
            // 处理每个展示
            foreach ($validated['impressions'] as $impression) {
                // 准备详细的展示记录
                $impressions[] = [
                    'product_id' => $impression['product_id'],
                    'section_type' => $impression['section_type'] ?? null,
                    'position' => $impression['position'] ?? null,
                    'device_type' => $impression['device_type'] ?? 'unknown',
                    'url' => $impression['url'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'viewed_at' => isset($impression['timestamp']) ? Carbon::parse($impression['timestamp'])->format('Y-m-d H:i:s') : Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                
                // 为统计数据准备聚合键
                $key = $impression['product_id'] . '-' . ($impression['section_type'] ?? 'unknown');
                if (!isset($stats[$key])) {
                    $stats[$key] = [
                        'product_id' => $impression['product_id'],
                        'section_type' => $impression['section_type'] ?? null,
                        'date' => $today,
                        'impressions' => 0,
                    ];
                }
                $stats[$key]['impressions']++;
            }
            
            // 批量插入详细展示记录
            if (!empty($impressions)) {
                ProductImpression::insert($impressions);
            }
            
            // 更新每日统计数据 - 修改为查找后更新以避免DB::raw问题
            foreach ($stats as $stat) {
                // 查找现有记录
                $record = ProductDisplayStat::where('product_id', $stat['product_id'])
                    ->where('section_type', $stat['section_type'])
                    ->where('date', $stat['date'])
                    ->first();
                    
                if ($record) {
                    // 如果记录存在，直接增加计数
                    $record->impressions = $record->impressions + $stat['impressions'];
                    $record->updated_at = Carbon::now();
                    $record->save();
                } else {
                    // 如果记录不存在，创建新记录
                    ProductDisplayStat::create([
                        'product_id' => $stat['product_id'],
                        'section_type' => $stat['section_type'],
                        'date' => $stat['date'],
                        'impressions' => $stat['impressions'],
                        'clicks' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Impressions recorded successfully',
                'count' => count($validated['impressions'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error recording impressions: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record impressions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 记录产品点击数据
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordClick(Request $request)
    {
        try {
            // 记录请求数据用于调试
            Log::info('ProductAnalytics recordClick request', [
                'request_data' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'section_type' => 'nullable|string',
                'position' => 'nullable|integer',
                'device_type' => 'nullable|string',
                'url' => 'nullable|string',
                'referrer' => 'nullable|string',
                'timestamp' => 'nullable|date',
            ]);
            
            // 记录详细的点击信息
            $click = new ProductClick();
            $click->product_id = $validated['product_id'];
            $click->section_type = $validated['section_type'] ?? null;
            $click->position = $validated['position'] ?? null;
            $click->device_type = $validated['device_type'] ?? 'unknown';
            $click->url = $validated['url'] ?? null;
            $click->referrer = $validated['referrer'] ?? null;
            $click->ip_address = $request->ip();
            $click->user_agent = $request->userAgent();
            $click->clicked_at = isset($validated['timestamp']) ? Carbon::parse($validated['timestamp'])->format('Y-m-d H:i:s') : Carbon::now();
            $click->save();
            
            // 更新每日点击统计数据 - 修改为查找后更新以避免DB::raw问题
            $today = Carbon::today()->format('Y-m-d');
            
            // 查找现有记录
            $record = ProductDisplayStat::where('product_id', $validated['product_id'])
                ->where('section_type', $validated['section_type'] ?? null)
                ->where('date', $today)
                ->first();
                
            if ($record) {
                // 如果记录存在，直接增加计数
                $record->clicks = $record->clicks + 1;
                $record->updated_at = Carbon::now();
                $record->save();
            } else {
                // 如果记录不存在，创建新记录
                ProductDisplayStat::create([
                    'product_id' => $validated['product_id'],
                    'section_type' => $validated['section_type'] ?? null,
                    'date' => $today,
                    'impressions' => 0,
                    'clicks' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Click recorded successfully'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error recording click: ' . $e->getMessage(), [
                'exception' => $e,
                'sql' => $e->getSql() ?? 'unknown',
                'bindings' => $e->getBindings() ?? [],
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Database error when recording click',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error recording click: ' . $e->getMessage(), [
                'exception' => $e,
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error when recording click',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error recording click: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record click',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取单个产品的统计数据
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductStats(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // 解析日期范围
            $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
            $startDate = $request->input('start_date', Carbon::parse($endDate)->subDays(29)->format('Y-m-d'));
            
            // 获取产品统计数据
            $stats = ProductDisplayStat::where('product_id', $id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
                
            // 计算总统计数据
            $totalStats = [
                'impressions' => $stats->sum('impressions'),
                'clicks' => $stats->sum('clicks'),
                'ctr' => $stats->sum('impressions') > 0 
                    ? round(($stats->sum('clicks') / $stats->sum('impressions')) * 100, 2) 
                    : 0
            ];
            
            // 按日期分组
            $dailyStats = $stats->groupBy('date')
                ->map(function($items) {
                    return [
                        'date' => $items->first()->date,
                        'impressions' => $items->sum('impressions'),
                        'clicks' => $items->sum('clicks'),
                        'ctr' => $items->sum('impressions') > 0 
                            ? round(($items->sum('clicks') / $items->sum('impressions')) * 100, 2) 
                            : 0
                    ];
                })->values();
                
            // 按区域类型分组
            $sectionStats = $stats->groupBy('section_type')
                ->map(function($items, $key) {
                    return [
                        'section_type' => $key ?: 'unknown',
                        'impressions' => $items->sum('impressions'),
                        'clicks' => $items->sum('clicks'),
                        'ctr' => $items->sum('impressions') > 0 
                            ? round(($items->sum('clicks') / $items->sum('impressions')) * 100, 2) 
                            : 0
                    ];
                })->values();
                
            return response()->json([
                'status' => 'success',
                'data' => [
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'thumbnail' => $product->thumbnail
                    ],
                    'stats' => [
                        'total' => $totalStats,
                        'daily' => $dailyStats,
                        'by_section' => $sectionStats
                    ],
                    'date_range' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting product stats: ' . $e->getMessage(), [
                'exception' => $e,
                'product_id' => $id
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get product statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取仪表盘汇总数据
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardSummary(Request $request)
    {
        try {
            // 解析日期范围
            $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
            $startDate = $request->input('start_date', Carbon::parse($endDate)->subDays(29)->format('Y-m-d'));
            
            // 获取总体统计数据
            $overallStats = DB::table('product_display_stats')
                ->select(
                    DB::raw('SUM(impressions) as total_impressions'),
                    DB::raw('SUM(clicks) as total_clicks'),
                    DB::raw('CASE WHEN SUM(impressions) > 0 THEN ROUND((SUM(clicks) / SUM(impressions)) * 100, 2) ELSE 0 END as ctr')
                )
                ->whereBetween('date', [$startDate, $endDate])
                ->first();
                
            // 获取每日统计数据用于图表
            $dailyStats = DB::table('product_display_stats')
                ->select(
                    'date',
                    DB::raw('SUM(impressions) as impressions'),
                    DB::raw('SUM(clicks) as clicks')
                )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();
                
            // 获取热门产品
            $popularProducts = ProductDisplayStat::select(
                    'product_id',
                    DB::raw('SUM(impressions) as total_impressions'),
                    DB::raw('SUM(clicks) as total_clicks'),
                    DB::raw('CASE WHEN SUM(impressions) > 0 THEN ROUND((SUM(clicks) / SUM(impressions)) * 100, 2) ELSE 0 END as ctr')
                )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('product_id')
                ->orderByDesc('total_impressions')
                ->limit(10)
                ->with(['product:id,name,sku,price,stock,thumbnail'])
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->product_id,
                        'name' => $item->product->name ?? 'Unknown Product',
                        'sku' => $item->product->sku ?? '',
                        'thumbnail' => $item->product->thumbnail ?? '',
                        'impressions' => $item->total_impressions,
                        'clicks' => $item->total_clicks,
                        'ctr' => $item->ctr
                    ];
                });
                
            return response()->json([
                'status' => 'success',
                'data' => [
                    'overall' => $overallStats,
                    'daily_stats' => $dailyStats,
                    'popular_products' => $popularProducts,
                    'date_range' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting dashboard summary: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get dashboard summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取首页不同区域的性能数据
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSectionsPerformance(Request $request)
    {
        try {
            // 解析日期范围
            $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
            $startDate = $request->input('start_date', Carbon::parse($endDate)->subDays(29)->format('Y-m-d'));
            
            // 获取区域类型分布
            $sectionStats = DB::table('product_display_stats')
                ->select(
                    'section_type',
                    DB::raw('SUM(impressions) as impressions'),
                    DB::raw('SUM(clicks) as clicks'),
                    DB::raw('CASE WHEN SUM(impressions) > 0 THEN ROUND((SUM(clicks) / SUM(impressions)) * 100, 2) ELSE 0 END as ctr')
                )
                ->whereBetween('date', [$startDate, $endDate])
                ->whereNotNull('section_type')
                ->groupBy('section_type')
                ->orderByDesc('impressions')
                ->get()
                ->map(function($item) {
                    return [
                        'section_type' => $item->section_type,
                        'section_name' => $this->getSectionName($item->section_type),
                        'impressions' => $item->impressions,
                        'clicks' => $item->clicks,
                        'ctr' => $item->ctr
                    ];
                });
                
            return response()->json([
                'status' => 'success',
                'data' => [
                    'sections' => $sectionStats,
                    'date_range' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting section performance: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get section performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取区域类型的友好名称
     *
     * @param  string  $sectionType
     * @return string
     */
    protected function getSectionName($sectionType)
    {
        $sections = [
            'featured' => 'Featured Products',
            'new_arrivals' => 'New Arrivals',
            'sale' => 'On Sale',
            'popular' => 'Popular Products',
            'related' => 'Related Products',
            'custom' => 'Custom Section',
        ];
        
        return $sections[$sectionType] ?? ucfirst(str_replace('_', ' ', $sectionType));
    }
}