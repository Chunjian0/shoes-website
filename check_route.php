<?php

// 引入Laravel应用
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 获取路由列表
$routeCollection = \Illuminate\Support\Facades\Route::getRoutes();

echo "查找通知历史记录路由...\n";
$found = false;

foreach ($routeCollection as $route) {
    $routeName = $route->getName();
    if (strpos($routeName, 'notification-history') !== false) {
        echo "找到路由：" . $routeName . "\n";
        echo "URI: " . $route->uri() . "\n";
        echo "方法: " . implode(', ', $route->methods()) . "\n";
        echo "控制器: " . $route->getActionName() . "\n";
        echo "参数: " . json_encode($route->parameterNames()) . "\n";
        echo "\n";
        $found = true;
    }
}

if (!$found) {
    echo "未找到与 'notification-history' 相关的路由\n";
}

// 检查特定路由
echo "\n尝试生成路由URL...\n";
try {
    $url1 = route('settings.notification-history');
    echo "settings.notification-history URL: " . $url1 . "\n";
} catch (\Exception $e) {
    echo "无法生成 settings.notification-history URL: " . $e->getMessage() . "\n";
}

try {
    $url2 = route('notification-history');
    echo "notification-history URL: " . $url2 . "\n";
} catch (\Exception $e) {
    echo "无法生成 notification-history URL: " . $e->getMessage() . "\n";
}

echo "\n完成路由检查。\n"; 