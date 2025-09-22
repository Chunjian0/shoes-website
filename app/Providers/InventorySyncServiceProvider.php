<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Stock;
use App\Models\Product;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventorySyncServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 监听Stock模型的saved事件
        Stock::saved(function ($stock) {
            $this->syncProductInventory($stock->product_id);
        });

        // 监听Stock模型的deleted事件
        Stock::deleted(function ($stock) {
            $this->syncProductInventory($stock->product_id);
        });
    }

    /**
     * 同步产品库存
     */
    protected function syncProductInventory(int $productId): void
    {
        try {
            // 计算该产品在所有仓库的总库存
            $totalStock = Stock::where('product_id', $productId)->sum('quantity');
            
            // 更新产品的inventory_count
            DB::table('products')
                ->where('id', $productId)
                ->update(['inventory_count' => $totalStock]);
                
            Log::info('Product inventory synced', [
                'product_id' => $productId,
                'total_stock' => $totalStock
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync product inventory', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 