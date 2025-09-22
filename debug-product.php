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

// 检查产品
try {
    // 获取产品ID=5的详情
    $product = \App\Models\Product::find(5);
    
    echo "<h2>产品ID=5的详情</h2>";
    if ($product) {
        echo "<h3>基本信息:</h3>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $product->id . "</li>";
        echo "<li><strong>名称:</strong> " . $product->name . "</li>";
        echo "<li><strong>SKU:</strong> " . $product->sku . "</li>";
        echo "<li><strong>售价:</strong> " . $product->selling_price . "</li>";
        echo "<li><strong>库存:</strong> " . $product->stock . "</li>";
        echo "</ul>";
        
        echo "<h3>完整产品数据:</h3>";
        echo "<pre>";
        print_r($product->toArray());
        echo "</pre>";
    } else {
        echo "<p>未找到ID=5的产品</p>";
    }
    
    // 检查数据库表结构
    echo "<h2>Products表字段</h2>";
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('products');
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // 检查购物车表
    echo "<h2>CartItem表字段</h2>";
    $cartColumns = \Illuminate\Support\Facades\Schema::getColumnListing('cart_items');
    echo "<pre>";
    print_r($cartColumns);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h3>错误:</h3>";
    echo "<p><strong>消息:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>文件:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<p><strong>跟踪:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 结束响应
$kernel->terminate($request, $response);
?> 