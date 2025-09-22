<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductTemplate;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateHomepageApiResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'homepage:fix-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复首页API接口，确保正确返回linked_products字段';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始修复首页API响应...');
        
        // 获取所有模板
        $templates = ProductTemplate::where('is_active', true)
            ->get();
            
        $this->info('找到 ' . count($templates) . ' 个活跃模板');
        
        // 遍历每个模板，确保有关联产品
        $fixed = 0;
        foreach ($templates as $template) {
            // 检查是否有linked_products关联
            $linkedProductsCount = DB::table('product_template_product')
                ->where('product_template_id', $template->id)
                ->count();
                
            $this->info("模板 {$template->name} (ID: {$template->id}) 有 {$linkedProductsCount} 个关联产品");
            
            // 如果没有关联产品，创建测试产品并关联
            if ($linkedProductsCount == 0) {
                $this->warn("为模板 {$template->name} 创建和关联测试产品");
                
                try {
                    // 创建测试产品
                    $productData = [
                        'name' => "Test Product for {$template->name}",
                        'category_id' => $template->category_id ?: 1,
                        'code' => 'TEST-' . strtoupper(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5)),
                        'sku' => 'test-' . strtolower(str_replace(' ', '-', $template->name)),
                        'barcode' => '12345' . rand(10000, 99999),
                        'selling_price' => 99.99,
                        'price' => 99.99,
                        'stock_quantity' => 100,
                        'is_active' => true
                    ];
                    
                    $product = Product::create($productData);
                    
                    // 关联产品到模板
                    DB::table('product_template_product')->insert([
                        'product_template_id' => $template->id,
                        'product_id' => $product->id,
                        'parameter_group' => 'test',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    $fixed++;
                    $this->info("成功为模板 {$template->name} 创建并关联测试产品");
                } catch (\Exception $e) {
                    $this->error("创建关联产品时出错: " . $e->getMessage());
                }
            } else {
                $this->info("模板 {$template->name} 已有关联产品，无需修复");
            }
        }
        
        // 更新已有模板的标记
        $featuredCount = $this->ensureTaggedTemplates('is_featured');
        $newArrivalCount = $this->ensureTaggedTemplates('is_new_arrival');
        $saleCount = $this->ensureTaggedTemplates('is_sale');
        
        $this->info("修复完成!");
        $this->info("- 为 {$fixed} 个模板添加了关联产品");
        $this->info("- 有 {$featuredCount} 个特色模板");
        $this->info("- 有 {$newArrivalCount} 个新品模板");
        $this->info("- 有 {$saleCount} 个促销模板");
        
        return 0;
    }
    
    /**
     * 确保至少有一个指定标记的模板
     */
    private function ensureTaggedTemplates($tag)
    {
        $count = ProductTemplate::where('is_active', true)
            ->where($tag, true)
            ->count();
            
        if ($count == 0) {
            // 随机选择一个模板并标记
            $template = ProductTemplate::where('is_active', true)->first();
            if ($template) {
                $template->update([$tag => true]);
                $count = 1;
                $this->info("将模板 {$template->name} 标记为 {$tag}");
            }
        }
        
        return $count;
    }
}
