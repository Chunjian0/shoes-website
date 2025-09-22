<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "检查inventory表是否已经移除：\n\n";

// 检查inventory表是否存在
try {
    $exists = DB::select("SHOW TABLES LIKE 'inventory'");
    if (count($exists) > 0) {
        echo "inventory表仍然存在！\n";
    } else {
        echo "inventory表已成功移除！\n";
    }
} catch (\Exception $e) {
    echo "查询表失败：" . $e->getMessage() . "\n";
}

// 在代码中查找对inventory表的引用
echo "\n检查代码中对inventory表的引用：\n";

// 定义需要检查的目录和文件扩展名
$directories = [
    'app',
    'routes',
    'resources/views',
    'database/migrations'
];
$fileExtensions = ['php', 'blade.php'];

// 检查结果
$references = [];

// 遍历目录查找引用
foreach ($directories as $directory) {
    $dir = __DIR__ . '/' . $directory;
    if (!is_dir($dir)) {
        echo "目录不存在：{$directory}\n";
        continue;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if (!in_array(pathinfo($file, PATHINFO_EXTENSION), $fileExtensions)) {
            continue;
        }
        
        $content = file_get_contents($file);
        $paths = [
            'Model引用' => [
                'use App\\Models\\Inventory;',
                'App\\Models\\Inventory',
                'Inventory::',
                'Inventory '
            ],
            '表名引用' => [
                "'inventory'",
                '"inventory"',
                'inventory table'
            ]
        ];
        
        foreach ($paths as $type => $searchTerms) {
            foreach ($searchTerms as $term) {
                if (strpos($content, $term) !== false) {
                    $relativePath = str_replace(__DIR__ . '/', '', $file);
                    $references[$relativePath][] = [
                        'type' => $type,
                        'term' => $term
                    ];
                    break 2; // 找到一个引用后，跳出两层循环
                }
            }
        }
    }
}

// 输出查找结果
if (count($references) > 0) {
    echo "找到以下文件中仍有对inventory表的引用：\n";
    foreach ($references as $file => $terms) {
        echo "- {$file}:\n";
        foreach ($terms as $term) {
            echo "  * {$term['type']}: {$term['term']}\n";
        }
    }
    echo "\n这些文件需要修改，移除对inventory表的引用。\n";
} else {
    echo "没有找到对inventory表的引用，所有相关代码都已移除！\n";
}

echo "\nDone."; 