<?php
// 设置错误显示
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 加载Laravel环境
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// 模拟购物车添加请求
try {
    // 获取请求数据
    $requestData = [
        'product_id' => 5,
        'quantity' => 1,
        'size' => '40'
    ];
    
    // 输出请求数据
    echo "<h2>测试添加到购物车</h2>";
    echo "<h3>请求数据:</h3>";
    echo "<pre>";
    print_r($requestData);
    echo "</pre>";
    
    // 获取CartController
    $controller = new App\Http\Controllers\Api\CartController();
    
    // 创建测试请求
    $request = new Illuminate\Http\Request();
    $request->replace($requestData);
    
    // 调用store方法
    echo "<h3>响应:</h3>";
    $response = $controller->store($request);
    
    // 输出响应
    echo "<pre>";
    print_r($response->getData());
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h3>错误:</h3>";
    echo "<p><strong>消息:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>文件:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<p><strong>跟踪:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 结束响应
$kernel->terminate($request, $response);
?> 