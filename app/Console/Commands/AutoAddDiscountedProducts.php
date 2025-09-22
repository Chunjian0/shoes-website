<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;
use App\Events\HomepageUpdatedEvent;
use Illuminate\Support\Facades\Log;

class AutoAddDiscountedProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:auto-add-discounted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically add discounted products to the sale section of the homepage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logInfo = [];
        $logInfo[] = "Auto Add Discounted Products Command started at " . Carbon::now()->format('Y-m-d H:i:s');
        
        // 检查自动添加折扣商品功能是否启用
        $autoAddSaleProducts = Setting::getValue('auto_add_sale_products', 'false') === 'true';
        
        if (!$autoAddSaleProducts) {
            $logInfo[] = "Auto add sale products feature is disabled. Exiting.";
            $this->info("Auto add sale products feature is disabled.");
            Log::info(implode("\n", $logInfo));
            return 0;
        }
        
        // 获取折扣商品展示天数设置
        $saleProductsDisplayDays = (int)Setting::getValue('sale_products_display_days', 30);
        $logInfo[] = "Sale products display days: $saleProductsDisplayDays";
        
        // 找出所有有折扣但尚未添加到促销区域的商品
        $discountedProducts = Product::where('is_active', true)
            ->whereNotNull('discount_percentage')
            ->where('discount_percentage', '>', 0)
            ->where(function($query) {
                $query->whereNull('discount_end_date')
                      ->orWhere('discount_end_date', '>', Carbon::now());
            })
            ->where('is_sale', false)
            ->get();
        
        $logInfo[] = "Found " . $discountedProducts->count() . " discounted products not yet in sale section";
        
        if ($discountedProducts->count() === 0) {
            $logInfo[] = "No new discounted products to add. Exiting.";
            $this->info("No new discounted products to add.");
            Log::info(implode("\n", $logInfo));
            return 0;
        }
        
        $addedCount = 0;
        $failedCount = 0;
        
        // 获取最大的排序值
        $maxSaleOrder = Product::where('is_sale', true)->max('sale_order') ?? 0;
        
        foreach ($discountedProducts as $product) {
            try {
                // 设置促销到期日期
                $saleUntilDate = null;
                
                // 如果产品有折扣结束日期，使用它，否则从当前日期加上设置的天数
                if ($product->discount_end_date) {
                    $saleUntilDate = $product->discount_end_date;
                } else {
                    $saleUntilDate = Carbon::now()->addDays($saleProductsDisplayDays);
                }
                
                // 更新商品
                $product->is_sale = true;
                $product->sale_order = ++$maxSaleOrder;
                $product->sale_until_date = $saleUntilDate;
                $product->save();
                
                $addedCount++;
                $logInfo[] = "Added product ID {$product->id} ({$product->name}) to sale section. Expiry: {$saleUntilDate->format('Y-m-d')}";
            } catch (\Exception $e) {
                $failedCount++;
                $logInfo[] = "Failed to add product ID {$product->id}: " . $e->getMessage();
                $this->error("Failed to add product ID {$product->id}: " . $e->getMessage());
            }
        }
        
        $logInfo[] = "Added $addedCount products to sale section. Failed: $failedCount";
        $this->info("Added $addedCount products to sale section. Failed: $failedCount");
        
        // 如果有产品被添加，触发首页更新事件
        if ($addedCount > 0) {
            event(new HomepageUpdatedEvent('sale_products_auto_added', 'system', [
                'added_count' => $addedCount,
                'failed_count' => $failedCount
            ]));
        }
        
        Log::info(implode("\n", $logInfo));
        return 0;
    }
}
