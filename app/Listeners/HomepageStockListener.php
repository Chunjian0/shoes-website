<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\StockChangedEvent;
use App\Services\HomepageStockService;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HomepageStockListener implements ShouldQueue
{
    /**
     * 首页库存服务
     */
    protected $homepageStockService;
    
    /**
     * 创建监听器实例
     *
     * @param HomepageStockService $homepageStockService
     * @return void
     */
    public function __construct(HomepageStockService $homepageStockService)
    {
        $this->homepageStockService = $homepageStockService;
    }

    /**
     * 处理事件
     *
     * @param StockChangedEvent $event
     * @return void
     */
    public function handle(StockChangedEvent $event)
    {
        try {
            // 获取产品ID和新库存量
            $productId = $event->productId;
            $newStock = $event->newQuantity;
            
            // 查找产品
            $product = Product::find($productId);
            if (!$product) {
                Log::warning('Product not found for stock event', [
                    'product_id' => $productId
                ]);
                return;
            }
            
            // 获取最低库存阈值
            $threshold = $this->homepageStockService->getMinStockThreshold();
            
            // 如果新库存低于阈值，检查产品是否在首页显示并处理
            if ($newStock < $threshold) {
                $inHomepage = false;
                
                // 检查产品是否为特色产品
                if ($product->is_featured) {
                    $product->is_featured = false;
                    $inHomepage = true;
                }
                
                // 检查产品是否为新品
                if ($product->is_new_arrival) {
                    $product->is_new_arrival = false;
                    $inHomepage = true;
                }
                
                // 检查产品是否为促销产品
                if ($product->is_sale) {
                    $product->is_sale = false;
                    $inHomepage = true;
                }
                
                // 如果产品在首页显示，保存修改并触发事件
                if ($inHomepage) {
                    $product->save();
                    
                    // 触发首页更新事件，使用系统用户作为更新者
                    event(new \App\Events\HomepageUpdatedEvent(
                        'low_stock_products',
                        'system@optic-system.com',
                        [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'stock' => $newStock,
                            'threshold' => $threshold
                        ]
                    ));
                    
                    Log::info('Product removed from homepage due to low stock', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'stock' => $newStock,
                        'threshold' => $threshold
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to process homepage stock event', [
                'error' => $e->getMessage(),
                'product_id' => $event->productId ?? null
            ]);
        }
    }
} 