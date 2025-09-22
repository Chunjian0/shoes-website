<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class HomepageSettingsSeeder extends Seeder
{
    /**
     * 运行数据库 Seeder
     */
    public function run(): void
    {
        $settings = [
            'featured_products_title' => [
                'value' => 'Featured Products',
                'label' => 'Featured Products Title'
            ],
            'featured_products_subtitle' => [
                'value' => 'Our carefully selected premium products known for exceptional quality',
                'label' => 'Featured Products Subtitle'
            ],
            'featured_products_button_text' => [
                'value' => 'View All Featured Products',
                'label' => 'Featured Products Button Text'
            ],
            'featured_products_button_link' => [
                'value' => '/products?featured=true',
                'label' => 'Featured Products Button Link'
            ],
            'carousel_autoplay' => [
                'value' => '1',
                'label' => 'Autoplay Carousel'
            ],
            'carousel_delay' => [
                'value' => '5000',
                'label' => 'Carousel Delay'
            ],
            'carousel_transition' => [
                'value' => 'slide',
                'label' => 'Carousel Transition'
            ],
            'carousel_show_navigation' => [
                'value' => '1',
                'label' => 'Show Carousel Navigation'
            ],
            'carousel_show_indicators' => [
                'value' => '1',
                'label' => 'Show Carousel Indicators'
            ],
            'show_promotion' => [
                'value' => '1',
                'label' => 'Show Promotion'
            ],
            'new_products_days' => [
                'value' => '30',
                'label' => 'New Products Days'
            ]
        ];
        
        // 检查settings表中是否存在label字段
        $hasLabelField = Schema::hasColumn('settings', 'label');
        
        foreach ($settings as $key => $setting) {
            try {
                $data = [
                    'value' => $setting['value'],
                    'group' => 'homepage'
                ];
                
                // 如果存在label字段，则添加label
                if ($hasLabelField) {
                    $data['label'] = $setting['label'];
                }
                
                Setting::updateOrCreate(
                    ['key' => $key],
                    $data
                );
                
                $this->command->info("Created homepage setting: {$key}");
            } catch (\Exception $e) {
                Log::error("Failed to create homepage setting: {$key}", [
                    'error' => $e->getMessage()
                ]);
                
                $this->command->error("Failed to create homepage setting: {$key} - {$e->getMessage()}");
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
            try {
                Setting::where('key', $key)->delete();
                $this->command->info("Deleted old banner setting: {$key}");
            } catch (\Exception $e) {
                Log::error("Failed to delete old banner setting: {$key}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
} 