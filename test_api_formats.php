<?php

/**
 * API响应格式测试脚本
 * 
 * 这个脚本用于测试API响应格式是否符合前端期望的格式。
 * 它会请求各个API端点，并验证响应格式。
 * 
 * 运行方式：php test_api_formats.php
 */

// 设置头部
header('Content-Type: text/plain');

echo "=== API响应格式测试 ===\n\n";

// 测试端点列表
$endpoints = [
    '/api/products/featured' => '精选产品',
    '/api/products/new-arrivals' => '新品',
    '/api/products/sale' => '促销产品',
    '/api/product-categories' => '产品类别',
    '/api/products/1' => '产品详情',
    '/api/product-categories/1' => '类别详情',
];

$base_url = 'http://localhost:8000'; // 可以根据需要更改

// 执行测试
foreach ($endpoints as $endpoint => $description) {
    echo "测试 {$description} ({$endpoint}):\n";
    
    // 发起API请求
    $ch = curl_init("{$base_url}{$endpoint}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // 检查请求是否成功
    if ($status_code !== 200) {
        echo "  请求失败: HTTP {$status_code}\n";
        continue;
    }
    
    // 解析响应
    $data = json_decode($response, true);
    
    if ($data === null) {
        echo "  解析响应失败: " . json_last_error_msg() . "\n";
        continue;
    }
    
    // 验证格式
    echo "  状态码: {$status_code}\n";
    
    // 检查是否有success键
    if (isset($data['success'])) {
        echo "  成功标志: " . ($data['success'] ? '成功' : '失败') . "\n";
    } else {
        echo "  错误: 缺少'success'键\n";
    }
    
    // 检查是否有产品或分类数据
    if (strpos($endpoint, 'product-categories') !== false) {
        if (isset($data['categories'])) {
            echo "  类别数量: " . count($data['categories']) . "\n";
        } else {
            echo "  错误: 缺少'categories'键\n";
        }
    } else if (strpos($endpoint, 'products') !== false && $endpoint !== '/api/products/1') {
        if (isset($data['products'])) {
            echo "  产品数量: " . count($data['products']) . "\n";
            
            if (count($data['products']) > 0) {
                $first_product = $data['products'][0];
                echo "  产品示例字段: " . implode(', ', array_keys($first_product)) . "\n";
            }
        } else {
            echo "  错误: 缺少'products'键\n";
        }
    }
    
    echo "\n";
}

echo "=== 测试完成 ===\n"; 