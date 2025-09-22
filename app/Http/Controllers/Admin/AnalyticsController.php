<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDisplayStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * 显示分析仪表板
     */
    public function dashboard(Request $request)
    {
        $dates = $this->getDateRange($request);
        
        // 获取总体统计数据
        $overallStats = DB::table('product_display_stats')
            ->select(
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('CASE WHEN SUM(impressions) > 0 THEN ROUND((SUM(clicks) / SUM(impressions)) * 100, 2) ELSE 0 END as ctr')
            )
            ->whereBetween('date', [$dates['start'], $dates['end']])
            ->first();
            
        // 获取每日统计数据用于图表
        $dailyStats = DB::table('product_display_stats')
            ->select(
                'date',
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(clicks) as clicks')
            )
            ->whereBetween('date', [$dates['start'], $dates['end']])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // 获取区域类型分布
        $sectionStats = DB::table('product_display_stats')
            ->select(
                'section_type',
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(clicks) as clicks'),
                DB::raw('CASE WHEN SUM(impressions) > 0 THEN ROUND((SUM(clicks) / SUM(impressions)) * 100, 2) ELSE 0 END as ctr')
            )
            ->whereBetween('date', [$dates['start'], $dates['end']])
            ->whereNotNull('section_type')
            ->groupBy('section_type')
            ->orderByDesc('impressions')
            ->get();
            
        // 获取设备类型分布
        $deviceStats = DB::table('product_impressions')
            ->select(
                'device_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('ROUND((COUNT(*) / (SELECT COUNT(*) FROM product_impressions WHERE viewed_at BETWEEN ? AND ?)) * 100, 2) as percentage')
            )
            ->whereBetween('viewed_at', [
                Carbon::parse($dates['start'])->startOfDay(),
                Carbon::parse($dates['end'])->endOfDay()
            ])
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->setBindings([
                Carbon::parse($dates['start'])->startOfDay(),
                Carbon::parse($dates['end'])->endOfDay(),
                Carbon::parse($dates['start'])->startOfDay(),
                Carbon::parse($dates['end'])->endOfDay()
            ])
            ->get();
            
        // 获取热门产品
        $popularProducts = ProductDisplayStat::select(
                'product_id',
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('CASE WHEN SUM(impressions) > 0 THEN ROUND((SUM(clicks) / SUM(impressions)) * 100, 2) ELSE 0 END as ctr')
            )
            ->whereBetween('date', [$dates['start'], $dates['end']])
            ->groupBy('product_id')
            ->orderByDesc('total_impressions')
            ->limit(10)
            ->with(['product:id,name,sku,price,stock,thumbnail'])
            ->get();
            
        // 图表数据
        $chartData = [
            'dates' => $dailyStats->pluck('date')->toArray(),
            'impressions' => $dailyStats->pluck('impressions')->toArray(),
            'clicks' => $dailyStats->pluck('clicks')->toArray(),
        ];
            
        return view('admin.analytics.dashboard', compact(
            'overallStats',
            'sectionStats',
            'deviceStats',
            'popularProducts',
            'chartData',
            'dates'
        ));
    }
    
    /**
     * 显示产品分析
     */
    public function productAnalytics(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $dates = $this->getDateRange($request);
        
        // 获取产品统计数据
        $stats = ProductDisplayStat::where('product_id', $product->id)
            ->whereBetween('date', [$dates['start'], $dates['end']])
            ->get();
            
        $overallStats = [
            'total_impressions' => $stats->sum('impressions'),
            'total_clicks' => $stats->sum('clicks'),
            'ctr' => $stats->sum('impressions') > 0 
                ? round(($stats->sum('clicks') / $stats->sum('impressions')) * 100, 2) 
                : 0,
        ];
        
        // 按日期分组
        $dailyStats = $stats->groupBy('date')
            ->map(function($items) {
                return [
                    'impressions' => $items->sum('impressions'),
                    'clicks' => $items->sum('clicks'),
                ];
            });
            
        // 按区域类型分组
        $sectionStats = $stats->groupBy('section_type')
            ->map(function($items) {
                return [
                    'impressions' => $items->sum('impressions'),
                    'clicks' => $items->sum('clicks'),
                    'ctr' => $items->sum('impressions') > 0 
                        ? round(($items->sum('clicks') / $items->sum('impressions')) * 100, 2) 
                        : 0,
                ];
            });
            
        // 图表数据
        $chartData = [
            'dates' => $dailyStats->keys()->toArray(),
            'impressions' => $dailyStats->pluck('impressions')->toArray(),
            'clicks' => $dailyStats->pluck('clicks')->toArray(),
        ];
            
        return view('admin.analytics.product', compact(
            'product',
            'overallStats',
            'sectionStats',
            'chartData',
            'dates'
        ));
    }

    /**
     * 显示A/B测试管理
     */
    public function abTests(Request $request)
    {
        $abTests = \App\Models\AbTest::with('results')
            ->orderByDesc('created_at')
            ->paginate(10);
            
        return view('admin.analytics.ab-tests', compact('abTests'));
    }
    
    /**
     * 处理日期范围
     */
    private function getDateRange(Request $request)
    {
        $range = $request->input('range', '30d');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // 如果提供了自定义日期范围
        if ($startDate && $endDate) {
            return [
                'start' => $startDate,
                'end' => $endDate,
                'range' => 'custom'
            ];
        }
        
        $end = Carbon::today()->toDateString();
        $start = null;
        
        switch ($range) {
            case '7d':
                $start = Carbon::today()->subDays(6)->toDateString();
                break;
            case '30d':
                $start = Carbon::today()->subDays(29)->toDateString();
                break;
            case '90d':
                $start = Carbon::today()->subDays(89)->toDateString();
                break;
            case 'month':
                $start = Carbon::today()->startOfMonth()->toDateString();
                break;
            case 'year':
                $start = Carbon::today()->startOfYear()->toDateString();
                break;
            default:
                $start = Carbon::today()->subDays(29)->toDateString();
                $range = '30d';
                break;
        }
        
        return [
            'start' => $start,
            'end' => $end,
            'range' => $range
        ];
    }
} 