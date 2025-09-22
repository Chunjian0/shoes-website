<?php
// 测试脚本：检查购物车参数如何被处理
// 运行后记得删除此文件

// 设置内容类型为JSON
header('Content-Type: application/json');

// 记录所有请求参数
$requestData = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'get' => $_GET,
    'post' => $_POST,
    'json' => null,
    'raw' => file_get_contents('php://input'),
    'headers' => getallheaders()
];

// 尝试解析JSON输入
if (!empty($requestData['raw'])) {
    try {
        $requestData['json'] = json_decode($requestData['raw'], true);
    } catch (Exception $e) {
        $requestData['json_error'] = $e->getMessage();
    }
}

// 写入日志文件
file_put_contents(
    'cart-parameters-log.txt', 
    date('Y-m-d H:i:s') . " - " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n\n", 
    FILE_APPEND
);

// 模拟一个成功的响应，以便前端不报错
echo json_encode([
    'success' => true,
    'message' => '请求参数已记录，请查看日志文件',
    'data' => $requestData
]);

// 示范如何获取这些规格参数
if (isset($requestData['json']) && is_array($requestData['json'])) {
    $specifications = $requestData['json']['specifications'] ?? null;
    
    if ($specifications) {
        $color = $specifications['color'] ?? 'default';
        $size = $specifications['size'] ?? 'default';
        
        file_put_contents(
            'cart-parameters-log.txt',
            "解析到的规格：color = {$color}, size = {$size}\n\n",
            FILE_APPEND
        );
    }
} 