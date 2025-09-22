<?php
// 加载Laravel环境
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

// 设置输出
echo "开始检查和清理孤立的媒体记录...\n";

// 获取所有媒体记录
$mediaRecords = DB::table('media')->get();
echo "找到 " . count($mediaRecords) . " 条媒体记录\n";

$orphanedCount = 0;
$deletedCount = 0;
$errorCount = 0;

// 遍历每条记录并检查文件是否存在
foreach ($mediaRecords as $media) {
    $filePath = $media->path;
    $fullStoragePath = storage_path('app/public/' . $filePath);
    $fullPublicPath = public_path('storage/' . $filePath);
    $fileExists = Storage::disk('public')->exists($filePath) || 
                 file_exists($fullStoragePath) || 
                 file_exists($fullPublicPath);
    
    if (!$fileExists) {
        $orphanedCount++;
        echo "发现孤立记录 [ID: {$media->id}] - 文件不存在: {$filePath}\n";
        
        // 询问是否删除
        echo "是否删除此记录? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) == 'y') {
            try {
                DB::table('media')->where('id', $media->id)->delete();
                echo "✓ 已删除记录 ID: {$media->id}\n";
                $deletedCount++;
            } catch (\Exception $e) {
                echo "✗ 删除失败: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        } else {
            echo "跳过删除\n";
        }
        echo "-----------------------------\n";
    }
}

// 输出统计
echo "\n清理完成！\n";
echo "总计检查: " . count($mediaRecords) . " 条记录\n";
echo "发现孤立: " . $orphanedCount . " 条记录\n";
echo "成功删除: " . $deletedCount . " 条记录\n";
echo "删除失败: " . $errorCount . " 条记录\n";

if ($orphanedCount == 0) {
    echo "✓ 没有发现孤立的媒体记录，数据库状态良好！\n";
} 