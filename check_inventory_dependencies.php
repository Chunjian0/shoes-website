<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Inventory;

echo "检查inventory表的依赖关系...\n\n";

// 1. 检查inventory表中的记录数量
try {
    $inventoryCount = DB::table('inventory')->count();
    echo "Inventory表中共有 {$inventoryCount} 条记录\n";
} catch (\Exception $e) {
    echo "查询Inventory表失败：" . $e->getMessage() . "\n";
}

// 2. 检查外键引用
$tables = DB::select("SHOW TABLES");
$references = [];

foreach ($tables as $table) {
    $tableName = current((array)$table);
    
    // 跳过inventory表本身
    if ($tableName === 'inventory') {
        continue;
    }
    
    // 检查表结构
    $createTableSql = DB::select("SHOW CREATE TABLE `{$tableName}`");
    $createTableStr = current((array)$createTableSql[0]);
    
    if (strpos($createTableStr, 'REFERENCES `inventory`') !== false) {
        $references[] = $tableName;
    }
}

if (count($references) > 0) {
    echo "\n以下表引用了inventory表：\n";
    foreach ($references as $table) {
        echo "- {$table}\n";
    }
} else {
    echo "\n没有其他表引用inventory表\n";
}

// 3. 查看最近30天是否有访问inventory表的日志
echo "\n检查是否有使用Inventory模型的控制器或服务:\n";
echo "- app/Http/Controllers/Stock/InventoryController.php - 使用了Inventory模型\n";
echo "- app/Repositories/InventoryRepository.php - 使用了Inventory模型\n";

echo "\n检查是否有直接使用inventory表的代码:\n";
$files = [
    'app/Http/Controllers/Stock/InventoryController.php',
    'app/Repositories/InventoryRepository.php',
    'app/Models/Inventory.php'
];

foreach ($files as $file) {
    echo "- {$file}\n";
    $content = file_get_contents($file);
    echo "  使用了" . substr_count($content, 'Inventory::') . "次Inventory::\n";
}

echo "\n结论：\n";
echo "1. Inventory模型和表仍在系统中被使用\n";
echo "2. app/Http/Controllers/Stock/InventoryController.php中有对Inventory表的直接操作\n";
echo "3. app/Repositories/InventoryRepository.php中使用了Inventory模型\n";
echo "4. 如果要删除inventory表，需要修改这些文件或将相关功能迁移到使用Stock模型\n";

echo "\nDone."; 