<?php
// 加载 Laravel 环境
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 获取CSRF令牌
$token = csrf_token();

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>链接参数调试</title>
    <meta name="csrf-token" content="'.$token.'">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-5">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-md shadow-md">
        <h1 class="text-2xl font-bold mb-6">参数链接测试</h1>
        
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">直接API调用</h2>
            <form id="api-form" class="bg-gray-50 p-4 rounded-md border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">模板ID:</label>
                        <input type="number" id="template_id" name="template_id" value="1" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">产品ID:</label>
                        <input type="number" id="product_id" name="product_id" value="5" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">参数组:</label>
                        <input type="text" id="parameter_group" name="parameter_group" value="color" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">参数值:</label>
                        <input type="text" id="parameter_value" name="parameter_value" value="red" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="button" id="send-api" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        发送API请求
                    </button>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">请求和响应:</label>
                    <div id="request-preview" class="mb-2 bg-gray-800 text-green-400 p-3 rounded font-mono text-sm overflow-auto max-h-40"></div>
                    <div id="api-result" class="bg-gray-800 text-white p-3 rounded font-mono text-sm overflow-auto max-h-40"></div>
                </div>
            </form>
        </div>
        
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">数据库状态</h2>
            <div id="db-status" class="bg-gray-50 p-4 rounded-md border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border">ID</th>
                                <th class="px-4 py-2 border">Template ID</th>
                                <th class="px-4 py-2 border">Product ID</th>
                                <th class="px-4 py-2 border">Parameter Group</th>
                                <th class="px-4 py-2 border">Created</th>
                                <th class="px-4 py-2 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody>';

// 显示现有链接
$links = \Illuminate\Support\Facades\DB::table('product_template_product')->get();
$templates = \App\Models\ProductTemplate::all()->keyBy('id');
$products = \App\Models\Product::all()->keyBy('id');

foreach ($links as $link) {
    $templateName = isset($templates[$link->product_template_id]) ? $templates[$link->product_template_id]->name : 'Unknown';
    $productName = isset($products[$link->product_id]) ? $products[$link->product_id]->name : 'Unknown';
    
    echo '
        <tr>
            <td class="px-4 py-2 border">'.$link->id.'</td>
            <td class="px-4 py-2 border">'.$link->product_template_id.' ('.$templateName.')</td>
            <td class="px-4 py-2 border">'.$link->product_id.' ('.$productName.')</td>
            <td class="px-4 py-2 border"><code class="bg-blue-50 px-2 py-1 rounded">'.htmlspecialchars($link->parameter_group).'</code></td>
            <td class="px-4 py-2 border">'.$link->created_at.'</td>
            <td class="px-4 py-2 border">
                <button class="delete-link px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs" data-id="'.$link->id.'">
                    删除
                </button>
            </td>
        </tr>';
}

if (count($links) == 0) {
    echo '<tr><td colspan="6" class="px-4 py-2 border text-center text-gray-500">没有链接数据</td></tr>';
}

echo '</tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <button id="refresh-db" class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
                        刷新数据
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">控制器代码检查</h2>
            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                <pre class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm overflow-auto max-h-80">
/**
 * 处理链接产品到模板的参数值
 *
 * @param Request $request
 * @return JsonResponse
 */
public function storeParameterValueLink(Request $request): JsonResponse
{
    try {
        // 验证请求
        $validated = $request->validate([
            \'template_id\' => \'required|exists:product_templates,id\',
            \'product_id\' => \'required|exists:products,id\',
            \'parameter_group\' => \'required|string\',
            \'parameter_value\' => \'required|string\',
        ]);
        
        $template = ProductTemplate::findOrFail($validated[\'template_id\']);
        $product = Product::findOrFail($validated[\'product_id\']);
        
        // 创建参数值完整标识符（如：color=red）
        $parameterIdentifier = $validated[\'parameter_group\'] . \'=\' . $validated[\'parameter_value\'];
        
        // 检查是否已有产品链接到此参数值
        $existingLink = $template->linkedProducts()
            ->wherePivot(\'parameter_group\', $parameterIdentifier)
            ->first();
            
        if ($existingLink && $existingLink->id !== (int)$validated[\'product_id\']) {
            // 先删除现有链接
            $template->linkedProducts()->detach($existingLink->id);
        }
        
        // 创建新链接 - 确保使用完整的参数标识符
        $template->linkedProducts()->syncWithoutDetaching([
            $validated[\'product_id\'] => [\'parameter_group\' => $parameterIdentifier]
        ]);
        
        // 返回成功响应
        return response()->json([
            \'status\' => \'success\',
            \'message\' => \'产品已成功链接到参数值\',
            \'data\' => [
                \'template_id\' => $template->id,
                \'template_name\' => $template->name,
                \'product_id\' => $product->id,
                \'product_name\' => $product->name,
                \'parameter_group\' => $validated[\'parameter_group\'],
                \'parameter_value\' => $validated[\'parameter_value\'],
                \'parameter_identifier\' => $parameterIdentifier
            ]
        ]);
    } catch (Exception $e) {
        // 错误处理...
    }
}</pre>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // 发送API请求
        document.getElementById("send-api").addEventListener("click", function() {
            const template_id = document.getElementById("template_id").value;
            const product_id = document.getElementById("product_id").value;
            const parameter_group = document.getElementById("parameter_group").value;
            const parameter_value = document.getElementById("parameter_value").value;
            
            const requestData = {
                template_id,
                product_id,
                parameter_group,
                parameter_value
            };
            
            document.getElementById("request-preview").textContent = JSON.stringify(requestData, null, 2);
            document.getElementById("api-result").textContent = "Loading...";
            
            fetch("/products/store-parameter-value-link", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(\'meta[name="csrf-token"]\').getAttribute("content")
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("api-result").textContent = JSON.stringify(data, null, 2);
                refreshDB();
            })
            .catch(error => {
                document.getElementById("api-result").textContent = "Error: " + error.message;
            });
        });
        
        // 刷新数据库状态
        document.getElementById("refresh-db").addEventListener("click", refreshDB);
        
        // 删除链接
        document.querySelectorAll(".delete-link").forEach(button => {
            button.addEventListener("click", function() {
                const id = this.getAttribute("data-id");
                
                fetch("/products/unlink-parameter-value", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(\'meta[name="csrf-token"]\').getAttribute("content")
                    },
                    body: JSON.stringify({
                        template_id: 1,
                        parameter_group: this.closest("tr").querySelector("code").textContent
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert("链接已删除");
                    window.location.reload();
                })
                .catch(error => {
                    alert("Error: " + error.message);
                });
            });
        });
        
        function refreshDB() {
            window.location.reload();
        }
    });
    </script>
</body>
</html>'; 
// 加载 Laravel 环境
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 获取CSRF令牌
$token = csrf_token();

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>链接参数调试</title>
    <meta name="csrf-token" content="'.$token.'">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-5">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-md shadow-md">
        <h1 class="text-2xl font-bold mb-6">参数链接测试</h1>
        
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">直接API调用</h2>
            <form id="api-form" class="bg-gray-50 p-4 rounded-md border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">模板ID:</label>
                        <input type="number" id="template_id" name="template_id" value="1" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">产品ID:</label>
                        <input type="number" id="product_id" name="product_id" value="5" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">参数组:</label>
                        <input type="text" id="parameter_group" name="parameter_group" value="color" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">参数值:</label>
                        <input type="text" id="parameter_value" name="parameter_value" value="red" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="button" id="send-api" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        发送API请求
                    </button>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">请求和响应:</label>
                    <div id="request-preview" class="mb-2 bg-gray-800 text-green-400 p-3 rounded font-mono text-sm overflow-auto max-h-40"></div>
                    <div id="api-result" class="bg-gray-800 text-white p-3 rounded font-mono text-sm overflow-auto max-h-40"></div>
                </div>
            </form>
        </div>
        
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">数据库状态</h2>
            <div id="db-status" class="bg-gray-50 p-4 rounded-md border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border">ID</th>
                                <th class="px-4 py-2 border">Template ID</th>
                                <th class="px-4 py-2 border">Product ID</th>
                                <th class="px-4 py-2 border">Parameter Group</th>
                                <th class="px-4 py-2 border">Created</th>
                                <th class="px-4 py-2 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody>';

// 显示现有链接
$links = \Illuminate\Support\Facades\DB::table('product_template_product')->get();
$templates = \App\Models\ProductTemplate::all()->keyBy('id');
$products = \App\Models\Product::all()->keyBy('id');

foreach ($links as $link) {
    $templateName = isset($templates[$link->product_template_id]) ? $templates[$link->product_template_id]->name : 'Unknown';
    $productName = isset($products[$link->product_id]) ? $products[$link->product_id]->name : 'Unknown';
    
    echo '
        <tr>
            <td class="px-4 py-2 border">'.$link->id.'</td>
            <td class="px-4 py-2 border">'.$link->product_template_id.' ('.$templateName.')</td>
            <td class="px-4 py-2 border">'.$link->product_id.' ('.$productName.')</td>
            <td class="px-4 py-2 border"><code class="bg-blue-50 px-2 py-1 rounded">'.htmlspecialchars($link->parameter_group).'</code></td>
            <td class="px-4 py-2 border">'.$link->created_at.'</td>
            <td class="px-4 py-2 border">
                <button class="delete-link px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs" data-id="'.$link->id.'">
                    删除
                </button>
            </td>
        </tr>';
}

if (count($links) == 0) {
    echo '<tr><td colspan="6" class="px-4 py-2 border text-center text-gray-500">没有链接数据</td></tr>';
}

echo '</tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <button id="refresh-db" class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
                        刷新数据
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3">控制器代码检查</h2>
            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                <pre class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm overflow-auto max-h-80">
/**
 * 处理链接产品到模板的参数值
 *
 * @param Request $request
 * @return JsonResponse
 */
public function storeParameterValueLink(Request $request): JsonResponse
{
    try {
        // 验证请求
        $validated = $request->validate([
            \'template_id\' => \'required|exists:product_templates,id\',
            \'product_id\' => \'required|exists:products,id\',
            \'parameter_group\' => \'required|string\',
            \'parameter_value\' => \'required|string\',
        ]);
        
        $template = ProductTemplate::findOrFail($validated[\'template_id\']);
        $product = Product::findOrFail($validated[\'product_id\']);
        
        // 创建参数值完整标识符（如：color=red）
        $parameterIdentifier = $validated[\'parameter_group\'] . \'=\' . $validated[\'parameter_value\'];
        
        // 检查是否已有产品链接到此参数值
        $existingLink = $template->linkedProducts()
            ->wherePivot(\'parameter_group\', $parameterIdentifier)
            ->first();
            
        if ($existingLink && $existingLink->id !== (int)$validated[\'product_id\']) {
            // 先删除现有链接
            $template->linkedProducts()->detach($existingLink->id);
        }
        
        // 创建新链接 - 确保使用完整的参数标识符
        $template->linkedProducts()->syncWithoutDetaching([
            $validated[\'product_id\'] => [\'parameter_group\' => $parameterIdentifier]
        ]);
        
        // 返回成功响应
        return response()->json([
            \'status\' => \'success\',
            \'message\' => \'产品已成功链接到参数值\',
            \'data\' => [
                \'template_id\' => $template->id,
                \'template_name\' => $template->name,
                \'product_id\' => $product->id,
                \'product_name\' => $product->name,
                \'parameter_group\' => $validated[\'parameter_group\'],
                \'parameter_value\' => $validated[\'parameter_value\'],
                \'parameter_identifier\' => $parameterIdentifier
            ]
        ]);
    } catch (Exception $e) {
        // 错误处理...
    }
}</pre>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // 发送API请求
        document.getElementById("send-api").addEventListener("click", function() {
            const template_id = document.getElementById("template_id").value;
            const product_id = document.getElementById("product_id").value;
            const parameter_group = document.getElementById("parameter_group").value;
            const parameter_value = document.getElementById("parameter_value").value;
            
            const requestData = {
                template_id,
                product_id,
                parameter_group,
                parameter_value
            };
            
            document.getElementById("request-preview").textContent = JSON.stringify(requestData, null, 2);
            document.getElementById("api-result").textContent = "Loading...";
            
            fetch("/products/store-parameter-value-link", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(\'meta[name="csrf-token"]\').getAttribute("content")
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("api-result").textContent = JSON.stringify(data, null, 2);
                refreshDB();
            })
            .catch(error => {
                document.getElementById("api-result").textContent = "Error: " + error.message;
            });
        });
        
        // 刷新数据库状态
        document.getElementById("refresh-db").addEventListener("click", refreshDB);
        
        // 删除链接
        document.querySelectorAll(".delete-link").forEach(button => {
            button.addEventListener("click", function() {
                const id = this.getAttribute("data-id");
                
                fetch("/products/unlink-parameter-value", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(\'meta[name="csrf-token"]\').getAttribute("content")
                    },
                    body: JSON.stringify({
                        template_id: 1,
                        parameter_group: this.closest("tr").querySelector("code").textContent
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert("链接已删除");
                    window.location.reload();
                })
                .catch(error => {
                    alert("Error: " + error.message);
                });
            });
        });
        
        function refreshDB() {
            window.location.reload();
        }
    });
    </script>
</body>
</html>'; 