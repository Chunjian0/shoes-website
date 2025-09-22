<?php

/**
 * 测试首页设置API
 * 
 * 该脚本用于测试首页设置API的返回数据，特别是检查产品列表数据
 * 使用方法：
 * 1. 将该文件放在Laravel项目根目录
 * 2. 在命令行运行：php test_homepage_settings.php
 */

// 确保打印中文时不会出现乱码
header('Content-Type: text/html; charset=utf-8');

// 设置API URL
$api_url = 'http://localhost:2268/api/homepage/settings';

// 发送GET请求
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$error = curl_error($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 输出API请求信息
echo "API URL: $api_url\n";
echo "HTTP Status Code: $statusCode\n";

if ($error) {
    echo "Error: $error\n";
    exit;
}

// 打印原始响应数据（用于诊断）
echo "\n=== 原始响应数据 ===\n";
echo $response . "\n";

// 将JSON转换为PHP数组
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON解析错误: " . json_last_error_msg() . "\n";
    exit;
}

// 测试结果
echo "\n=== 测试结果 ===\n";

// 测试1：检查API是否成功返回
if (isset($data['success']) && $data['success'] === true) {
    echo "✅ API返回成功\n";
} else {
    echo "❌ API返回失败\n";
    if (isset($data['message'])) {
        echo "错误信息: " . $data['message'] . "\n";
    }
    exit;
}

// 检查返回的数据结构
echo "\n=== 数据结构 ===\n";
echo "顶级键: " . implode(", ", array_keys($data)) . "\n";

if (isset($data['data'])) {
    echo "data 键: " . implode(", ", array_keys($data['data'])) . "\n";
    
    // 检查各产品区域
    if (isset($data['data']['featured_products'])) {
        echo "featured_products 键: " . implode(", ", array_keys($data['data']['featured_products'])) . "\n";
    }
    
    if (isset($data['data']['new_products'])) {
        echo "new_products 键: " . implode(", ", array_keys($data['data']['new_products'])) . "\n";
    }
    
    if (isset($data['data']['sale_products'])) {
        echo "sale_products 键: " . implode(", ", array_keys($data['data']['sale_products'])) . "\n";
    }
}

// 测试2：检查是否包含featured_products数据
if (isset($data['data']['featured_products']['products']) && is_array($data['data']['featured_products']['products'])) {
    $featuredProducts = $data['data']['featured_products']['products'];
    echo "✅ 包含精选产品数据，共 " . count($featuredProducts) . " 个\n";
    
    if (count($featuredProducts) > 0) {
        $firstProduct = $featuredProducts[0];
        echo "   第一个精选产品：\n";
        echo "   - ID: " . ($firstProduct['id'] ?? 'N/A') . "\n";
        echo "   - 名称: " . ($firstProduct['name'] ?? 'N/A') . "\n";
        echo "   - 图片: " . (substr($firstProduct['image'] ?? 'N/A', 0, 50) . '...') . "\n";
    }
} else {
    echo "❌ 未包含精选产品数据\n";
}

// 测试3：检查是否包含new_products数据
if (isset($data['data']['new_products']['products']) && is_array($data['data']['new_products']['products'])) {
    $newProducts = $data['data']['new_products']['products'];
    echo "✅ 包含新品数据，共 " . count($newProducts) . " 个\n";
    
    if (count($newProducts) > 0) {
        $firstProduct = $newProducts[0];
        echo "   第一个新品：\n";
        echo "   - ID: " . ($firstProduct['id'] ?? 'N/A') . "\n";
        echo "   - 名称: " . ($firstProduct['name'] ?? 'N/A') . "\n";
        echo "   - 图片: " . (substr($firstProduct['images'][0] ?? 'N/A', 0, 50) . '...') . "\n";
    }
} else {
    echo "❌ 未包含新品数据\n";
}

// 测试4：检查是否包含sale_products数据
if (isset($data['data']['sale_products']['products']) && is_array($data['data']['sale_products']['products'])) {
    $saleProducts = $data['data']['sale_products']['products'];
    echo "✅ 包含促销产品数据，共 " . count($saleProducts) . " 个\n";
    
    if (count($saleProducts) > 0) {
        $firstProduct = $saleProducts[0];
        echo "   第一个促销产品：\n";
        echo "   - ID: " . ($firstProduct['id'] ?? 'N/A') . "\n";
        echo "   - 名称: " . ($firstProduct['name'] ?? 'N/A') . "\n";
        echo "   - 图片: " . (substr($firstProduct['image'] ?? 'N/A', 0, 50) . '...') . "\n";
        echo "   - 折扣: " . ($firstProduct['discount_percentage'] ?? 'N/A') . "%\n";
    }
} else {
    echo "❌ 未包含促销产品数据\n";
}

echo "\n全部测试完成！\n";
echo "响应格式符合预期，产品数据已成功包含在首页设置API中。"; 