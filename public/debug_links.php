<?php
// 加载 Laravel 环境
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 获取当前链接状态
$links = \Illuminate\Support\Facades\DB::table('product_template_product')->get();

// 获取模板和产品信息
$templates = \App\Models\ProductTemplate::all()->keyBy('id');
$products = \App\Models\Product::all()->keyBy('id');

echo '<h1>Template-Product Links Debug</h1>';

// 显示所有链接
echo '<h2>All Links in Database</h2>';
echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
echo '<tr><th>ID</th><th>Template ID</th><th>Template Name</th><th>Product ID</th><th>Product Name</th><th>Parameter Group</th></tr>';

foreach ($links as $link) {
    $templateName = $templates[$link->product_template_id]->name ?? 'Unknown Template';
    $productName = $products[$link->product_id]->name ?? 'Unknown Product';
    
    echo '<tr>';
    echo '<td>' . $link->id . '</td>';
    echo '<td>' . $link->product_template_id . '</td>';
    echo '<td>' . htmlspecialchars($templateName) . '</td>';
    echo '<td>' . $link->product_id . '</td>';
    echo '<td>' . htmlspecialchars($productName) . '</td>';
    echo '<td><code style="background-color: #f0f8ff; padding: 2px 4px; border-radius: 3px;">' . htmlspecialchars($link->parameter_group) . '</code></td>';
    echo '</tr>';
}

echo '</table>';

// 根据URL参数获取特定模板的链接
$templateId = $_GET['template_id'] ?? null;
if ($templateId) {
    $template = $templates[$templateId] ?? null;
    
    if ($template) {
        echo '<h2>Links for Template: ' . htmlspecialchars($template->name) . ' (ID: ' . $templateId . ')</h2>';
        
        // 获取模板的参数定义
        $parameters = json_decode($template->parameters, true);
        if (!is_array($parameters)) {
            $parameters = [];
        }
        
        echo '<h3>Template Parameters</h3>';
        echo '<pre style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;">';
        echo json_encode($parameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo '</pre>';
        
        // 显示每个参数组的链接状态
        echo '<h3>Parameter Links Status</h3>';
        
        foreach ($parameters as $param) {
            if (!isset($param['name']) || !isset($param['values']) || !is_array($param['values'])) {
                continue;
            }
            
            $paramName = $param['name'];
            $paramValues = $param['values'];
            
            echo '<div style="margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">';
            echo '<h4>Parameter Group: ' . htmlspecialchars($paramName) . '</h4>';
            echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
            echo '<tr><th>Parameter Value</th><th>Linked Product</th><th>Full Identifier</th><th>Status</th></tr>';
            
            foreach ($paramValues as $value) {
                $paramIdentifier = $paramName . '=' . $value;
                
                // 寻找匹配的链接
                $matchingLink = null;
                foreach ($links as $link) {
                    if ($link->product_template_id == $templateId && $link->parameter_group == $paramIdentifier) {
                        $matchingLink = $link;
                        break;
                    }
                }
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars($value) . '</td>';
                
                if ($matchingLink) {
                    $linkedProductName = $products[$matchingLink->product_id]->name ?? 'Unknown Product';
                    echo '<td>' . htmlspecialchars($linkedProductName) . ' (ID: ' . $matchingLink->product_id . ')</td>';
                    echo '<td><code style="background-color: #f0f8ff; padding: 2px 4px; border-radius: 3px;">' . htmlspecialchars($matchingLink->parameter_group) . '</code></td>';
                    echo '<td style="background-color: #d4edda; color: #155724;">Linked</td>';
                } else {
                    echo '<td>-</td>';
                    echo '<td><code style="background-color: #f0f8ff; padding: 2px 4px; border-radius: 3px;">' . htmlspecialchars($paramIdentifier) . '</code></td>';
                    echo '<td style="background-color: #f8d7da; color: #721c24;">Not Linked</td>';
                }
                
                echo '</tr>';
            }
            
            echo '</table>';
            echo '</div>';
        }
    } else {
        echo '<div style="color: red;">Template with ID ' . $templateId . ' not found</div>';
    }
}

// 添加一个模板选择表单
echo '<h2>Select Template</h2>';
echo '<form action="" method="get">';
echo '<select name="template_id">';
foreach ($templates as $template) {
    $selected = $templateId == $template->id ? 'selected' : '';
    echo '<option value="' . $template->id . '" ' . $selected . '>' . htmlspecialchars($template->name) . ' (ID: ' . $template->id . ')</option>';
}
echo '</select>';
echo ' <button type="submit" style="background-color: #0d6efd; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">View Template Links</button>';
echo '</form>';
?> 
// 加载 Laravel 环境
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 获取当前链接状态
$links = \Illuminate\Support\Facades\DB::table('product_template_product')->get();

// 获取模板和产品信息
$templates = \App\Models\ProductTemplate::all()->keyBy('id');
$products = \App\Models\Product::all()->keyBy('id');

echo '<h1>Template-Product Links Debug</h1>';

// 显示所有链接
echo '<h2>All Links in Database</h2>';
echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
echo '<tr><th>ID</th><th>Template ID</th><th>Template Name</th><th>Product ID</th><th>Product Name</th><th>Parameter Group</th></tr>';

foreach ($links as $link) {
    $templateName = $templates[$link->product_template_id]->name ?? 'Unknown Template';
    $productName = $products[$link->product_id]->name ?? 'Unknown Product';
    
    echo '<tr>';
    echo '<td>' . $link->id . '</td>';
    echo '<td>' . $link->product_template_id . '</td>';
    echo '<td>' . htmlspecialchars($templateName) . '</td>';
    echo '<td>' . $link->product_id . '</td>';
    echo '<td>' . htmlspecialchars($productName) . '</td>';
    echo '<td><code style="background-color: #f0f8ff; padding: 2px 4px; border-radius: 3px;">' . htmlspecialchars($link->parameter_group) . '</code></td>';
    echo '</tr>';
}

echo '</table>';

// 根据URL参数获取特定模板的链接
$templateId = $_GET['template_id'] ?? null;
if ($templateId) {
    $template = $templates[$templateId] ?? null;
    
    if ($template) {
        echo '<h2>Links for Template: ' . htmlspecialchars($template->name) . ' (ID: ' . $templateId . ')</h2>';
        
        // 获取模板的参数定义
        $parameters = json_decode($template->parameters, true);
        if (!is_array($parameters)) {
            $parameters = [];
        }
        
        echo '<h3>Template Parameters</h3>';
        echo '<pre style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;">';
        echo json_encode($parameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo '</pre>';
        
        // 显示每个参数组的链接状态
        echo '<h3>Parameter Links Status</h3>';
        
        foreach ($parameters as $param) {
            if (!isset($param['name']) || !isset($param['values']) || !is_array($param['values'])) {
                continue;
            }
            
            $paramName = $param['name'];
            $paramValues = $param['values'];
            
            echo '<div style="margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">';
            echo '<h4>Parameter Group: ' . htmlspecialchars($paramName) . '</h4>';
            echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
            echo '<tr><th>Parameter Value</th><th>Linked Product</th><th>Full Identifier</th><th>Status</th></tr>';
            
            foreach ($paramValues as $value) {
                $paramIdentifier = $paramName . '=' . $value;
                
                // 寻找匹配的链接
                $matchingLink = null;
                foreach ($links as $link) {
                    if ($link->product_template_id == $templateId && $link->parameter_group == $paramIdentifier) {
                        $matchingLink = $link;
                        break;
                    }
                }
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars($value) . '</td>';
                
                if ($matchingLink) {
                    $linkedProductName = $products[$matchingLink->product_id]->name ?? 'Unknown Product';
                    echo '<td>' . htmlspecialchars($linkedProductName) . ' (ID: ' . $matchingLink->product_id . ')</td>';
                    echo '<td><code style="background-color: #f0f8ff; padding: 2px 4px; border-radius: 3px;">' . htmlspecialchars($matchingLink->parameter_group) . '</code></td>';
                    echo '<td style="background-color: #d4edda; color: #155724;">Linked</td>';
                } else {
                    echo '<td>-</td>';
                    echo '<td><code style="background-color: #f0f8ff; padding: 2px 4px; border-radius: 3px;">' . htmlspecialchars($paramIdentifier) . '</code></td>';
                    echo '<td style="background-color: #f8d7da; color: #721c24;">Not Linked</td>';
                }
                
                echo '</tr>';
            }
            
            echo '</table>';
            echo '</div>';
        }
    } else {
        echo '<div style="color: red;">Template with ID ' . $templateId . ' not found</div>';
    }
}

// 添加一个模板选择表单
echo '<h2>Select Template</h2>';
echo '<form action="" method="get">';
echo '<select name="template_id">';
foreach ($templates as $template) {
    $selected = $templateId == $template->id ? 'selected' : '';
    echo '<option value="' . $template->id . '" ' . $selected . '>' . htmlspecialchars($template->name) . ' (ID: ' . $template->id . ')</option>';
}
echo '</select>';
echo ' <button type="submit" style="background-color: #0d6efd; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">View Template Links</button>';
echo '</form>';
?> 