<?php

/**
 * 购物车API测试脚本
 * 
 * 这个脚本用于测试购物车相关API的功能和响应格式
 * 
 * 运行方式：php test_api_cart.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== 购物车API测试 ===\n\n";

// 定义基础URL
$base_url = 'http://localhost/Laravel/optic-system';

// 示例认证令牌 (实际使用时需要替换为有效的令牌)
$token = 'example_token';

// 测试获取购物车
echo "测试获取购物车API:\n";
sendRequest($base_url, '/api/cart', 'GET', null, $token);

// 测试添加商品到购物车
$add_item_data = [
    'product_id' => 1,
    'quantity' => 2,
    'specifications' => [
        'size' => '42',
        'color' => 'black'
    ]
];

echo "\n测试添加商品到购物车API:\n";
sendRequest($base_url, '/api/cart/items', 'POST', $add_item_data, $token);

// 测试更新购物车商品数量
$update_item_data = [
    'quantity' => 3
];

echo "\n测试更新购物车商品数量API (商品ID为1):\n";
sendRequest($base_url, '/api/cart/items/1', 'PUT', $update_item_data, $token);

// 测试删除购物车商品
echo "\n测试删除购物车商品API (商品ID为1):\n";
sendRequest($base_url, '/api/cart/items/1', 'DELETE', null, $token);

// 测试清空购物车
echo "\n测试清空购物车API:\n";
sendRequest($base_url, '/api/cart/clear', 'POST', null, $token);

// 测试获取购物车商品数量
echo "\n测试获取购物车商品数量API:\n";
sendRequest($base_url, '/api/cart/count', 'GET', null, $token);

echo "\n=== 测试完成 ===\n";

/**
 * 模拟登录并获取令牌
 * 
 * @param string $base_url 基础URL
 * @param string $email 邮箱
 * @param string $password 密码
 * @return string|null 认证令牌
 */
function login($base_url, $email, $password)
{
    echo "尝试登录...\n";
    
    $login_data = [
        'email' => $email,
        'password' => $password
    ];
    
    $ch = curl_init($base_url . '/api/customer/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($login_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($status_code !== 200) {
        echo "  登录失败: HTTP $status_code\n";
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['data']['token'])) {
        echo "  登录失败: 未找到令牌\n";
        return null;
    }
    
    echo "  登录成功，获取到令牌\n";
    return $data['data']['token'];
}

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
    
    // 检查是否有购物车数据
    if (isset($response_data['data']['cart'])) {
        echo "  购物车字段: " . implode(', ', array_keys($response_data['data']['cart'])) . "\n";
        
        // 购物车商品
        if (isset($response_data['data']['cart']['items']) && is_array($response_data['data']['cart']['items'])) {
            echo "  购物车商品数量: " . count($response_data['data']['cart']['items']) . "\n";
            
            if (count($response_data['data']['cart']['items']) > 0) {
                echo "  购物车商品字段: " . implode(', ', array_keys($response_data['data']['cart']['items'][0])) . "\n";
            }
        }
    }
    
    // 检查是否有购物车商品数量
    if (isset($response_data['data']['count'])) {
        echo "  购物车商品数量: " . $response_data['data']['count'] . "\n";
    }
} 