<?php
// 加载Laravel环境
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 获取customers表结构
try {
    // 从数据库中获取customers表的结构
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('customers');
    echo "Customers表所有字段:\n";
    print_r($columns);
    
    // 获取member_level字段的详细信息
    $connection = \Illuminate\Support\Facades\DB::connection();
    $table = 'customers';
    $column = 'member_level';
    
    // 获取数据库类型
    $databaseName = $connection->getDatabaseName();
    $driverName = $connection->getDriverName();
    
    echo "\n数据库名称: $databaseName\n";
    echo "数据库驱动: $driverName\n";
    
    if ($driverName === 'mysql') {
        // MySQL特定查询
        $columnDetails = $connection->select("SHOW COLUMNS FROM `$table` WHERE Field = ?", [$column]);
        
        if (!empty($columnDetails)) {
            echo "\nmember_level字段详情:\n";
            print_r($columnDetails[0]);
        } else {
            echo "\n未找到member_level字段\n";
        }
    }
    
    // 尝试获取已存在的一些member_level值
    $existingValues = \Illuminate\Support\Facades\DB::table('customers')
        ->select('member_level')
        ->distinct()
        ->get()
        ->pluck('member_level')
        ->toArray();
    
    echo "\n当前存在的member_level值:\n";
    print_r($existingValues);
    
} catch (\Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} 