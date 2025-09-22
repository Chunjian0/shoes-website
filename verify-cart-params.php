<?php
// 临时文件：验证购物车参数
// 测试后删除此文件

// 记录时间戳
$timestamp = date('Y-m-d H:i:s');

// 设置响应头为JSON
header('Content-Type: application/json');

// 创建日志文件路径
$logFile = __DIR__ . '/verify-cart-params.log';

// 获取请求方法
$method = $_SERVER['REQUEST_METHOD'];

// 记录请求开始
file_put_contents($logFile, "\n\n=== $timestamp - 新请求 ($method) ===\n", FILE_APPEND);

// 获取Content-Type和请求正文
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '未知';
$rawInput = file_get_contents('php://input');

// 记录请求头信息
file_put_contents($logFile, "Content-Type: $contentType\n", FILE_APPEND);
file_put_contents($logFile, "请求大小: " . strlen($rawInput) . " 字节\n", FILE_APPEND);

// 解析请求数据
$requestData = [];
$errorMsg = null;

// 处理不同请求方法
if ($method === 'GET') {
    $requestData = $_GET;
    file_put_contents($logFile, "GET 参数: " . json_encode($_GET, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
} elseif ($method === 'POST') {
    // 处理URL编码的表单数据
    if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
        $requestData = $_POST;
        file_put_contents($logFile, "POST 表单数据: " . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    }
    // 处理JSON数据
    elseif (strpos($contentType, 'application/json') !== false) {
        try {
            $requestData = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
            if ($requestData === null) {
                $errorMsg = "JSON解析失败";
            }
            file_put_contents($logFile, "JSON 数据: " . $rawInput . "\n", FILE_APPEND);
            file_put_contents($logFile, "解析后的JSON: " . json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        } catch (Exception $e) {
            $errorMsg = "JSON解析错误: " . $e->getMessage();
        }
    }
    // 处理multipart表单数据
    elseif (strpos($contentType, 'multipart/form-data') !== false) {
        $requestData = $_POST;
        file_put_contents($logFile, "Multipart 表单数据: " . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        
        if (!empty($_FILES)) {
            file_put_contents($logFile, "上传的文件: " . json_encode($_FILES, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        }
    }
    // 其他内容类型
    else {
        file_put_contents($logFile, "原始请求体: " . $rawInput . "\n", FILE_APPEND);
        $requestData = ['raw' => $rawInput];
    }
}

// 检查规格参数
file_put_contents($logFile, "\n=== 规格参数分析 ===\n", FILE_APPEND);

// 直接参数检查
$hasDirectColor = isset($requestData['color']);
$hasDirectSize = isset($requestData['size']);
$directColor = $hasDirectColor ? $requestData['color'] : '未设置';
$directSize = $hasDirectSize ? $requestData['size'] : '未设置';

file_put_contents($logFile, "直接color参数: " . ($hasDirectColor ? "是 ($directColor)" : "否") . "\n", FILE_APPEND);
file_put_contents($logFile, "直接size参数: " . ($hasDirectSize ? "是 ($directSize)" : "否") . "\n", FILE_APPEND);

// specifications参数检查
$hasSpecs = isset($requestData['specifications']) && is_array($requestData['specifications']);
$specColor = null;
$specSize = null;

if ($hasSpecs) {
    $specs = $requestData['specifications'];
    $specColor = isset($specs['color']) ? $specs['color'] : '未设置';
    $specSize = isset($specs['size']) ? $specs['size'] : '未设置';
    
    file_put_contents($logFile, "specifications中的color: " . (isset($specs['color']) ? "是 ($specColor)" : "否") . "\n", FILE_APPEND);
    file_put_contents($logFile, "specifications中的size: " . (isset($specs['size']) ? "是 ($specSize)" : "否") . "\n", FILE_APPEND);
    file_put_contents($logFile, "完整specifications: " . json_encode($specs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
} else {
    file_put_contents($logFile, "没有找到specifications参数或不是数组\n", FILE_APPEND);
}

// 模板ID检查
$hasTemplateId = isset($requestData['template_id']);
$templateId = $hasTemplateId ? $requestData['template_id'] : '未设置';
file_put_contents($logFile, "template_id参数: " . ($hasTemplateId ? "是 ($templateId)" : "否") . "\n", FILE_APPEND);

// 返回分析结果
$responseData = [
    'success' => true,
    'message' => '参数分析完成',
    'request_method' => $method,
    'content_type' => $contentType,
    'params_analysis' => [
        'direct_params' => [
            'has_color' => $hasDirectColor,
            'color' => $directColor,
            'has_size' => $hasDirectSize,
            'size' => $directSize,
        ],
        'specifications' => [
            'has_specs' => $hasSpecs,
            'color' => $specColor,
            'size' => $specSize,
        ],
        'template' => [
            'has_template_id' => $hasTemplateId,
            'template_id' => $templateId
        ]
    ],
    'full_request' => $requestData
];

if ($errorMsg) {
    $responseData['success'] = false;
    $responseData['error'] = $errorMsg;
}

// 记录响应
file_put_contents($logFile, "\n=== 返回响应 ===\n" . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

// 返回JSON响应
echo json_encode($responseData, JSON_PRETTY_PRINT);
?> 