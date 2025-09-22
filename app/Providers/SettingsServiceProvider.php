<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class SettingsServiceProvider extends ServiceProvider
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
        // 确保在迁移过程中不执行这段代码
        if (Schema::hasTable('settings')) {
            try {
                // 获取系统设置
                $settings = Setting::where('group', 'system')->get();

                // 将设置加载到配置中
                foreach ($settings as $setting) {
                    // 将布尔值字符串转换为实际布尔值
                    if ($setting->value === 'true') {
                        Config::set('settings.' . $setting->key, true);
                    } elseif ($setting->value === 'false') {
                        Config::set('settings.' . $setting->key, false);
                    } else {
                        Config::set('settings.' . $setting->key, $setting->value);
                    }
                }

                // 确保自动创建质量检验设置有默认值
                if (!Config::has('settings.auto_create_inspection')) {
                    Config::set('settings.auto_create_inspection', true);
                }

                // 确保自动审批质量检验设置有默认值
                if (!Config::has('settings.auto_approve_inspection')) {
                    Config::set('settings.auto_approve_inspection', true);
                }

            } catch (\Exception $e) {
                Log::error('加载设置到配置中失败: ' . $e->getMessage());
            }
        }
    }
} 