<?php
// 设置错误显示
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 加载Laravel环境
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// 输出详细的异常信息
function printExceptionDetails(\Throwable $e, $level = 0) {
    echo str_repeat('  ', $level) . "<strong>错误类型:</strong> " . get_class($e) . "<br>";
    echo str_repeat('  ', $level) . "<strong>错误消息:</strong> " . $e->getMessage() . "<br>";
    echo str_repeat('  ', $level) . "<strong>文件:</strong> " . $e->getFile() . ":" . $e->getLine() . "<br>";
    
    if ($level === 0) {
        echo str_repeat('  ', $level) . "<strong>堆栈跟踪:</strong><br>";
        echo "<pre>";
        echo $e->getTraceAsString();
        echo "</pre>";
    }
    
    if ($e->getPrevious()) {
        echo str_repeat('  ', $level) . "<strong>前一个异常:</strong><br>";
        printExceptionDetails($e->getPrevious(), $level + 1);
    }
}

// 模拟购物车添加请求
try {
    echo "<h1>购物车API调试</h1>";
    
    // 获取请求数据
    $requestData = [
        'product_id' => 5,
        'quantity' => 1,
        'size' => '40'
    ];
    
    // 输出请求数据
    echo "<h2>请求数据:</h2>";
    echo "<pre>";
    print_r($requestData);
    echo "</pre>";
    
    // 获取Product模型
    echo "<h2>产品信息:</h2>";
    $product = \App\Models\Product::find($requestData['product_id']);
    if ($product) {
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $product->id . "</li>";
        echo "<li><strong>名称:</strong> " . $product->name . "</li>";
        echo "<li><strong>SKU:</strong> " . $product->sku . "</li>";
        echo "<li><strong>售价:</strong> " . $product->selling_price . "</li>";
        echo "<li><strong>库存:</strong> " . $product->stock . "</li>";
        echo "</ul>";
    } else {
        echo "<p>未找到ID=" . $requestData['product_id'] . "的产品</p>";
    }
    
    // 调试购物车会话
    echo "<h2>会话信息:</h2>";
    $sessionId = session()->getId();
    echo "<p><strong>会话ID:</strong> " . $sessionId . "</p>";
    
    // 创建测试请求
    $request = new Illuminate\Http\Request();
    $request->replace($requestData);
    
    // 逐步调试购物车添加流程
    echo "<h2>购物车调试:</h2>";
    
    // 1. 获取或创建购物车
    try {
        echo "<h3>1. 获取或创建购物车:</h3>";
        $cart = \App\Models\Cart::firstOrCreate(
            ['session_id' => $sessionId, 'customer_id' => null],
            ['session_id' => $sessionId]
        );
        echo "<p>购物车ID: " . $cart->id . "</p>";
    } catch (\Exception $e) {
        echo "<p>创建购物车失败:</p>";
        printExceptionDetails($e);
    }
    
    // 2. 检查或创建购物车项
    try {
        echo "<h3>2. 检查购物车项:</h3>";
        $specifications = [
            'size' => $requestData['size'],
            'color' => $requestData['color'] ?? null,
        ];
        
        $cartItem = \App\Models\CartItem::where('cart_id', $cart->id)
            ->where('product_id', $requestData['product_id'])
            ->where('specifications', json_encode($specifications))
            ->first();
            
        if ($cartItem) {
            echo "<p>购物车中已存在该产品，数量: " . $cartItem->quantity . "</p>";
        } else {
            echo "<p>购物车中不存在该产品，将创建新项</p>";
        }
    } catch (\Exception $e) {
        echo "<p>检查购物车项失败:</p>";
        printExceptionDetails($e);
    }
    
    // 3. 创建或更新购物车项
    try {
        echo "<h3>3. 创建或更新购物车项:</h3>";
        
        if ($cartItem) {
            // 更新数量
            $cartItem->quantity += $requestData['quantity'];
            $result = $cartItem->save();
            echo "<p>更新购物车项结果: " . ($result ? "成功" : "失败") . "</p>";
        } else {
            // 创建新项
            $cartItem = new \App\Models\CartItem([
                'cart_id' => $cart->id,
                'product_id' => $requestData['product_id'],
                'quantity' => $requestData['quantity'],
                'price' => $product->selling_price,
                'specifications' => $specifications,
            ]);
            $result = $cartItem->save();
            echo "<p>创建购物车项结果: " . ($result ? "成功" : "失败") . "</p>";
        }
    } catch (\Exception $e) {
        echo "<p>创建或更新购物车项失败:</p>";
        printExceptionDetails($e);
    }
    
    // 4. 获取完整的购物车数据
    try {
        echo "<h3>4. 最终购物车数据:</h3>";
        $cart->refresh();
        $cartItems = $cart->items()->with('product')->get();
        
        echo "<p>购物车项数量: " . count($cartItems) . "</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>产品</th><th>数量</th><th>价格</th><th>规格</th></tr>";
        
        foreach ($cartItems as $item) {
            echo "<tr>";
            echo "<td>" . $item->id . "</td>";
            echo "<td>" . ($item->product ? $item->product->name : '未知产品') . "</td>";
            echo "<td>" . $item->quantity . "</td>";
            echo "<td>" . $item->price . "</td>";
            echo "<td><pre>" . json_encode($item->specifications, JSON_PRETTY_PRINT) . "</pre></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } catch (\Exception $e) {
        echo "<p>获取购物车数据失败:</p>";
        printExceptionDetails($e);
    }
    
    // 5. 调用实际的CartController
    try {
        echo "<h3>5. 调用CartController.store方法:</h3>";
        
        // 获取CartController并调用store方法
        $controller = new \App\Http\Controllers\Api\CartController();
        $controllerResponse = $controller->store($request);
        
        echo "<h4>控制器响应:</h4>";
        echo "<pre>";
        if (method_exists($controllerResponse, 'getData')) {
            print_r($controllerResponse->getData());
        } else {
            print_r($controllerResponse);
        }
        echo "</pre>";
    } catch (\Exception $e) {
        echo "<p>调用控制器方法失败:</p>";
        printExceptionDetails($e);
    }
    
} catch (\Throwable $e) {
    echo "<h2>错误发生:</h2>";
    printExceptionDetails($e);
}

// 结束响应
$kernel->terminate($request, $response);
?> 