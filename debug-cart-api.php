<?php
// 启用所有错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 创建日志文件
$logFile = __DIR__ . '/cart-api-test.log';
file_put_contents($logFile, "===== 测试开始于 " . date('Y-m-d H:i:s') . " =====\n", FILE_APPEND);

// 测试参数
$apiUrl = 'http://localhost:2268/api/cart'; // 使用服务器实际运行的端口
$productId = 5; // 替换为实际存在的产品ID
$quantity = 1;
$size = '40';
$color = 'Black';

// 指定cookie文件
$cookieFile = __DIR__ . '/cookie.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile); // 删除之前的cookie文件
}

// 创建cURL会话
$ch = curl_init();

// 设置cURL选项以保留Cookie
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 禁用SSL验证（仅用于测试）
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);     // 禁用主机验证（仅用于测试）
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest',
]);

// 第1步：访问首页以获取初始化Cookie
file_put_contents($logFile, "步骤1: 获取初始Cookie\n", FILE_APPEND);
curl_setopt($ch, CURLOPT_URL, 'http://localhost:2268');
curl_setopt($ch, CURLOPT_HTTPGET, true);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    file_put_contents($logFile, "cURL错误 (首页): " . curl_error($ch) . "\n", FILE_APPEND);
    echo "cURL错误 (首页): " . curl_error($ch) . "\n";
} else {
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    file_put_contents($logFile, "首页请求状态码: {$statusCode}\n", FILE_APPEND);
    echo "首页请求状态码: {$statusCode}\n";
}

// 第2步：获取CSRF令牌
file_put_contents($logFile, "步骤2: 获取CSRF令牌\n", FILE_APPEND);
$csrfUrl = 'http://localhost:2268/sanctum/csrf-cookie';
curl_setopt($ch, CURLOPT_URL, $csrfUrl);
curl_setopt($ch, CURLOPT_HTTPGET, true);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    file_put_contents($logFile, "cURL错误 (CSRF): " . curl_error($ch) . "\n", FILE_APPEND);
    echo "cURL错误 (CSRF): " . curl_error($ch) . "\n";
} else {
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    file_put_contents($logFile, "CSRF请求状态码: {$statusCode}\n", FILE_APPEND);
    echo "CSRF请求状态码: {$statusCode}\n";
    
    if (file_exists($cookieFile)) {
        $cookieContent = file_get_contents($cookieFile);
        file_put_contents($logFile, "Cookie内容:\n{$cookieContent}\n", FILE_APPEND);
        echo "Cookie文件已创建: {$cookieFile}\n";
    } else {
        file_put_contents($logFile, "Cookie文件未创建\n", FILE_APPEND);
        echo "Cookie文件未创建\n";
    }
}

// 尝试提取XSRF令牌从cookie文件（如果存在）
$xsrf_token = null;
if (file_exists($cookieFile)) {
    $cookieContent = file_get_contents($cookieFile);
    if (preg_match('/XSRF-TOKEN\s+([^\s]+)/', $cookieContent, $matches)) {
        $xsrf_token = $matches[1];
        file_put_contents($logFile, "找到XSRF-TOKEN: {$xsrf_token}\n", FILE_APPEND);
        echo "找到XSRF-TOKEN: {$xsrf_token}\n";
    } else {
        file_put_contents($logFile, "未找到XSRF-TOKEN\n", FILE_APPEND);
        echo "未找到XSRF-TOKEN\n";
    }
}

// 第3步：发送购物车请求
file_put_contents($logFile, "步骤3: 添加商品到购物车\n", FILE_APPEND);
file_put_contents($logFile, "API URL: {$apiUrl}\n", FILE_APPEND);

$data = [
    'product_id' => $productId,
    'quantity' => $quantity,
    'size' => $size,
    'color' => $color
];

file_put_contents($logFile, "请求数据: " . json_encode($data) . "\n", FILE_APPEND);

$headers = [
    'Accept: application/json',
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest',
];

// 如果找到了XSRF令牌，添加到请求头中
if ($xsrf_token) {
    $headers[] = 'X-XSRF-TOKEN: ' . urldecode($xsrf_token);
}

curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$info = curl_getinfo($ch);

if (curl_errno($ch)) {
    file_put_contents($logFile, "cURL错误 (购物车): " . curl_error($ch) . "\n", FILE_APPEND);
    echo "cURL错误 (购物车): " . curl_error($ch) . "\n";
} else {
    file_put_contents($logFile, "状态码: {$info['http_code']}\n", FILE_APPEND);
    file_put_contents($logFile, "响应时间: {$info['total_time']} 秒\n", FILE_APPEND);
    file_put_contents($logFile, "响应头信息:\n" . print_r($info, true) . "\n", FILE_APPEND);
    file_put_contents($logFile, "响应内容: {$response}\n", FILE_APPEND);
    
    echo "状态码: {$info['http_code']}\n";
    echo "响应内容:\n{$response}\n";
}

curl_close($ch);

// 查看Laravel日志文件
$cartDebugLogFile = __DIR__ . '/storage/logs/cart-debug.log';
if (file_exists($cartDebugLogFile)) {
    file_put_contents($logFile, "\n===== Laravel购物车日志内容 =====\n", FILE_APPEND);
    $cartDebugLog = file_get_contents($cartDebugLogFile);
    file_put_contents($logFile, $cartDebugLog, FILE_APPEND);
    echo "\n===== Laravel购物车日志内容 =====\n{$cartDebugLog}\n";
} else {
    file_put_contents($logFile, "\nLaravel购物车日志文件不存在: {$cartDebugLogFile}\n", FILE_APPEND);
    echo "Laravel购物车日志文件不存在: {$cartDebugLogFile}\n";
}

file_put_contents($logFile, "===== 测试结束于 " . date('Y-m-d H:i:s') . " =====\n\n", FILE_APPEND);
echo "测试完成。详细日志请查看: {$logFile}\n"; 