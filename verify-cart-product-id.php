<?php

/**
 * 购物车参数验证工具
 * 
 * 这个脚本用于检查购物车添加产品过程中的参数传递问题，
 * 特别关注product_id是否在请求过程中被错误地修改。
 */

echo "===============================================\n";
echo "       购物车参数验证工具（Cart Verifier）       \n";
echo "===============================================\n\n";

// 检查调试日志
$debugLogFile = storage_path('logs/cart-debug.log');
if (!file_exists($debugLogFile)) {
    echo "错误: 找不到购物车调试日志文件 $debugLogFile\n";
    echo "请确保CartController中已经添加日志记录功能\n";
    exit(1);
}

echo "正在分析购物车调试日志...\n\n";

// 读取日志内容
$logContent = file_get_contents($debugLogFile);
$logLines = explode("\n", $logContent);

// 查找最近的购物车添加记录
$requests = [];
$currentRequest = null;
$requestsFound = 0;

foreach ($logLines as $line) {
    // 查找请求开始标记
    if (strpos($line, "CartController.store method called") !== false) {
        if ($currentRequest) {
            $requests[] = $currentRequest;
        }
        $currentRequest = [
            'timestamp' => substr($line, 0, 19),
            'request_data' => null,
            'product_id' => null,
            'final_product_id' => null,
            'creation_data' => null,
            'result' => null
        ];
        $requestsFound++;
    }
    
    // 对于当前请求，提取关键信息
    if ($currentRequest) {
        // 请求数据
        if (strpos($line, "Request data:") !== false) {
            $jsonStart = strpos($line, "{");
            if ($jsonStart !== false) {
                $jsonData = substr($line, $jsonStart);
                $data = json_decode($jsonData, true);
                $currentRequest['request_data'] = $data;
                if (isset($data['product_id'])) {
                    $currentRequest['product_id'] = $data['product_id'];
                }
            }
        }
        
        // 原始product_id
        if (strpos($line, "原始请求中的product_id:") !== false) {
            $parts = explode(":", $line, 2);
            if (count($parts) > 1) {
                $currentRequest['product_id'] = trim($parts[1]);
            }
        }
        
        // 创建购物车项数据
        if (strpos($line, "创建新购物车项") !== false && strpos($line, "product_id") !== false) {
            $parts = explode("product_id:", $line, 2);
            if (count($parts) > 1) {
                $currentRequest['final_product_id'] = trim($parts[1]);
            }
        }
        
        // 创建新CartItem的详细数据
        if (strpos($line, "准备创建CartItem，详细数据:") !== false) {
            $jsonStart = strpos($line, "{");
            if ($jsonStart !== false) {
                $jsonData = substr($line, $jsonStart);
                $currentRequest['creation_data'] = json_decode($jsonData, true);
            }
        }
        
        // New item data
        if (strpos($line, "New item data:") !== false) {
            $jsonStart = strpos($line, "{");
            if ($jsonStart !== false) {
                $jsonData = substr($line, $jsonStart);
                $data = json_decode($jsonData, true);
                if (!$currentRequest['creation_data']) {
                    $currentRequest['creation_data'] = $data;
                }
                if (isset($data['product_id'])) {
                    $currentRequest['final_product_id'] = $data['product_id'];
                }
            }
        }
        
        // 创建结果
        if (strpos($line, "Creation result:") !== false) {
            $currentRequest['result'] = $line;
        }
    }
}

// 添加最后一个请求
if ($currentRequest) {
    $requests[] = $currentRequest;
}

// 显示结果
if ($requestsFound === 0) {
    echo "未找到购物车添加请求记录。请先尝试添加商品到购物车，然后再运行此脚本。\n";
    exit(0);
}

echo "找到 {$requestsFound} 条购物车添加请求记录。\n\n";

// 分析最近的3个请求
$recentRequests = array_slice($requests, -3);
foreach ($recentRequests as $index => $request) {
    echo "请求 #" . ($index + 1) . " (时间: {$request['timestamp']})\n";
    echo "----------------------------------------\n";
    
    if ($request['request_data']) {
        echo "请求数据: " . json_encode($request['request_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    echo "原始 product_id: " . ($request['product_id'] ?? "未知") . "\n";
    echo "最终 product_id: " . ($request['final_product_id'] ?? "未知") . "\n";
    
    if ($request['product_id'] !== $request['final_product_id'] &&
        $request['product_id'] !== null && $request['final_product_id'] !== null) {
        echo "警告: product_id 在处理过程中已更改!\n";
    }
    
    if ($request['creation_data']) {
        echo "购物车项创建数据: " . json_encode($request['creation_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    if ($request['result']) {
        echo "创建结果: " . $request['result'] . "\n";
    }
    
    echo "\n";
}

// 提供修复建议
echo "===============================================\n";
echo "修复建议:\n";
echo "===============================================\n\n";

$hasIdMismatch = false;
foreach ($recentRequests as $request) {
    if ($request['product_id'] !== $request['final_product_id'] &&
        $request['product_id'] !== null && $request['final_product_id'] !== null) {
        $hasIdMismatch = true;
        break;
    }
}

if ($hasIdMismatch) {
    echo "检测到product_id不一致问题。建议修改CartController.php:\n\n";
    
    echo "1. 在store方法中，确保使用强类型检查product_id:\n";
    echo "   ```php\n";
    echo "   // 将请求的product_id转换为整数\n";
    echo "   \$productId = (int)\$request->product_id;\n\n";
    echo "   // 验证product_id是否有效\n";
    echo "   if (\$productId <= 0) {\n";
    echo "       return response()->json([\n";
    echo "           'success' => false,\n";
    echo "           'message' => '无效的产品ID: ' . \$request->product_id\n";
    echo "       ], 422);\n";
    echo "   }\n\n";
    echo "   // 在创建CartItem时直接使用这个变量\n";
    echo "   \$newItemData = [\n";
    echo "       'cart_id' => \$cart->id,\n";
    echo "       'product_id' => \$productId, // 使用验证过的ID\n";
    echo "       'quantity' => \$request->quantity,\n";
    echo "       'specifications' => \$specifications,\n";
    echo "   ];\n";
    echo "   ```\n\n";
    
    echo "2. 检查是否有任何代码可能覆盖product_id:\n";
    echo "   - 搜索所有对\$newItemData['product_id']的赋值\n";
    echo "   - 确保创建CartItem之前没有其他代码修改product_id\n\n";
    
    echo "3. 增加更多调试日志以跟踪product_id的变化:\n";
    echo "   ```php\n";
    echo "   file_put_contents(\$debugLogFile, date('Y-m-d H:i:s') . \" - 验证product_id: {\$productId}\\n\", FILE_APPEND);\n";
    echo "   ```\n";
} else {
    echo "未检测到product_id不一致问题。如果仍然存在问题:\n\n";
    
    echo "1. 检查前端是否正确传递product_id:\n";
    echo "   - 在TemplateDetailPage.tsx的handleAddToCart中确认selectedProductId值\n";
    echo "   - 在cartService.ts中验证product_id没有被覆盖\n\n";
    
    echo "2. 检查API请求过程:\n";
    echo "   - 使用浏览器开发者工具检查实际发送的请求参数\n";
    echo "   - 验证network请求中的product_id是否正确\n\n";
    
    echo "3. 增加前端调试代码:\n";
    echo "   ```js\n";
    echo "   console.log('最终API请求数据:', {\n";
    echo "     product_id: selectedProductId,\n";
    echo "     quantity,\n";
    echo "     template_id: template.id\n";
    echo "   });\n";
    echo "   ```\n";
}

echo "\n祝您调试顺利!\n";

// 帮助函数
function storage_path($path) {
    return __DIR__ . '/storage/logs/' . basename($path);
} 