<?php

/**
 * 订单API测试脚本
 * 
 * 这个脚本用于测试订单相关API的功能和响应格式
 * 
 * 运行方式：php test_api_orders.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== 订单API测试 ===\n\n";

// 定义基础URL
$base_url = 'http://localhost/Laravel/optic-system';

// 示例认证令牌 (实际使用时需要替换为有效的令牌)
$token = 'example_token';

// 测试获取订单列表
echo "测试获取订单列表API:\n";
sendRequest($base_url, '/api/orders', 'GET', null, $token);

// 模拟创建订单数据
$order_data = [
    'shipping_address' => [
        'name' => '测试用户',
        'phone' => '1234567890',
        'address_line1' => '测试地址1',
        'address_line2' => '测试地址2',
        'city' => '测试城市',
        'state' => '测试省份',
        'postal_code' => '123456',
        'country' => '中国'
    ],
    'payment_method' => 'credit_card',
    'items' => [
        [
            'product_id' => 1,
            'quantity' => 2,
            'specifications' => [
                'size' => '42',
                'color' => 'black'
            ]
        ],
        [
            'product_id' => 2,
            'quantity' => 1,
            'specifications' => [
                'size' => '39',
                'color' => 'white'
            ]
        ]
    ],
    'notes' => '这是一个测试订单'
];

// 测试创建订单
echo "\n测试创建订单API:\n";
sendRequest($base_url, '/api/orders', 'POST', $order_data, $token);

// 测试获取订单详情
echo "\n测试获取订单详情API:\n";
sendRequest($base_url, '/api/orders/1', 'GET', null, $token);

// 测试取消订单
echo "\n测试取消订单API:\n";
sendRequest($base_url, '/api/orders/1/cancel', 'POST', ['reason' => '测试取消'], $token);

// 测试查询订单状态历史
echo "\n测试查询订单状态历史API:\n";
sendRequest($base_url, '/api/orders/1/history', 'GET', null, $token);

echo "\n=== 测试完成 ===\n";

/**
 * 发送API请求并显示结果
 * 
 * @param string $base_url 基础URL
 * @param string $endpoint API端点
 * @param string $method 请求方法
 * @param array|null $data 请求数据
 * @param string|null $token 认证令牌
 */
function sendRequest($base_url, $endpoint, $method = 'GET', $data = null, $token = null)
{
    echo "  $method $endpoint\n";
    
    if ($data) {
        echo "  请求数据: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    // 初始化请求
    $ch = curl_init($base_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    // 设置请求头
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json'
    ];
    
    // 添加认证令牌
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // 添加请求数据
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    // 执行请求
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        echo "  请求错误: " . curl_error($ch) . "\n";
        curl_close($ch);
        return;
    }
    
    curl_close($ch);
    
    // 检查请求是否成功
    echo "  状态码: $status_code\n";
    
    // 尝试解析响应
    $response_data = json_decode($response, true);
    
    if ($response_data === null) {
        echo "  解析响应失败: " . json_last_error_msg() . "\n";
        echo "  原始响应: " . substr($response, 0, 500) . (strlen($response) > 500 ? '...' : '') . "\n";
        return;
    }
    
    // 输出响应摘要
    echo "  响应键: " . implode(', ', array_keys($response_data)) . "\n";
    
    // 检查是否有状态信息
    if (isset($response_data['status'])) {
        echo "  状态: " . $response_data['status'] . "\n";
    } elseif (isset($response_data['success'])) {
        echo "  成功: " . ($response_data['success'] ? '是' : '否') . "\n";
    }
    
    // 检查是否有消息
    if (isset($response_data['message'])) {
        echo "  消息: " . $response_data['message'] . "\n";
    }
    
    // 检查是否有订单数据
    if (isset($response_data['data']['orders'])) {
        echo "  订单数量: " . count($response_data['data']['orders']) . "\n";
        
        if (count($response_data['data']['orders']) > 0) {
            echo "  订单示例字段: " . implode(', ', array_keys($response_data['data']['orders'][0])) . "\n";
        }
    }
    
    // 检查是否有单个订单数据
    if (isset($response_data['data']['order'])) {
        echo "  订单字段: " . implode(', ', array_keys($response_data['data']['order'])) . "\n";
        
        // 查看是否有订单项
        if (isset($response_data['data']['order']['items']) && is_array($response_data['data']['order']['items'])) {
            echo "  订单项数量: " . count($response_data['data']['order']['items']) . "\n";
        }
    }
} 