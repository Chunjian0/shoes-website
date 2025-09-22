<?php
require 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 检查homepage_section_category表的列结构
$columns = DB::select('SHOW COLUMNS FROM homepage_section_category');
echo "homepage_section_category表的列结构：\n";
foreach ($columns as $column) {
    echo "{$column->Field} ({$column->Type})\n";
}

// 检查homepage_section表的结构
$columns = DB::select('SHOW COLUMNS FROM homepage_sections');
echo "\nhomepage_sections表的列结构：\n";
foreach ($columns as $column) {
    echo "{$column->Field} ({$column->Type})\n";
}

// 检查products表stock字段
$columns = DB::select('SHOW COLUMNS FROM products');
echo "\nproducts表的列结构：\n";
foreach ($columns as $column) {
    echo "{$column->Field} ({$column->Type})\n";
}

echo "\n完成表结构检查\n";
