<?php
// 加载Laravel环境
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// 调试信息
echo "开始修复banner图片路径...\n";

// 获取当前设置
$bannerSettings = DB::table('settings')
    ->where('group', 'homepage')
    ->where('key', 'banner_image')
    ->first();

// 获取media表中的记录
$mediaRecord = DB::table('media')
    ->where('model_type', 'App\\Models\\Setting')
    ->where('model_id', 1)
    ->where('collection_name', 'banner')
    ->orderBy('id', 'desc')
    ->first();

if (!$mediaRecord) {
    echo "未找到相关media记录\n";
    $mediaRecord = DB::table('media')->orderBy('id', 'desc')->first();
    
    if (!$mediaRecord) {
        echo "数据库中没有任何media记录\n";
        exit;
    }
    
    echo "使用最新的media记录（可能不是banner图片）：\n";
}

echo "找到media记录：\n";
echo "- ID: " . $mediaRecord->id . "\n";
echo "- 文件名: " . $mediaRecord->file_name . "\n";
echo "- 路径: " . $mediaRecord->path . "\n";

// 修复路径
$correctPath = 'storage/' . $mediaRecord->path;
echo "正确的路径应该是: " . $correctPath . "\n";

if (!$bannerSettings) {
    echo "未找到banner_image设置，将创建新记录\n";
    
    // 插入新记录
    DB::table('settings')->insert([
        'group' => 'homepage',
        'key' => 'banner_image',
        'value' => $correctPath,
        'label' => 'Homepage Banner Image',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "已创建banner_image设置记录: " . $correctPath . "\n";
} else {
    echo "当前banner_image值: " . $bannerSettings->value . "\n";
    
    // 更新数据库
    DB::table('settings')
        ->where('group', 'homepage')
        ->where('key', 'banner_image')
        ->update(['value' => $correctPath]);
    
    echo "已更新数据库中的banner_image值为: " . $correctPath . "\n";
}

echo "修复完成！请刷新首页管理页面查看效果。\n"; 