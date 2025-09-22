<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

echo "开始同步产品库存数据...\n";

// 获取所有产品
$products = Product::all();
$count = 0;
$updated = 0;

foreach ($products as $product) {
    $count++;
    
    // 计算该产品在所有仓库的总库存
    $totalStock = Stock::where('product_id', $product->id)->sum('quantity');
    
    // 如果库存不一致，则更新产品的inventory_count
    if ($product->inventory_count != $totalStock) {
        $oldCount = $product->inventory_count;
        
        // 更新产品库存
        DB::table('products')
            ->where('id', $product->id)
            ->update(['inventory_count' => $totalStock]);
            
        echo "已更新产品 [{$product->id}] {$product->name}: {$oldCount} -> {$totalStock}\n";
        $updated++;
    }
}

echo "\n同步完成! 共处理 {$count} 个产品，更新了 {$updated} 个产品的库存数据。\n";
echo "Done."; 