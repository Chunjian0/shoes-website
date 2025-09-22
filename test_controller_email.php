<?php

// 初始化Laravel应用
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;

// 记录测试开始
echo "Testing TestController::sendTestEmail...\n";
Log::info('Starting TestController email test');

try {
    // 创建请求对象
    $request = Request::create('/test-email', 'POST', [
        'recipient' => 'test@example.com',
        'subject' => 'Test Email from Controller',
        'content' => '<h1>Test Email</h1><p>This is a test email sent through the TestController.</p>',
        'type' => 'test_mail'
    ]);
    
    // 获取控制器实例
    $controller = app(TestController::class);
    
    echo "Sending test email...\n";
    
    // 调用控制器方法
    $response = $controller->sendTestEmail($request);
    
    // 输出响应
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
    
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
    Log::error('Exception in TestController test', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

echo "\nTest completed.\n"; 