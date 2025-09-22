<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\HomepageUpdatedEvent;

class UpdateNewProductsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-new-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新产品的新品状态，移除过期的新品展示';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始更新产品新品状态...');
        
        try {
            // 移除过期新品
            $this->removeExpiredNewProducts();
            
            // 添加新产品到新品区域（如果设置了自动添加）
            $this->addNewProducts();
            
            $this->info('产品新品状态更新完成！');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('更新产品新品状态时发生错误：' . $e->getMessage());
            Log::error('更新产品新品状态时发生错误', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
    
    /**
     * 移除过期的新品展示
     */
    protected function removeExpiredNewProducts()
    {
        $today = Carbon::today();
        
        // 查找所有设置了过期日期且日期已过的产品
        $expiredProducts = Product::where('is_new_arrival', true)
            ->whereNotNull('new_until_date')
            ->where('new_until_date', '<', $today)
            ->get();
        
        $count = 0;
        
        foreach ($expiredProducts as $product) {
            $product->is_new_arrival = false;
            $product->save();
            
            $count++;
            $this->info("已移除过期新品：{$product->name} (ID: {$product->id})");
            
            Log::info('产品已从新品区域自动移除', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'new_until_date' => $product->new_until_date
            ]);
        }
        
        if ($count > 0) {
            // 触发首页更新事件
            event(new HomepageUpdatedEvent(
                'new_products_auto_removed',
                'system@optic-system.com',
                ['count' => $count]
            ));
            
            $this->info("共有 {$count} 个过期新品被移除");
        } else {
            $this->info("没有发现过期新品");
        }
    }
    
    /**
     * 添加新产品到新品区域
     */
    protected function addNewProducts()
    {
        // 检查是否开启自动添加新品
        $autoAddEnabled = Setting::getValue('auto_add_new_products', 'false');
        
        if ($autoAddEnabled !== 'true') {
            $this->info("自动添加新品功能未启用");
            return;
        }
        
        // 获取新品展示天数
        $displayDays = (int)Setting::getValue('new_products_display_days', 30);
        
        // 计算截止日期
        $endDate = Carbon::today()->addDays($displayDays);
        
        // 查找最近创建且未设置为新品的产品
        $recentProducts = Product::where('is_new_arrival', false)
            ->whereNull('new_until_date')
            ->where('created_at', '>=', Carbon::now()->subDay()) // 查找24小时内创建的产品
            ->get();
        
        $count = 0;
        
        foreach ($recentProducts as $product) {
            $product->is_new_arrival = true;
            $product->new_until_date = $endDate;
            $product->save();
            
            $count++;
            $this->info("已自动添加新品：{$product->name} (ID: {$product->id})，展示截止日期：{$endDate->format('Y-m-d')}");
            
            Log::info('产品已自动添加到新品区域', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'new_until_date' => $endDate->format('Y-m-d')
            ]);
        }
        
        if ($count > 0) {
            // 触发首页更新事件
            event(new HomepageUpdatedEvent(
                'new_products_auto_added',
                'system@optic-system.com',
                ['count' => $count]
            ));
            
            $this->info("共有 {$count} 个产品自动添加到新品区域");
        } else {
            $this->info("没有发现需要自动添加的新品");
        }
    }
}
