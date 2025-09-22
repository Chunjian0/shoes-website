<?php
// 设置错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 设置响应头
header('Content-Type: application/json');

// API基础URL
$baseUrl = 'http://localhost:8000/api';

// 要测试的API端点
$endpoints = [
    '/test',
    '/homepage/data',
    '/homepage/settings',
    '/homepage/banners',
    '/homepage/featured-templates',
    '/homepage/new-arrival-templates',
    '/homepage/sale-templates',
    '/product-templates'
];

// 测试结果
$results = [];

// 测试每个端点
foreach ($endpoints as $endpoint) {
    $results[$endpoint] = testEndpoint($baseUrl . $endpoint);
}

// 输出结果
echo json_encode([
    'success' => true,
    'message' => 'API格式检查完成',
    'results' => $results
], JSON_PRETTY_PRINT);

/**
 * 测试API端点
 * @param string $url API URL
 * @return array 测试结果
 */
function testEndpoint($url) {
    try {
        // 初始化curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        
        // 执行请求
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // 处理响应
        if ($error) {
            return [
                'status' => 'error',
                'message' => "CURL错误: $error",
                'http_code' => $httpCode
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'status' => 'error',
                'message' => "HTTP错误码: $httpCode",
                'response' => $response,
                'http_code' => $httpCode
            ];
        }
        
        // 解析JSON
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => 'error',
                'message' => "JSON解析错误: " . json_last_error_msg(),
                'response' => substr($response, 0, 500),
                'http_code' => $httpCode
            ];
        }
        
        // 检查API格式
        $formatValid = isset($data['success']);
        $hasData = isset($data['data']);
        
        return [
            'status' => 'success',
            'format_valid' => $formatValid,
            'has_data' => $hasData,
            'data_structure' => $hasData ? array_keys($data['data']) : [],
            'sample' => json_encode($data, JSON_PRETTY_PRINT),
            'http_code' => $httpCode
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => "异常: " . $e->getMessage()
        ];
    }
} 