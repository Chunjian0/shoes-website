<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use App\Models\NotificationSetting;
use App\Events\HomepageUpdatedEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HomepageStockService
{
    /**
     * 获取首页产品最低库存阈值
     *
     * @return int
     */
    public function getMinStockThreshold(): int
    {
        return Cache::remember('homepage_min_stock_threshold', 3600, function () {
            $setting = Setting::where('key', 'homepage_min_stock_threshold')->first();
            return $setting ? (int)$setting->value : 5; // 默认阈值为5
        });
    }
    
    /**
     * 更新首页产品最低库存阈值
     *
     * @param int $threshold
     * @return bool
     */
    public function updateMinStockThreshold(int $threshold): bool
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'homepage_min_stock_threshold'],
                [
                    'value' => (string)$threshold,
                    'type' => 'integer',
                    'group' => 'homepage',
                    'label' => '首页产品最低库存阈值',
                    'description' => '库存低于此值的产品将自动从首页中移除'
                ]
            );
            
            // 清除缓存
            Cache::forget('homepage_min_stock_threshold');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update homepage min stock threshold', [
                'error' => $e->getMessage(),
                'threshold' => $threshold
            ]);
            return false;
        }
    }
    
    /**
     * 过滤所有低库存的首页产品
     *
     * @return array 被移除的产品ID数组
     */
    public function filterLowStockProducts(): array
    {
        try {
            $threshold = $this->getMinStockThreshold();
            $removedProducts = [];
            
            // 获取特色产品
            $featuredProducts = Product::where('is_featured', true)->get();
            // 过滤出低库存的产品
            $lowStockFeaturedProducts = $featuredProducts->filter(function ($product) use ($threshold) {
                $totalStock = DB::table('stocks')->where('product_id', $product->id)->sum('quantity');
                return $totalStock < $threshold;
            });
            $this->processLowStockProducts($lowStockFeaturedProducts, 'is_featured', $removedProducts);
            
            // 获取新品
            $newProducts = Product::where('is_new_arrival', true)->get();
            // 过滤出低库存的产品
            $lowStockNewProducts = $newProducts->filter(function ($product) use ($threshold) {
                $totalStock = DB::table('stocks')->where('product_id', $product->id)->sum('quantity');
                return $totalStock < $threshold;
            });
            $this->processLowStockProducts($lowStockNewProducts, 'is_new_arrival', $removedProducts);
            
            // 获取促销产品
            $saleProducts = Product::where('is_sale', true)->get();
            // 过滤出低库存的产品
            $lowStockSaleProducts = $saleProducts->filter(function ($product) use ($threshold) {
                $totalStock = DB::table('stocks')->where('product_id', $product->id)->sum('quantity');
                return $totalStock < $threshold;
            });
            $this->processLowStockProducts($lowStockSaleProducts, 'is_sale', $removedProducts);
            
            // 如果有产品被移除，触发通知事件
            if (count($removedProducts) > 0) {
                event(new HomepageUpdatedEvent(
                    'low_stock_products',
                    'system@optic-system.com',
                    [
                        'count' => count($removedProducts),
                        'products' => $removedProducts
                    ]
                ));
            }
            
            return $removedProducts;
        } catch (\Exception $e) {
            Log::error('Failed to filter low stock homepage products', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * 获取首页低库存产品
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockFeaturedProducts()
    {
        $threshold = $this->getMinStockThreshold();
        $lowStockProducts = [];
        
        // 特色产品
        $featuredProducts = Product::where('is_featured', true)->get();
        foreach ($featuredProducts as $product) {
            $totalStock = DB::table('stocks')->where('product_id', $product->id)->sum('quantity');
            if ($totalStock < $threshold) {
                $product->homepage_section = 'featured';
                $lowStockProducts[] = $product;
            }
        }
        
        // 新品
        $newProducts = Product::where('is_new_arrival', true)->get();
        foreach ($newProducts as $product) {
            $totalStock = DB::table('stocks')->where('product_id', $product->id)->sum('quantity');
            if ($totalStock < $threshold) {
                $product->homepage_section = 'new_arrival';
                $lowStockProducts[] = $product;
            }
        }
        
        // 促销产品
        $saleProducts = Product::where('is_sale', true)->get();
        foreach ($saleProducts as $product) {
            $totalStock = DB::table('stocks')->where('product_id', $product->id)->sum('quantity');
            if ($totalStock < $threshold) {
                $product->homepage_section = 'sale';
                $lowStockProducts[] = $product;
            }
        }
        
        return new \Illuminate\Database\Eloquent\Collection($lowStockProducts);
    }
    
    /**
     * 处理低库存产品
     *
     * @param \Illuminate\Database\Eloquent\Collection $products
     * @param string $flagField
     * @param array &$removedProducts
     * @return void
     */
    protected function processLowStockProducts($products, string $flagField, array &$removedProducts): void
    {
        foreach ($products as $product) {
            $product->$flagField = false;
            $product->save();
            
            $totalStock = DB::table('stocks')->where('product_id', $product->id)->sum('quantity');
            
            $removedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock' => $totalStock,
                'section' => $this->getSectionNameFromFlag($flagField)
            ];
            
            Log::info("Removed product from homepage due to low stock", [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'section' => $this->getSectionNameFromFlag($flagField),
                'stock' => $totalStock
            ]);
        }
    }
    
    /**
     * 从标志字段获取区域名称
     *
     * @param string $flagField
     * @return string
     */
    protected function getSectionNameFromFlag(string $flagField): string
    {
        $map = [
            'is_featured' => 'Featured Products',
            'is_new_arrival' => 'New Arrivals',
            'is_sale' => 'Sale Products'
        ];
        
        return $map[$flagField] ?? 'Unknown Section';
    }
} 