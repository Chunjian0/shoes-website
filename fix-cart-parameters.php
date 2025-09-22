<?php
// 临时文件：修复购物车参数处理问题
// 测试后请删除此文件

// 路由将指向这个文件，然后我们将操作转发到实际的CartController

// 设置响应头为JSON
header('Content-Type: application/json');

// 创建日志文件路径
$logFile = __DIR__ . '/fix-cart-params.log';
$timestamp = date('Y-m-d H:i:s');

// 记录请求开始
file_put_contents($logFile, "\n\n=== $timestamp - 拦截到的请求 ===\n", FILE_APPEND);

// 获取请求正文
$rawInput = file_get_contents('php://input');
file_put_contents($logFile, "原始请求数据: " . $rawInput . "\n", FILE_APPEND);

// 解析请求数据
try {
    $requestData = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
    if ($requestData === null) {
        throw new Exception("JSON解析失败");
    }
} catch (Exception $e) {
    // 解析失败，返回错误
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON request',
        'error' => $e->getMessage()
    ]);
    exit;
}

// 记录解析后的数据
file_put_contents($logFile, "解析后数据: " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

// 修复specifications和直接参数的问题
$specifications = [];

// 确保规格参数正确
if (isset($requestData['specifications']) && is_array($requestData['specifications'])) {
    $specifications = $requestData['specifications'];
}

// 从规格或直接参数中获取颜色和尺寸
$color = null;
$size = null;

// 检查规格中是否有颜色和尺寸
if (isset($specifications['color'])) {
    $color = $specifications['color'];
}
if (isset($specifications['size'])) {
    $size = $specifications['size'];
}

// 检查直接参数中是否有颜色和尺寸
if (isset($requestData['color'])) {
    $color = $requestData['color'];
    // 确保规格中也有这个值
    $specifications['color'] = $color;
}
if (isset($requestData['size'])) {
    $size = $requestData['size'];
    // 确保规格中也有这个值
    $specifications['size'] = $size;
}

// 记录修复后的参数
file_put_contents($logFile, "修复后的颜色: " . ($color ?? '未设置') . "\n", FILE_APPEND);
file_put_contents($logFile, "修复后的尺寸: " . ($size ?? '未设置') . "\n", FILE_APPEND);
file_put_contents($logFile, "修复后的规格: " . json_encode($specifications, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

// 更新请求数据
$requestData['specifications'] = $specifications;
$requestData['color'] = $color;
$requestData['size'] = $size;

// 记录更新后的完整请求数据
file_put_contents($logFile, "更新后的请求数据: " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

// 模拟调用实际的CartController添加购物车功能
// 在实际环境中，这将是对CartController的实际调用
$mockResponse = [
    'success' => true,
    'message' => '参数修复完成，这是一个模拟响应',
    'fixed_parameters' => [
        'color' => $color,
        'size' => $size,
        'specifications' => $specifications,
        'template_id' => $requestData['template_id'] ?? null,
        'product_id' => $requestData['product_id'] ?? null,
        'quantity' => $requestData['quantity'] ?? null
    ]
];

// 记录响应
file_put_contents($logFile, "响应: " . json_encode($mockResponse, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

// 返回响应
echo json_encode($mockResponse); 