<?php

// 初始化Laravel应用
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;
use App\Services\NotificationSettingService;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

// 记录测试开始
echo "Checking notification settings...\n";

try {
    // 获取服务
    $notificationSettingService = app(NotificationSettingService::class);
    
    // 检查电子邮件通知是否启用
    $emailEnabled = $notificationSettingService->isMethodEnabled('email');
    echo "Email notifications enabled (from service): " . ($emailEnabled ? 'Yes' : 'No') . "\n";
    
    // 直接从数据库检查设置
    $emailSetting = Setting::where('key', 'email_notifications_enabled')->first();
    echo "Email setting from database: " . ($emailSetting ? $emailSetting->value : 'Not found') . "\n";
    
    // 检查缓存
    $cacheKey = 'notification_settings:email_notifications_enabled';
    $cachedValue = Cache::get($cacheKey);
    echo "Cached value: " . ($cachedValue ? ($cachedValue === true ? 'true' : $cachedValue) : 'Not found in cache') . "\n";
    
    // 清除缓存并重新检查
    echo "\nClearing cache and checking again...\n";
    Cache::forget($cacheKey);
    
    $emailEnabled = $notificationSettingService->isMethodEnabled('email');
    echo "Email notifications enabled (after cache clear): " . ($emailEnabled ? 'Yes' : 'No') . "\n";
    
    // 如果设置不存在或为false，尝试创建/更新它
    if (!$emailSetting || $emailSetting->value !== 'true') {
        echo "\nUpdating email notification setting to 'true'...\n";
        
        $result = $notificationSettingService->updateNotificationMethod('email', true);
        echo "Update result: " . ($result ? 'Success' : 'Failed') . "\n";
        
        // 再次检查
        $emailEnabled = $notificationSettingService->isMethodEnabled('email');
        echo "Email notifications enabled (after update): " . ($emailEnabled ? 'Yes' : 'No') . "\n";
    }
    
    // 列出所有通知设置
    echo "\nAll notification settings:\n";
    $allSettings = Setting::where('group', 'notification')->get();
    foreach ($allSettings as $setting) {
        echo "- {$setting->key}: {$setting->value}\n";
    }
    
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nCheck completed.\n"; 