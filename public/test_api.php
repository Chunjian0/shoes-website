<?php
// 设置错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// API基础URL (使用Artisan服务器地址)
$baseUrl = 'http://localhost:8000/api';

// 测试端点
$endpoints = [
    '/test',
    '/product-templates',
    '/homepage/data',
    '/homepage/featured-templates',
    '/homepage/banners',
    '/homepage/settings'
];

// 测试每个端点
foreach ($endpoints as $endpoint) {
    echo "Testing endpoint: " . $baseUrl . $endpoint . PHP_EOL;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP code: " . $httpCode . PHP_EOL;
    
    if ($error) {
        echo "CURL error: " . $error . PHP_EOL;
    }
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "Valid JSON response" . PHP_EOL;
            
            if (isset($data['success']) && isset($data['message'])) {
                echo "Message: " . $data['message'] . PHP_EOL;
            } elseif (isset($data['success']) && isset($data['data'])) {
                echo "API format is correct" . PHP_EOL;
                echo "Response structure: " . implode(', ', array_keys($data['data'])) . PHP_EOL;
            } else {
                echo "API format is not standard" . PHP_EOL;
            }
        } else {
            echo "Invalid JSON response: " . json_last_error_msg() . PHP_EOL;
            echo "Raw response: " . substr($response, 0, 100) . "..." . PHP_EOL;
        }
    } else {
        echo "Error response status: " . $httpCode . PHP_EOL;
        echo substr($response, 0, 150) . "..." . PHP_EOL;
    }
    
    echo "------------------------------------" . PHP_EOL;
}

echo "Testing completed!";