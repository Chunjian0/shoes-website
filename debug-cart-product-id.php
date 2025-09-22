<?php

// 加载Laravel应用
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\CartItem;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;

// 清除旧的调试日志
$debugLogFile = storage_path('logs/product-id-debug.log');
file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 开始购物车产品ID调试\n");

// 函数：记录日志
function logDebug($message, $data = null) {
    $logFile = storage_path('logs/product-id-debug.log');
    $logMessage = date('Y-m-d H:i:s') . " - {$message}";
    
    if ($data !== null) {
        $logMessage .= ": " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
    echo $logMessage . "\n";
}

// 检查购物车项的产品ID与选定产品ID的关系
function checkCartProductIds() {
    // 获取所有购物车项
    $cartItems = CartItem::with(['product', 'cart'])->get();
    logDebug("找到 " . $cartItems->count() . " 个购物车项");
    
    foreach ($cartItems as $item) {
        logDebug("购物车项 #{$item->id}", [
            'cart_id' => $item->cart_id,
            'cart_name' => $item->cart ? $item->cart->name : 'Unknown',
            'product_id' => $item->product_id,
            'product_name' => $item->product ? $item->product->name : 'Unknown',
            'product_sku' => $item->product ? $item->product->sku : 'Unknown',
            'quantity' => $item->quantity,
            'specifications' => $item->specifications,
            'parameter_group' => $item->parameter_group
        ]);
        
        // 检查是否有模板ID
        $templateId = $item->specifications['template_id'] ?? null;
        if ($templateId) {
            $template = ProductTemplate::find($templateId);
            if ($template) {
                logDebug("关联模板 #{$template->id}", [
                    'name' => $template->name,
                    'linked_products_count' => $template->linkedProducts()->count()
                ]);
                
                // 获取模板中的所有产品
                $linkedProducts = $template->linkedProducts()->get();
                foreach ($linkedProducts as $index => $linkedProduct) {
                    // 获取product_template_product表中的pivot数据
                    $pivot = DB::table('product_template_product')
                        ->where('product_template_id', $template->id)
                        ->where('product_id', $linkedProduct->id)
                        ->first();
                    
                    logDebug("模板关联产品 #{$index}", [
                        'product_id' => $linkedProduct->id,
                        'name' => $linkedProduct->name,
                        'sku' => $linkedProduct->sku,
                        'parameter_group' => $pivot ? $pivot->parameter_group : null
                    ]);
                    
                    // 检查该产品的参数是否与购物车项规格匹配
                    if ($pivot && $pivot->parameter_group) {
                        $params = [];
                        $paramGroups = explode(';', $pivot->parameter_group);
                        foreach ($paramGroups as $param) {
                            $parts = explode('=', $param);
                            if (count($parts) == 2) {
                                $params[trim($parts[0])] = trim($parts[1]);
                            }
                        }
                        
                        $specsMatch = true;
                        foreach ($params as $key => $value) {
                            // 检查必要的规格是否匹配（如颜色和尺寸）
                            if (in_array($key, ['color', 'size', 'Color', 'Size', '颜色', '尺码']) && 
                                (!isset($item->specifications[$key]) || $item->specifications[$key] != $value)) {
                                $specsMatch = false;
                                break;
                            }
                        }
                        
                        if ($specsMatch) {
                            logDebug("规格匹配! 这个产品可能是用户选择的产品");
                        }
                    }
                }
            } else {
                logDebug("未找到模板 #{$templateId}");
            }
        } else {
            logDebug("该购物车项没有关联模板");
        }
        
        logDebug("----------------------------------------");
    }
}

// 检查前端传递的数据与后端接收的差异
function mockCartAddRequest($productId, $templateId, $color, $size) {
    logDebug("模拟添加购物车请求", [
        'product_id' => $productId,
        'template_id' => $templateId,
        'color' => $color,
        'size' => $size
    ]);
    
    // 检查产品是否存在
    $product = Product::find($productId);
    if (!$product) {
        logDebug("错误: 产品 #{$productId} 不存在");
        return;
    }
    
    logDebug("找到产品", [
        'id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'template_id' => $product->template_id
    ]);
    
    // 检查模板是否存在
    $template = null;
    if ($templateId) {
        $template = ProductTemplate::find($templateId);
        if (!$template) {
            logDebug("错误: 模板 #{$templateId} 不存在");
        } else {
            logDebug("找到模板", [
                'id' => $template->id,
                'name' => $template->name
            ]);
            
            // 检查产品是否属于该模板
            $isInTemplate = $template->linkedProducts()->where('products.id', $productId)->exists();
            logDebug("产品是否属于模板: " . ($isInTemplate ? "是" : "否"));
            
            if (!$isInTemplate) {
                logDebug("警告: 产品不属于指定的模板!");
                
                // 检查该产品实际所属的模板
                if ($product->template_id) {
                    $actualTemplate = ProductTemplate::find($product->template_id);
                    if ($actualTemplate) {
                        logDebug("产品实际属于模板", [
                            'template_id' => $actualTemplate->id,
                            'template_name' => $actualTemplate->name
                        ]);
                    }
                }
            }
        }
    }
    
    // 分析规格数据如何传递到CartController
    $specifications = [];
    if ($color) $specifications['color'] = $color;
    if ($size) $specifications['size'] = $size;
    if ($templateId) $specifications['template_id'] = $templateId;
    
    logDebug("最终规格数据", $specifications);
    
    // 检查该产品在模板中的参数组
    if ($template) {
        $pivot = DB::table('product_template_product')
            ->where('product_template_id', $template->id)
            ->where('product_id', $productId)
            ->first();
        
        if ($pivot && $pivot->parameter_group) {
            logDebug("产品在模板中的参数组", $pivot->parameter_group);
            
            // 解析参数组
            $params = [];
            $paramGroups = explode(';', $pivot->parameter_group);
            foreach ($paramGroups as $param) {
                $parts = explode('=', $param);
                if (count($parts) == 2) {
                    $params[trim($parts[0])] = trim($parts[1]);
                }
            }
            
            logDebug("解析后的参数组", $params);
            
            // 比较用户选择的参数与产品参数组
            $matchResult = [];
            foreach (['color', 'size'] as $key) {
                $userValue = $specifications[$key] ?? null;
                $productValue = null;
                
                // 检查多种可能的键名
                foreach ([strtolower($key), ucfirst($key), $key == 'color' ? '颜色' : '尺码'] as $possibleKey) {
                    if (isset($params[$possibleKey])) {
                        $productValue = $params[$possibleKey];
                        break;
                    }
                }
                
                $matchResult[$key] = [
                    'user_selected' => $userValue,
                    'product_param' => $productValue,
                    'match' => $userValue == $productValue
                ];
            }
            
            logDebug("参数匹配结果", $matchResult);
        } else {
            logDebug("未找到产品在模板中的参数组信息");
        }
    }
}

// 执行调试操作
logDebug("开始调试购物车产品ID问题");

// 检查现有购物车项
checkCartProductIds();

// 模拟添加购物车请求
// 替换以下参数为你的实际情况
// 例如：mockCartAddRequest(产品ID, 模板ID, 颜色, 尺寸);
mockCartAddRequest(5, 1, 'yellow', 'L');

logDebug("调试完成");

echo "调试脚本已运行完成，请检查 storage/logs/product-id-debug.log 文件查看详细结果\n"; 