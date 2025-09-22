<?php
// 加载 Laravel 环境
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ProductTemplate;

echo "<h1>修复参数链接格式</h1>";

// 获取所有模板
$templates = ProductTemplate::all();

// 修复前的记录
$before = DB::table('product_template_product')->get();
echo "<h2>修复前数据</h2>";
echo "<pre>" . json_encode($before, JSON_PRETTY_PRINT) . "</pre>";

// 遍历所有模板
foreach ($templates as $template) {
    // 获取模板参数
    $parameters = $template->parameters;
    if (!is_array($parameters)) {
        try {
            $parameters = json_decode($parameters, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $parameters = [];
            }
        } catch (\Exception $e) {
            $parameters = [];
        }
    }
    
    // 获取现有链接
    $links = DB::table('product_template_product')
        ->where('product_template_id', $template->id)
        ->get();
    
    // 处理每个链接
    foreach ($links as $link) {
        $paramGroup = $link->parameter_group;
        
        // 检查是否已经是"group=value"格式
        if (strpos($paramGroup, '=') !== false) {
            echo "<p>链接ID {$link->id} 已经是正确格式：{$paramGroup}</p>";
            continue;
        }
        
        // 查找参数组
        $paramValues = [];
        foreach ($parameters as $param) {
            if (isset($param['name']) && $param['name'] === $paramGroup && isset($param['values'])) {
                $paramValues = $param['values'];
                break;
            }
        }
        
        if (empty($paramValues)) {
            echo "<p>警告：链接ID {$link->id} 的参数组 {$paramGroup} 在模板中没有值</p>";
            continue;
        }
        
        // 使用第一个值作为默认值修复
        $firstValue = $paramValues[0] ?? 'default';
        $newParamGroup = $paramGroup . '=' . $firstValue;
        
        // 更新数据库
        $updated = DB::table('product_template_product')
            ->where('id', $link->id)
            ->update(['parameter_group' => $newParamGroup]);
        
        echo "<p>修复链接ID {$link->id}：{$paramGroup} → {$newParamGroup}，结果：" . ($updated ? '成功' : '失败') . "</p>";
    }
}

// 修复后的记录
$after = DB::table('product_template_product')->get();
echo "<h2>修复后数据</h2>";
echo "<pre>" . json_encode($after, JSON_PRETTY_PRINT) . "</pre>";

echo "<p><a href='/link_debug.php'>返回调试页面</a></p>";
?> 
// 加载 Laravel 环境
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ProductTemplate;

echo "<h1>修复参数链接格式</h1>";

// 获取所有模板
$templates = ProductTemplate::all();

// 修复前的记录
$before = DB::table('product_template_product')->get();
echo "<h2>修复前数据</h2>";
echo "<pre>" . json_encode($before, JSON_PRETTY_PRINT) . "</pre>";

// 遍历所有模板
foreach ($templates as $template) {
    // 获取模板参数
    $parameters = $template->parameters;
    if (!is_array($parameters)) {
        try {
            $parameters = json_decode($parameters, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $parameters = [];
            }
        } catch (\Exception $e) {
            $parameters = [];
        }
    }
    
    // 获取现有链接
    $links = DB::table('product_template_product')
        ->where('product_template_id', $template->id)
        ->get();
    
    // 处理每个链接
    foreach ($links as $link) {
        $paramGroup = $link->parameter_group;
        
        // 检查是否已经是"group=value"格式
        if (strpos($paramGroup, '=') !== false) {
            echo "<p>链接ID {$link->id} 已经是正确格式：{$paramGroup}</p>";
            continue;
        }
        
        // 查找参数组
        $paramValues = [];
        foreach ($parameters as $param) {
            if (isset($param['name']) && $param['name'] === $paramGroup && isset($param['values'])) {
                $paramValues = $param['values'];
                break;
            }
        }
        
        if (empty($paramValues)) {
            echo "<p>警告：链接ID {$link->id} 的参数组 {$paramGroup} 在模板中没有值</p>";
            continue;
        }
        
        // 使用第一个值作为默认值修复
        $firstValue = $paramValues[0] ?? 'default';
        $newParamGroup = $paramGroup . '=' . $firstValue;
        
        // 更新数据库
        $updated = DB::table('product_template_product')
            ->where('id', $link->id)
            ->update(['parameter_group' => $newParamGroup]);
        
        echo "<p>修复链接ID {$link->id}：{$paramGroup} → {$newParamGroup}，结果：" . ($updated ? '成功' : '失败') . "</p>";
    }
}

// 修复后的记录
$after = DB::table('product_template_product')->get();
echo "<h2>修复后数据</h2>";
echo "<pre>" . json_encode($after, JSON_PRETTY_PRINT) . "</pre>";

echo "<p><a href='/link_debug.php'>返回调试页面</a></p>";
?> 