<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class HomepageDemoSeeder extends Seeder
{
    /**
     * 运行数据库种子
     */
    public function run(): void
    {
        $this->command->info('Creating demo homepage content...');
        
        // 检查banner表是否存在
        if (Schema::hasTable('banners')) {
            $this->createDemoBanners();
        } else {
            $this->command->warn('Banners table does not exist, skipping banner creation');
        }
        
        // 更新首页设置
        $this->updateHomepageSettings();
        
        $this->command->info('Demo homepage content created successfully!');
    }
    
    /**
     * 创建示例轮播图数据
     */
    protected function createDemoBanners(): void
    {
        try {
            // 先检查是否已有banners数据
            $existingCount = DB::table('banners')->count();
            if ($existingCount > 0) {
                $this->command->info("Banners already exist ({$existingCount} found). Skipping demo banners creation.");
                return;
            }
            
            // 创建示例轮播图
            $demoBanners = [
                [
                    'title' => 'Premium Eyewear Collection',
                    'subtitle' => 'Discover the perfect pair for your style and vision needs',
                    'button_text' => 'Shop Now',
                    'button_link' => '/products',
                    'is_active' => true,
                    'order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'New Spring Styles',
                    'subtitle' => 'Fresh designs for the new season',
                    'button_text' => 'Explore',
                    'button_link' => '/products?category=new-arrivals',
                    'is_active' => true,
                    'order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Designer Sunglasses',
                    'subtitle' => 'Protection and style combined',
                    'button_text' => 'View Collection',
                    'button_link' => '/products?category=sunglasses',
                    'is_active' => true,
                    'order' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];
            
            foreach ($demoBanners as $banner) {
                DB::table('banners')->insert($banner);
                $this->command->line("Created banner: {$banner['title']}");
            }
            
        } catch (\Exception $e) {
            $this->command->error("Failed to create demo banners: {$e->getMessage()}");
            Log::error("Failed to create demo banners", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    
    /**
     * 更新首页设置
     */
    protected function updateHomepageSettings(): void
    {
        try {
            $settings = [
                'featured_products_title' => [
                    'value' => 'Featured Products',
                    'label' => 'Featured Products Title',
                    'type' => 'string',
                    'description' => 'Title for the featured products section'
                ],
                'featured_products_subtitle' => [
                    'value' => 'Our carefully selected premium eyewear known for exceptional quality and style',
                    'label' => 'Featured Products Subtitle',
                    'type' => 'string',
                    'description' => 'Subtitle for the featured products section'
                ],
                'featured_products_button_text' => [
                    'value' => 'View All Featured Products',
                    'label' => 'Featured Products Button Text',
                    'type' => 'string',
                    'description' => 'Text for the featured products button'
                ],
                'featured_products_button_link' => [
                    'value' => '/products?featured=true',
                    'label' => 'Featured Products Button Link',
                    'type' => 'string',
                    'description' => 'Link for the featured products button'
                ],
                'carousel_autoplay' => [
                    'value' => true,
                    'label' => 'Autoplay Carousel',
                    'type' => 'boolean',
                    'description' => 'Whether the carousel should automatically rotate'
                ],
                'carousel_delay' => [
                    'value' => 5000,
                    'label' => 'Carousel Delay',
                    'type' => 'integer',
                    'description' => 'Time in milliseconds between slides'
                ],
                'carousel_transition' => [
                    'value' => 'slide',
                    'label' => 'Carousel Transition',
                    'type' => 'string',
                    'description' => 'Type of transition between slides'
                ],
                'carousel_show_navigation' => [
                    'value' => true,
                    'label' => 'Show Carousel Navigation',
                    'type' => 'boolean',
                    'description' => 'Whether to show navigation arrows'
                ],
                'carousel_show_indicators' => [
                    'value' => true,
                    'label' => 'Show Carousel Indicators',
                    'type' => 'boolean',
                    'description' => 'Whether to show indicators for slide position'
                ],
            ];
            
            // 检查settings表结构
            $hasType = Schema::hasColumn('settings', 'type');
            $hasLabel = Schema::hasColumn('settings', 'label');
            $hasDescription = Schema::hasColumn('settings', 'description');
            
            foreach ($settings as $key => $setting) {
                $data = [
                    'key' => $key,
                    'value' => is_bool($setting['value']) ? ($setting['value'] ? '1' : '0') : $setting['value'],
                    'group' => 'homepage',
                    'updated_at' => now()
                ];
                
                if ($hasLabel) {
                    $data['label'] = $setting['label'];
                }
                
                if ($hasType) {
                    $data['type'] = $setting['type'];
                }
                
                if ($hasDescription) {
                    $data['description'] = $setting['description'];
                }
                
                // 检查设置是否已存在
                $exists = DB::table('settings')->where('key', $key)->exists();
                
                if ($exists) {
                    // 更新现有设置
                    DB::table('settings')->where('key', $key)->update($data);
                    $this->command->line("Updated setting: {$key}");
                } else {
                    // 添加创建时间
                    $data['created_at'] = now();
                    
                    // 创建新设置
                    DB::table('settings')->insert($data);
                    $this->command->line("Created setting: {$key}");
                }
            }
            
            // 删除旧的单个banner相关设置
            $oldBannerKeys = [
                'banner_title',
                'banner_subtitle', 
                'banner_button_text', 
                'banner_button_link', 
                'banner_image_url',
                'banner_media_id'
            ];
            
            foreach ($oldBannerKeys as $key) {
                if (DB::table('settings')->where('key', $key)->exists()) {
                    DB::table('settings')->where('key', $key)->delete();
                    $this->command->line("Deleted old setting: {$key}");
                }
            }
            
        } catch (\Exception $e) {
            $this->command->error("Failed to update homepage settings: {$e->getMessage()}");
            Log::error("Failed to update homepage settings", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
