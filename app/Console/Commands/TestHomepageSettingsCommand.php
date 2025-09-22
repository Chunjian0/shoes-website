<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\HomepageSettingsAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestHomepageSettingsCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'test:homepage-settings';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Test the HomepageSettingsAdapter and display the output';

    /**
     * 执行命令
     */
    public function handle(HomepageSettingsAdapter $adapter)
    {
        $this->info('Testing HomepageSettingsAdapter...');
        
        try {
            // 获取首页设置
            $response = $adapter->getHomepageSettings();
            
            // 解析响应内容
            $content = json_decode($response->getContent(), true);
            
            if ($content === null) {
                $this->error('Failed to parse response JSON: ' . json_last_error_msg());
                return 1;
            }
            
            // 输出响应内容
            $this->info('Response status code: ' . $response->getStatusCode());
            $this->info('Response content:');
            
            // 显示所有设置
            foreach ($content as $key => $value) {
                if (is_string($value) || is_numeric($value) || is_null($value)) {
                    $this->line("  {$key}: " . (is_null($value) ? 'null' : $value));
                } elseif (is_array($value)) {
                    if ($key === 'banners') {
                        $this->info("  banners: " . count($value) . " items");
                    } else {
                        $this->info("  {$key}: [array]");
                    }
                }
            }
            
            // 检查必要的字段
            $required_fields = [
                'banner_title',
                'banner_subtitle',
                'banner_button_text',
                'banner_button_link',
                'banner_image_url',
                'offer_title',
                'offer_subtitle',
                'offer_button_text',
                'offer_button_link',
                'offer_image_url',
                'featured_products_title',
                'featured_products_subtitle',
                'featured_products_button_text',
                'featured_products_button_link'
            ];
            
            $missing_fields = [];
            foreach ($required_fields as $field) {
                if (!isset($content[$field])) {
                    $missing_fields[] = $field;
                }
            }
            
            if (empty($missing_fields)) {
                $this->info('Test passed: All required fields are present');
            } else {
                $this->error('Test failed: Missing fields: ' . implode(', ', $missing_fields));
                return 1;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Exception occurred: ' . $e->getMessage());
            Log::error('Exception in TestHomepageSettingsCommand', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
} 