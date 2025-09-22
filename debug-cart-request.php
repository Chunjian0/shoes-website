<?php
// 设置错误显示
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 保存错误到本地文件
$errorLogFile = __DIR__ . '/cart-error.log';
file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - Cart Debug Script Started\n", FILE_APPEND);

try {
    // 模拟直接访问API路由，不使用代理
    $url = 'http://localhost:2268/api/cart'; // 直接访问Laravel端口
    $data = [
        'product_id' => 5,
        'quantity' => 1,
        'size' => '40'
    ];
    
    // 获取本地会话ID
    session_start();
    $sessionId = session_id();
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - Session ID: $sessionId\n", FILE_APPEND);
    
    // 初始化curl会话
    $ch = curl_init($url);
    
    // 设置curl选项，匹配前端的请求配置
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Debug-Info: Direct-PHP-Test'
    ]);
    
    // 开启Cookie支持，等同于withCredentials: true
    curl_setopt($ch, CURLOPT_COOKIEFILE, '');  // 使用内存中的cookie
    curl_setopt($ch, CURLOPT_COOKIEJAR, '');   // 保存响应cookie到内存
    
    // 添加XSRF防护（如果需要）
    $xsrfToken = isset($_COOKIE['XSRF-TOKEN']) ? $_COOKIE['XSRF-TOKEN'] : null;
    if ($xsrfToken) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'X-XSRF-TOKEN: ' . $xsrfToken
        ], [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Debug-Info: Direct-PHP-Test'
        ]));
    }
    
    // 记录请求
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - Sending request to $url with data: " . json_encode($data) . "\n", FILE_APPEND);
    if ($xsrfToken) {
        file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - Using XSRF Token: $xsrfToken\n", FILE_APPEND);
    }
    
    // 执行请求
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    
    // 记录响应
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - Response status: {$info['http_code']}\n", FILE_APPEND);
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - Response body: $response\n", FILE_APPEND);
    
    if ($error) {
        file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - cURL error: $error\n", FILE_APPEND);
    }
    
    // 关闭curl会话
    curl_close($ch);
    
    // 输出结果
    echo "<h1>购物车API测试</h1>";
    echo "<h2>请求详情:</h2>";
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<h2>会话信息:</h2>";
    echo "<p>Session ID: $sessionId</p>";
    echo "<p>XSRF Token: " . ($xsrfToken ?: 'Not available') . "</p>";
    
    echo "<h2>响应状态码:</h2>";
    echo "<p>{$info['http_code']}</p>";
    
    echo "<h2>响应内容:</h2>";
    echo "<pre>" . ($response ? json_encode(json_decode($response), JSON_PRETTY_PRINT) : 'No response') . "</pre>";
    
    if ($error) {
        echo "<h2>cURL错误:</h2>";
        echo "<p>$error</p>";
    }
    
} catch (\Throwable $e) {
    // 记录异常
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    // 输出异常
    echo "<h1>异常发生</h1>";
    echo "<p><strong>消息:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>文件:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<p><strong>堆栈跟踪:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 