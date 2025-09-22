<?php
/**
 * 调试脚本：跟踪product_id的流转过程
 * 
 * 该脚本分析购物车请求日志和前端通信日志，验证product_id从前端到后端的一致性
 * 用于排查为什么前端发送product_id为5但数据库记录为1的问题
 */

// 启动Laravel环境
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 引入DB门面
use Illuminate\Support\Facades\DB;

// 定义日志文件
$cartDebugLog = storage_path('logs/cart-debug.log');
$frontendDebugLog = storage_path('logs/frontend-debug.log');

echo "====== 购物车Product ID流转分析 ======\n\n";

// 检查购物车后端日志
if (file_exists($cartDebugLog)) {
    echo "分析后端购物车日志...\n";
    $logContent = file_get_contents($cartDebugLog);
    $requests = [];
    $currentRequest = null;
    
    // 分析日志内容
    $lines = explode("\n", $logContent);
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        
        // 开始新请求
        if (strpos($line, 'CartController.store method called') !== false) {
            if ($currentRequest) {
                $requests[] = $currentRequest;
            }
            $currentRequest = [
                'timestamp' => substr($line, 0, 19),
                'original_product_id' => null,
                'converted_product_id' => null,
                'final_product_id' => null,
                'result' => null
            ];
        }
        
        // 收集请求信息
        if ($currentRequest) {
            // 原始请求数据
            if (strpos($line, 'Request data:') !== false) {
                $jsonStart = strpos($line, '{');
                if ($jsonStart !== false) {
                    $jsonData = substr($line, $jsonStart);
                    $data = json_decode($jsonData, true);
                    if (isset($data['product_id'])) {
                        $currentRequest['original_product_id'] = $data['product_id'];
                        $currentRequest['request_data'] = $data;
                    }
                }
            }
            
            // 转换后的product_id
            if (strpos($line, '转换后的product_id:') !== false) {
                preg_match('/转换后的product_id: (\d+)/', $line, $matches);
                if (isset($matches[1])) {
                    $currentRequest['converted_product_id'] = (int)$matches[1];
                }
            }
            
            // 最终使用的product_id
            if (strpos($line, 'Looking for product with ID:') !== false) {
                preg_match('/Looking for product with ID: (\d+)/', $line, $matches);
                if (isset($matches[1])) {
                    $currentRequest['final_product_id'] = (int)$matches[1];
                }
            }
            
            // 创建购物车项结果
            if (strpos($line, 'Creation result:') !== false) {
                $currentRequest['result'] = $line;
            }
        }
    }
    
    // 添加最后一个请求
    if ($currentRequest) {
        $requests[] = $currentRequest;
    }
    
    // 显示最近的请求
    if (!empty($requests)) {
        $recentRequests = array_slice($requests, -5);
        echo "最近5个购物车请求分析：\n";
        foreach ($recentRequests as $index => $request) {
            echo "\n请求 #" . ($index + 1) . " (" . $request['timestamp'] . "):\n";
            echo "  原始请求product_id: " . var_export($request['original_product_id'], true) . "\n";
            echo "  转换后product_id: " . var_export($request['converted_product_id'], true) . "\n";
            echo "  最终使用product_id: " . var_export($request['final_product_id'], true) . "\n";
            
            // 检测不一致
            if ($request['original_product_id'] != $request['final_product_id']) {
                echo "  [警告] Product ID 不一致！\n";
            }
            
            // 显示请求数据
            if (isset($request['request_data'])) {
                echo "  请求参数: " . json_encode($request['request_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            }
            
            // 显示结果
            if ($request['result']) {
                echo "  结果: " . trim(substr($request['result'], strpos($request['result'], 'Creation result:') + 16)) . "\n";
            }
        }
    } else {
        echo "没有找到购物车请求记录\n";
    }
} else {
    echo "购物车调试日志不存在: {$cartDebugLog}\n";
}

// 检查数据库中的实际记录
echo "\n正在检查数据库中的购物车记录...\n";
try {
    $cartItems = DB::table('cart_items')
        ->select('cart_items.*', 'products.name as product_name', 'products.template_id')
        ->join('products', 'cart_items.product_id', '=', 'products.id')
        ->orderBy('cart_items.created_at', 'desc')
        ->limit(5)
        ->get();
    
    if (count($cartItems) > 0) {
        echo "最近5个购物车项目记录：\n";
        foreach ($cartItems as $index => $item) {
            echo "\n购物车项目 #" . ($index + 1) . ":\n";
            echo "  ID: " . $item->id . "\n";
            echo "  购物车ID: " . $item->cart_id . "\n";
            echo "  产品ID: " . $item->product_id . "\n";
            echo "  产品名称: " . $item->product_name . "\n";
            echo "  模板ID: " . $item->template_id . "\n";
            echo "  数量: " . $item->quantity . "\n";
            echo "  规格: " . $item->specifications . "\n";
            echo "  创建时间: " . $item->created_at . "\n";
        }
    } else {
        echo "数据库中没有购物车记录\n";
    }
} catch (\Exception $e) {
    echo "查询数据库错误: " . $e->getMessage() . "\n";
}

// 提供修复建议
echo "\n====== 修复建议 ======\n";
echo "1. 确保前端发送正确的product_id参数，检查TemplateDetailPage.tsx和cartService.ts\n";
echo "2. 确认后端CartController.php中product_id的类型转换是否正确\n";
echo "3. 检查数据库模型Product和CartItem之间的关系设置\n";
echo "4. 验证请求拦截器中是否有修改请求参数的逻辑\n";
echo "5. 确认前端发送的是selectedProductId而不是template_id\n";

echo "\n====== 完成分析 ======\n"; 