<?php

// 初始化Laravel应用
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\Services\NotificationSettingService;

// 记录测试开始
echo "Starting email test...\n";
Log::info('Starting email test script');

try {
    // 获取服务
    $notificationSettingService = app(NotificationSettingService::class);
    $notificationService = app(NotificationService::class);
    
    // 检查邮件配置
    echo "Mail configuration:\n";
    echo "MAIL_MAILER: " . config('mail.default') . "\n";
    echo "MAIL_HOST: " . config('mail.mailers.' . config('mail.default') . '.host') . "\n";
    echo "MAIL_PORT: " . config('mail.mailers.' . config('mail.default') . '.port') . "\n";
    echo "MAIL_USERNAME: " . config('mail.mailers.' . config('mail.default') . '.username') . "\n";
    echo "MAIL_ENCRYPTION: " . config('mail.mailers.' . config('mail.default') . '.encryption') . "\n";
    echo "MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
    
    // 检查邮件通知是否启用
    $emailEnabled = $notificationSettingService->isMethodEnabled('email');
    echo "Email notifications enabled: " . ($emailEnabled ? 'Yes' : 'No') . "\n";
    
    // 测试收件人
    $recipient = 'test@example.com';
    $subject = 'Test Email After Fix';
    $content = '<h1>Test Email</h1><p>This is a test email sent after fixing the email sending functionality.</p>';
    
    echo "Sending test email to: $recipient\n";
    
    // 使用修复后的方法发送邮件
    $result = $notificationService->sendCustomEmail(
        $recipient,
        $subject,
        $content,
        [], // 无附件
        false // 记录到通知日志
    );
    
    if ($result) {
        echo "Email sent successfully!\n";
        Log::info('Test email sent successfully', ['recipient' => $recipient]);
    } else {
        echo "Failed to send email.\n";
        Log::error('Failed to send test email', ['recipient' => $recipient]);
    }
    
    // 检查最近的日志
    echo "\nRecent logs:\n";
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logs = file($logPath);
        $recentLogs = array_slice($logs, -20); // 获取最后20行
        foreach ($recentLogs as $log) {
            echo $log;
        }
    } else {
        echo "Log file not found at: $logPath\n";
    }
    
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    Log::error('Exception in test email script', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

echo "Email test completed.\n"; 