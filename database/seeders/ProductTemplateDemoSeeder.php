<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductTemplate;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductTemplateDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清除已有数据
        DB::table('product_template_product')->truncate();
        ProductTemplate::query()->delete();
        
        // 创建测试用的产品分类
        $categoryId = $this->ensureCategory();
        
        // 创建一些测试用的产品模板
        $featuredTemplate = ProductTemplate::create([
            'name' => 'Featured Demo Template',
            'description' => 'This is a featured demo template for demonstration purposes.',
            'category_id' => $categoryId,
            'is_active' => true,
            'is_featured' => true,
            'is_new_arrival' => false,
            'is_sale' => false,
            'parameters' => [
                [
                    'name' => 'Color',
                    'values' => ['Red', 'Blue', 'Green', 'Black']
                ],
                [
                    'name' => 'Size',
                    'values' => ['Small', 'Medium', 'Large']
                ]
            ],
            'images' => [
                [
                    'url' => 'demo/featured-template.jpg',
                    'alt' => 'Featured Template'
                ]
            ]
        ]);
        
        $newArrivalTemplate = ProductTemplate::create([
            'name' => 'New Arrival Demo Template',
            'description' => 'This is a new arrival demo template for demonstration purposes.',
            'category_id' => $categoryId,
            'is_active' => true,
            'is_featured' => false,
            'is_new_arrival' => true,
            'is_sale' => false,
            'parameters' => [
                [
                    'name' => 'Material',
                    'values' => ['Plastic', 'Metal', 'Glass']
                ],
                [
                    'name' => 'Weight',
                    'values' => ['Light', 'Medium', 'Heavy']
                ]
            ],
            'images' => [
                [
                    'url' => 'demo/new-arrival-template.jpg',
                    'alt' => 'New Arrival Template'
                ]
            ]
        ]);
        
        $saleTemplate = ProductTemplate::create([
            'name' => 'Sale Demo Template',
            'description' => 'This is a sale demo template for demonstration purposes.',
            'category_id' => $categoryId,
            'is_active' => true,
            'is_featured' => false,
            'is_new_arrival' => false,
            'is_sale' => true,
            'parameters' => [
                [
                    'name' => 'Type',
                    'values' => ['Type A', 'Type B', 'Type C']
                ],
                [
                    'name' => 'Quality',
                    'values' => ['Standard', 'Premium', 'Luxury']
                ]
            ],
            'images' => [
                [
                    'url' => 'demo/sale-template.jpg',
                    'alt' => 'Sale Template'
                ]
            ]
        ]);
        
        // 创建并关联产品
        $this->createAndLinkProducts($featuredTemplate, 3);
        $this->createAndLinkProducts($newArrivalTemplate, 3);
        $this->createAndLinkProducts($saleTemplate, 3);
        
        $this->command->info('Demo product templates and linked products created successfully.');
    }
    
    /**
     * 确保测试分类存在
     */
    private function ensureCategory()
    {
        $category = ProductCategory::firstOrCreate(
            ['name' => 'Demo Category'],
            [
                'description' => 'Demo category for test templates',
                'is_active' => true
            ]
        );
        
        return $category->id;
    }
    
    /**
     * 创建并关联产品到模板
     */
    private function createAndLinkProducts(ProductTemplate $template, int $count = 3)
    {
        for ($i = 1; $i <= $count; $i++) {
            // 获取产品模型的所有可填充字段
            $fillable = (new Product())->getFillable();
            
            // 准备数据
            $productData = [
                'name' => "{$template->name} Product {$i}",
                'category_id' => $template->category_id,
                'sku' => strtolower(str_replace(' ', '-', $template->name)) . "-{$i}-" . Str::random(5),
                'description' => "Demo product {$i} for {$template->name}",
                'price' => rand(1000, 5000) / 100,
                'stock_quantity' => rand(10, 100),
                'is_active' => true
            ];
            
            // 如果Product模型需要code字段
            if (in_array('code', $fillable)) {
                $productData['code'] = 'DEMO-' . strtoupper(Str::random(5));
            }
            
            // 如果Product模型需要barcode字段
            if (in_array('barcode', $fillable)) {
                $productData['barcode'] = '123' . rand(1000000, 9999999);
            }
            
            // 检查其他可能的必填字段
            if (in_array('selling_price', $fillable)) {
                $productData['selling_price'] = $productData['price'];
            }
            
            // 检查product字段的实际名称
            try {
                $product = Product::create($productData);
                
                // 通过中间表关联产品和模板
                if ($template->parameters && is_array($template->parameters) && count($template->parameters) > 0) {
                    $paramGroup = $template->parameters[0]['name'];
                    $paramValue = $template->parameters[0]['values'][array_rand($template->parameters[0]['values'])];
                    
                    DB::table('product_template_product')->insert([
                        'product_template_id' => $template->id,
                        'product_id' => $product->id,
                        'parameter_group' => "{$paramGroup}={$paramValue}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    // 如果没有参数，直接关联
                    DB::table('product_template_product')->insert([
                        'product_template_id' => $template->id,
                        'product_id' => $product->id,
                        'parameter_group' => 'default',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            } catch (\Exception $e) {
                $this->command->error("Error creating product: " . $e->getMessage());
            }
        }
    }
}
