<?php
// 加载Laravel环境
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 获取users表结构
try {
    $connection = \Illuminate\Support\Facades\DB::connection();
    $table = 'users';
    
    // 获取表的所有列信息
    $columns = $connection->select("SHOW COLUMNS FROM `$table`");
    
    echo "Users表字段信息:\n";
    foreach ($columns as $column) {
        $nullable = $column->Null === 'YES' ? '可为NULL' : '不可为NULL';
        $default = $column->Default ? "默认值: {$column->Default}" : "无默认值";
        echo "{$column->Field} - 类型: {$column->Type} - {$nullable} - {$default}\n";
    }
    
    // 特别检查employee_id字段
    $employeeIdField = array_filter($columns, function($col) {
        return $col->Field === 'employee_id';
    });
    
    if ($employeeIdField) {
        $field = reset($employeeIdField);
        echo "\n特别关注字段employee_id:\n";
        echo "类型: {$field->Type}\n";
        echo "可为空: " . ($field->Null === 'YES' ? '是' : '否') . "\n";
        echo "默认值: " . ($field->Default ? $field->Default : '无默认值') . "\n";
        echo "额外信息: {$field->Extra}\n";
    } else {
        echo "\nemployee_id字段不存在\n";
    }
    
    // 检查表是否有外键约束
    $foreignKeys = $connection->select("
        SELECT
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_SCHEMA = DATABASE() AND
            TABLE_NAME = ? AND
            REFERENCED_TABLE_NAME IS NOT NULL
    ", [$table]);
    
    if ($foreignKeys) {
        echo "\n表外键约束:\n";
        foreach ($foreignKeys as $fk) {
            echo "{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
        }
    } else {
        echo "\n表没有外键约束\n";
    }
    
} catch (\Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} 