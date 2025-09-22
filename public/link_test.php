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

// 处理表单提交
$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'link' && isset($_POST['template_id'], $_POST['product_id'], $_POST['parameter_group'], $_POST['parameter_value'])) {
        try {
            $templateId = $_POST['template_id'];
            $productId = $_POST['product_id'];
            $paramGroup = $_POST['parameter_group'];
            $paramValue = $_POST['parameter_value'];
            
            // 创建完整标识符
            $paramIdentifier = $paramGroup . '=' . $paramValue;
            
            // 检查是否已经有链接
            $existingLink = \Illuminate\Support\Facades\DB::table('product_template_product')
                ->where('product_template_id', $templateId)
                ->where('parameter_group', $paramIdentifier)
                ->first();
                
            if ($existingLink) {
                // 先删除已有链接
                \Illuminate\Support\Facades\DB::table('product_template_product')
                    ->where('product_template_id', $templateId)
                    ->where('parameter_group', $paramIdentifier)
                    ->delete();
            }
            
            // 创建新链接
            \Illuminate\Support\Facades\DB::table('product_template_product')->insert([
                'product_template_id' => $templateId,
                'product_id' => $productId,
                'parameter_group' => $paramIdentifier,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $message = "Product successfully linked to parameter: $paramIdentifier";
            $messageType = "success";
            
            // 刷新链接数据
            $links = \Illuminate\Support\Facades\DB::table('product_template_product')->get();
            
        } catch (\Exception $e) {
            $message = "Error linking product: " . $e->getMessage();
            $messageType = "error";
        }
    } elseif ($_POST['action'] === 'unlink' && isset($_POST['link_id'])) {
        try {
            $linkId = $_POST['link_id'];
            
            // 删除链接
            \Illuminate\Support\Facades\DB::table('product_template_product')
                ->where('id', $linkId)
                ->delete();
                
            $message = "Link successfully removed";
            $messageType = "success";
            
            // 刷新链接数据
            $links = \Illuminate\Support\Facades\DB::table('product_template_product')->get();
            
        } catch (\Exception $e) {
            $message = "Error removing link: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// 获取选择的模板
$selectedTemplateId = $_GET['template_id'] ?? null;
$selectedTemplate = $selectedTemplateId ? $templates[$selectedTemplateId] ?? null : null;

// 页面头部
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product-Parameter Link Tester</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-5">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Product-Parameter Link Tester</h1>';

// 显示消息
if (!empty($message)) {
    $bgColor = $messageType === "success" ? "bg-green-100 border-green-500 text-green-700" : "bg-red-100 border-red-500 text-red-700";
    echo '<div class="' . $bgColor . ' border-l-4 p-4 mb-5" role="alert">
        <p>' . htmlspecialchars($message) . '</p>
    </div>';
}

// 模板选择表单
echo '<div class="bg-white shadow-md rounded p-6 mb-6">
    <h2 class="text-xl font-semibold mb-4">Select Template</h2>
    <form action="" method="get">
        <div class="flex items-center space-x-3">
            <select name="template_id" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Select a Template --</option>';

foreach ($templates as $template) {
    $selected = $selectedTemplateId == $template->id ? 'selected' : '';
    echo '<option value="' . $template->id . '" ' . $selected . '>' . htmlspecialchars($template->name) . ' (ID: ' . $template->id . ')</option>';
}

echo '</select>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">View Template</button>
        </div>
    </form>
</div>';

// 显示当前模板和参数
if ($selectedTemplate) {
    // 解析模板参数
    $parameters = json_decode($selectedTemplate->parameters, true);
    if (!is_array($parameters)) {
        $parameters = [];
    }
    
    echo '<div class="bg-white shadow-md rounded p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Template: ' . htmlspecialchars($selectedTemplate->name) . '</h2>
        
        <div class="mb-4">
            <h3 class="text-lg font-medium mb-2">Parameters:</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    
    foreach ($parameters as $param) {
        if (!isset($param['name']) || !isset($param['values']) || !is_array($param['values'])) {
            continue;
        }
        
        $paramName = $param['name'];
        $paramValues = $param['values'];
        
        echo '<div class="border rounded p-4">
            <h4 class="font-medium mb-2">' . htmlspecialchars($paramName) . '</h4>
            <div class="flex flex-wrap gap-2">';
        
        foreach ($paramValues as $value) {
            $paramIdentifier = $paramName . '=' . $value;
            
            // 查找链接状态
            $linkedProduct = null;
            foreach ($links as $link) {
                if ($link->product_template_id == $selectedTemplate->id && $link->parameter_group == $paramIdentifier) {
                    $linkedProduct = $products[$link->product_id] ?? null;
                    break;
                }
            }
            
            if ($linkedProduct) {
                echo '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full flex items-center">
                    ' . htmlspecialchars($value) . '
                    <span class="inline-block w-1 h-1 bg-green-600 rounded-full mx-1"></span>
                    ' . htmlspecialchars($linkedProduct->name) . '
                </span>';
            } else {
                echo '<span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                    ' . htmlspecialchars($value) . '
                </span>';
            }
        }
        
        echo '</div>
        </div>';
    }
    
    echo '</div>
        </div>
        
        <h3 class="text-lg font-medium mb-2">Create New Link:</h3>
        <form action="" method="post" class="mb-4">
            <input type="hidden" name="action" value="link">
            <input type="hidden" name="template_id" value="' . $selectedTemplate->id . '">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parameter Group:</label>
                    <select name="parameter_group" class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">-- Select Parameter Group --</option>';
    
    foreach ($parameters as $param) {
        if (isset($param['name'])) {
            echo '<option value="' . htmlspecialchars($param['name']) . '">' . htmlspecialchars($param['name']) . '</option>';
        }
    }
    
    echo '</select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parameter Value:</label>
                    <input type="text" name="parameter_value" class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product:</label>
                    <select name="product_id" class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">-- Select Product --</option>';
    
    foreach ($products as $product) {
        echo '<option value="' . $product->id . '">' . htmlspecialchars($product->name) . ' (SKU: ' . htmlspecialchars($product->sku) . ')</option>';
    }
    
    echo '</select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Create Link</button>
                </div>
            </div>
        </form>
        
        <h3 class="text-lg font-medium mb-2">Current Links:</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border text-left">ID</th>
                        <th class="px-4 py-2 border text-left">Parameter</th>
                        <th class="px-4 py-2 border text-left">Product</th>
                        <th class="px-4 py-2 border text-left">Created At</th>
                        <th class="px-4 py-2 border text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>';
    
    // 过滤当前模板的链接
    $templateLinks = array_filter((array)$links, function($link) use ($selectedTemplate) {
        return $link->product_template_id == $selectedTemplate->id;
    });
    
    if (count($templateLinks) > 0) {
        foreach ($templateLinks as $link) {
            $productName = isset($products[$link->product_id]) ? $products[$link->product_id]->name : 'Unknown Product';
            
            echo '<tr>
                <td class="px-4 py-2 border">' . $link->id . '</td>
                <td class="px-4 py-2 border"><code class="bg-blue-50 px-2 py-1 rounded">' . htmlspecialchars($link->parameter_group) . '</code></td>
                <td class="px-4 py-2 border">' . htmlspecialchars($productName) . ' (ID: ' . $link->product_id . ')</td>
                <td class="px-4 py-2 border">' . $link->created_at . '</td>
                <td class="px-4 py-2 border">
                    <form action="" method="post" class="inline">
                        <input type="hidden" name="action" value="unlink">
                        <input type="hidden" name="link_id" value="' . $link->id . '">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1 rounded">Remove</button>
                    </form>
                </td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="5" class="px-4 py-2 border text-center text-gray-500">No links found for this template</td></tr>';
    }
    
    echo '</tbody>
            </table>
        </div>
    </div>';
}

// 显示所有链接的表格
echo '<div class="bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-semibold mb-4">All Links in Database</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 border text-left">ID</th>
                    <th class="px-4 py-2 border text-left">Template</th>
                    <th class="px-4 py-2 border text-left">Parameter</th>
                    <th class="px-4 py-2 border text-left">Product</th>
                    <th class="px-4 py-2 border text-left">Created At</th>
                </tr>
            </thead>
            <tbody>';

if (count($links) > 0) {
    foreach ($links as $link) {
        $templateName = isset($templates[$link->product_template_id]) ? $templates[$link->product_template_id]->name : 'Unknown Template';
        $productName = isset($products[$link->product_id]) ? $products[$link->product_id]->name : 'Unknown Product';
        
        echo '<tr>
            <td class="px-4 py-2 border">' . $link->id . '</td>
            <td class="px-4 py-2 border">' . htmlspecialchars($templateName) . ' (ID: ' . $link->product_template_id . ')</td>
            <td class="px-4 py-2 border"><code class="bg-blue-50 px-2 py-1 rounded">' . htmlspecialchars($link->parameter_group) . '</code></td>
            <td class="px-4 py-2 border">' . htmlspecialchars($productName) . ' (ID: ' . $link->product_id . ')</td>
            <td class="px-4 py-2 border">' . $link->created_at . '</td>
        </tr>';
    }
} else {
    echo '<tr><td colspan="5" class="px-4 py-2 border text-center text-gray-500">No links found in database</td></tr>';
}

echo '</tbody>
        </table>
    </div>
</div>';

echo '</div>
</body>
</html>'; 
// 加载 Laravel 环境
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 获取当前链接状态
$links = \Illuminate\Support\Facades\DB::table('product_template_product')->get();

// 获取模板和产品信息
$templates = \App\Models\ProductTemplate::all()->keyBy('id');
$products = \App\Models\Product::all()->keyBy('id');

// 处理表单提交
$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'link' && isset($_POST['template_id'], $_POST['product_id'], $_POST['parameter_group'], $_POST['parameter_value'])) {
        try {
            $templateId = $_POST['template_id'];
            $productId = $_POST['product_id'];
            $paramGroup = $_POST['parameter_group'];
            $paramValue = $_POST['parameter_value'];
            
            // 创建完整标识符
            $paramIdentifier = $paramGroup . '=' . $paramValue;
            
            // 检查是否已经有链接
            $existingLink = \Illuminate\Support\Facades\DB::table('product_template_product')
                ->where('product_template_id', $templateId)
                ->where('parameter_group', $paramIdentifier)
                ->first();
                
            if ($existingLink) {
                // 先删除已有链接
                \Illuminate\Support\Facades\DB::table('product_template_product')
                    ->where('product_template_id', $templateId)
                    ->where('parameter_group', $paramIdentifier)
                    ->delete();
            }
            
            // 创建新链接
            \Illuminate\Support\Facades\DB::table('product_template_product')->insert([
                'product_template_id' => $templateId,
                'product_id' => $productId,
                'parameter_group' => $paramIdentifier,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $message = "Product successfully linked to parameter: $paramIdentifier";
            $messageType = "success";
            
            // 刷新链接数据
            $links = \Illuminate\Support\Facades\DB::table('product_template_product')->get();
            
        } catch (\Exception $e) {
            $message = "Error linking product: " . $e->getMessage();
            $messageType = "error";
        }
    } elseif ($_POST['action'] === 'unlink' && isset($_POST['link_id'])) {
        try {
            $linkId = $_POST['link_id'];
            
            // 删除链接
            \Illuminate\Support\Facades\DB::table('product_template_product')
                ->where('id', $linkId)
                ->delete();
                
            $message = "Link successfully removed";
            $messageType = "success";
            
            // 刷新链接数据
            $links = \Illuminate\Support\Facades\DB::table('product_template_product')->get();
            
        } catch (\Exception $e) {
            $message = "Error removing link: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// 获取选择的模板
$selectedTemplateId = $_GET['template_id'] ?? null;
$selectedTemplate = $selectedTemplateId ? $templates[$selectedTemplateId] ?? null : null;

// 页面头部
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product-Parameter Link Tester</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-5">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Product-Parameter Link Tester</h1>';

// 显示消息
if (!empty($message)) {
    $bgColor = $messageType === "success" ? "bg-green-100 border-green-500 text-green-700" : "bg-red-100 border-red-500 text-red-700";
    echo '<div class="' . $bgColor . ' border-l-4 p-4 mb-5" role="alert">
        <p>' . htmlspecialchars($message) . '</p>
    </div>';
}

// 模板选择表单
echo '<div class="bg-white shadow-md rounded p-6 mb-6">
    <h2 class="text-xl font-semibold mb-4">Select Template</h2>
    <form action="" method="get">
        <div class="flex items-center space-x-3">
            <select name="template_id" class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Select a Template --</option>';

foreach ($templates as $template) {
    $selected = $selectedTemplateId == $template->id ? 'selected' : '';
    echo '<option value="' . $template->id . '" ' . $selected . '>' . htmlspecialchars($template->name) . ' (ID: ' . $template->id . ')</option>';
}

echo '</select>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">View Template</button>
        </div>
    </form>
</div>';

// 显示当前模板和参数
if ($selectedTemplate) {
    // 解析模板参数
    $parameters = json_decode($selectedTemplate->parameters, true);
    if (!is_array($parameters)) {
        $parameters = [];
    }
    
    echo '<div class="bg-white shadow-md rounded p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Template: ' . htmlspecialchars($selectedTemplate->name) . '</h2>
        
        <div class="mb-4">
            <h3 class="text-lg font-medium mb-2">Parameters:</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    
    foreach ($parameters as $param) {
        if (!isset($param['name']) || !isset($param['values']) || !is_array($param['values'])) {
            continue;
        }
        
        $paramName = $param['name'];
        $paramValues = $param['values'];
        
        echo '<div class="border rounded p-4">
            <h4 class="font-medium mb-2">' . htmlspecialchars($paramName) . '</h4>
            <div class="flex flex-wrap gap-2">';
        
        foreach ($paramValues as $value) {
            $paramIdentifier = $paramName . '=' . $value;
            
            // 查找链接状态
            $linkedProduct = null;
            foreach ($links as $link) {
                if ($link->product_template_id == $selectedTemplate->id && $link->parameter_group == $paramIdentifier) {
                    $linkedProduct = $products[$link->product_id] ?? null;
                    break;
                }
            }
            
            if ($linkedProduct) {
                echo '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full flex items-center">
                    ' . htmlspecialchars($value) . '
                    <span class="inline-block w-1 h-1 bg-green-600 rounded-full mx-1"></span>
                    ' . htmlspecialchars($linkedProduct->name) . '
                </span>';
            } else {
                echo '<span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                    ' . htmlspecialchars($value) . '
                </span>';
            }
        }
        
        echo '</div>
        </div>';
    }
    
    echo '</div>
        </div>
        
        <h3 class="text-lg font-medium mb-2">Create New Link:</h3>
        <form action="" method="post" class="mb-4">
            <input type="hidden" name="action" value="link">
            <input type="hidden" name="template_id" value="' . $selectedTemplate->id . '">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parameter Group:</label>
                    <select name="parameter_group" class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">-- Select Parameter Group --</option>';
    
    foreach ($parameters as $param) {
        if (isset($param['name'])) {
            echo '<option value="' . htmlspecialchars($param['name']) . '">' . htmlspecialchars($param['name']) . '</option>';
        }
    }
    
    echo '</select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parameter Value:</label>
                    <input type="text" name="parameter_value" class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product:</label>
                    <select name="product_id" class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">-- Select Product --</option>';
    
    foreach ($products as $product) {
        echo '<option value="' . $product->id . '">' . htmlspecialchars($product->name) . ' (SKU: ' . htmlspecialchars($product->sku) . ')</option>';
    }
    
    echo '</select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Create Link</button>
                </div>
            </div>
        </form>
        
        <h3 class="text-lg font-medium mb-2">Current Links:</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border text-left">ID</th>
                        <th class="px-4 py-2 border text-left">Parameter</th>
                        <th class="px-4 py-2 border text-left">Product</th>
                        <th class="px-4 py-2 border text-left">Created At</th>
                        <th class="px-4 py-2 border text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>';
    
    // 过滤当前模板的链接
    $templateLinks = array_filter((array)$links, function($link) use ($selectedTemplate) {
        return $link->product_template_id == $selectedTemplate->id;
    });
    
    if (count($templateLinks) > 0) {
        foreach ($templateLinks as $link) {
            $productName = isset($products[$link->product_id]) ? $products[$link->product_id]->name : 'Unknown Product';
            
            echo '<tr>
                <td class="px-4 py-2 border">' . $link->id . '</td>
                <td class="px-4 py-2 border"><code class="bg-blue-50 px-2 py-1 rounded">' . htmlspecialchars($link->parameter_group) . '</code></td>
                <td class="px-4 py-2 border">' . htmlspecialchars($productName) . ' (ID: ' . $link->product_id . ')</td>
                <td class="px-4 py-2 border">' . $link->created_at . '</td>
                <td class="px-4 py-2 border">
                    <form action="" method="post" class="inline">
                        <input type="hidden" name="action" value="unlink">
                        <input type="hidden" name="link_id" value="' . $link->id . '">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1 rounded">Remove</button>
                    </form>
                </td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="5" class="px-4 py-2 border text-center text-gray-500">No links found for this template</td></tr>';
    }
    
    echo '</tbody>
            </table>
        </div>
    </div>';
}

// 显示所有链接的表格
echo '<div class="bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-semibold mb-4">All Links in Database</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 border text-left">ID</th>
                    <th class="px-4 py-2 border text-left">Template</th>
                    <th class="px-4 py-2 border text-left">Parameter</th>
                    <th class="px-4 py-2 border text-left">Product</th>
                    <th class="px-4 py-2 border text-left">Created At</th>
                </tr>
            </thead>
            <tbody>';

if (count($links) > 0) {
    foreach ($links as $link) {
        $templateName = isset($templates[$link->product_template_id]) ? $templates[$link->product_template_id]->name : 'Unknown Template';
        $productName = isset($products[$link->product_id]) ? $products[$link->product_id]->name : 'Unknown Product';
        
        echo '<tr>
            <td class="px-4 py-2 border">' . $link->id . '</td>
            <td class="px-4 py-2 border">' . htmlspecialchars($templateName) . ' (ID: ' . $link->product_template_id . ')</td>
            <td class="px-4 py-2 border"><code class="bg-blue-50 px-2 py-1 rounded">' . htmlspecialchars($link->parameter_group) . '</code></td>
            <td class="px-4 py-2 border">' . htmlspecialchars($productName) . ' (ID: ' . $link->product_id . ')</td>
            <td class="px-4 py-2 border">' . $link->created_at . '</td>
        </tr>';
    }
} else {
    echo '<tr><td colspan="5" class="px-4 py-2 border text-center text-gray-500">No links found in database</td></tr>';
}

echo '</tbody>
        </table>
    </div>
</div>';

echo '</div>
</body>
</html>'; 