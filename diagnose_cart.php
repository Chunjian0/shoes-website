<?php
require_once __DIR__ . '/vendor/autoload.php';

// 启动 Laravel 应用程序
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "====== 购物车诊断脚本 ======\n\n";

// 检查问题的购物车项
$cartItem = CartItem::find(4);
if ($cartItem) {
    echo "找到购物车项 ID: {$cartItem->id}\n";
    echo "购物车 ID: {$cartItem->cart_id}\n";
    echo "商品 ID: {$cartItem->product_id}\n";
    echo "数量: {$cartItem->quantity}\n";
    echo "规格信息: " . json_encode($cartItem->specifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 检查购物车
    $cart = Cart::find($cartItem->cart_id);
    if ($cart) {
        echo "购物车信息:\n";
        echo "ID: {$cart->id}, 名称: {$cart->name}, 类型: {$cart->type}\n";
        echo "会话ID: {$cart->session_id}, 客户ID: " . ($cart->customer_id ?? 'null') . "\n\n";
    } else {
        echo "找不到对应的购物车, ID: {$cartItem->cart_id}\n\n";
    }
    
    // 检查商品
    $product = Product::find($cartItem->product_id);
    if ($product) {
        echo "商品信息:\n";
        echo "ID: {$product->id}, 名称: {$product->name}, SKU: {$product->sku}\n";
        echo "价格: {$product->price}, 销售价: {$product->selling_price}\n";
        
        // 检查该商品是否与模板关联
        $template = ProductTemplate::whereHas('linkedProducts', function($query) use ($product) {
            $query->where('products.id', $product->id);
        })->first();
        
        if ($template) {
            echo "关联模板:\n";
            echo "模板ID: {$template->id}, 名称: {$template->name}\n";
            
            // 查找参数组
            $pivot = DB::table('product_template_product')
                ->where('product_template_id', $template->id)
                ->where('product_id', $product->id)
                ->first();
            
            if ($pivot && $pivot->parameter_group) {
                echo "参数组: {$pivot->parameter_group}\n\n";
            } else {
                echo "没有找到参数组\n\n";
            }
        } else {
            echo "该商品没有关联模板\n\n";
        }
    } else {
        echo "找不到对应的商品, ID: {$cartItem->product_id}\n\n";
    }
} else {
    echo "找不到购物车项 ID: 4\n\n";
}

// 测试添加购物车
echo "===== 测试添加购物车 =====\n\n";

// 找一个带模板的产品
$testProduct = Product::with('template')->first();
if ($testProduct) {
    echo "测试商品 ID: {$testProduct->id}, 名称: {$testProduct->name}\n";
    
    // 查找关联模板
    $testTemplate = ProductTemplate::whereHas('linkedProducts', function($query) use ($testProduct) {
        $query->where('products.id', $testProduct->id);
    })->first();
    
    if ($testTemplate) {
        echo "模板 ID: {$testTemplate->id}, 名称: {$testTemplate->name}\n";
        
        // 获取参数组
        $testPivot = DB::table('product_template_product')
            ->where('product_template_id', $testTemplate->id)
            ->where('product_id', $testProduct->id)
            ->first();
        
        if ($testPivot && $testPivot->parameter_group) {
            echo "参数组: {$testPivot->parameter_group}\n\n";
            
            // 创建模拟添加购物车的数据
            $specifications = [];
            $parameterGroup = $testPivot->parameter_group;
            $parameters = explode(';', $parameterGroup);
            
            foreach ($parameters as $param) {
                $parts = explode('=', $param);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    if (!empty($key) && !empty($value)) {
                        $specifications[$key] = $value;
                    }
                }
            }
            
            // 添加模板ID
            $specifications['template_id'] = $testTemplate->id;
            
            echo "构建的specifications: " . json_encode($specifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
            
            // 检查当前数据库中保存的规格格式
            $cartItems = CartItem::where('specifications', '!=', '[]')->limit(3)->get();
            
            if ($cartItems->isNotEmpty()) {
                echo "已存在的带规格的购物车项示例:\n";
                foreach ($cartItems as $item) {
                    echo "ID: {$item->id}, 规格: " . json_encode($item->specifications, JSON_UNESCAPED_UNICODE) . "\n";
                }
                echo "\n";
            }
        } else {
            echo "没有找到参数组\n\n";
        }
    } else {
        echo "该商品没有关联模板\n\n";
    }
} else {
    echo "找不到合适的测试商品\n\n";
}

// 检查前端传递的参数处理
echo "===== 前端参数处理模拟 =====\n\n";

// 模拟前端发送的数据
$simulatedRequests = [
    // 情况1: 传递参数组字符串
    [
        'product_id' => 1,
        'quantity' => 1,
        'parameter_group' => 'color=red;size =big',
        'template_id' => 1
    ],
    // 情况2: 传递单独的参数
    [
        'product_id' => 1,
        'quantity' => 1,
        'color' => 'red',
        'size' => 'big',
        'template_id' => 1
    ],
    // 情况3: 包含空格的参数
    [
        'product_id' => 1,
        'quantity' => 1,
        'parameter_group' => 'color=red;size = big',
        'template_id' => 1
    ]
];

foreach ($simulatedRequests as $index => $request) {
    echo "模拟请求 " . ($index + 1) . ":\n";
    echo "输入: " . json_encode($request, JSON_UNESCAPED_UNICODE) . "\n";
    
    // 处理规格信息
    $specifications = [];
    
    // 处理parameter_group字符串
    if (isset($request['parameter_group'])) {
        $parameters = explode(';', $request['parameter_group']);
        foreach ($parameters as $param) {
            $parts = explode('=', $param);
            if (count($parts) == 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                if (!empty($key) && !empty($value)) {
                    $specifications[$key] = $value;
                }
            }
        }
    } else {
        // 处理单独的参数
        if (isset($request['color'])) {
            $specifications['color'] = trim($request['color']);
        }
        
        if (isset($request['size'])) {
            $specifications['size'] = trim($request['size']);
        }
    }
    
    // 添加模板ID
    if (isset($request['template_id']) && $request['template_id']) {
        $specifications['template_id'] = $request['template_id'];
    }
    
    echo "处理后的specifications: " . json_encode($specifications, JSON_UNESCAPED_UNICODE) . "\n\n";
}

echo "\n====== 诊断完成 ======\n"; 