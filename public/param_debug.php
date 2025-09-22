<?php
// 显示错误信息
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 启用日志记录 
file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Request received: " . json_encode($_GET) . "\n", FILE_APPEND);

// 获取请求参数
$id = $_GET['id'] ?? null;
$templateId = $_GET['template_id'] ?? $id;  // 支持两种参数名称
$generate = isset($_GET['generate']) && $_GET['generate'] == 1;

// 记录使用的ID
file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Using template ID: " . $templateId . "\n", FILE_APPEND);

// 确保有模板ID
if (!$templateId) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing template ID parameter. Please provide template_id or id parameter.'
    ]);
    file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Error: Missing template ID\n", FILE_APPEND);
    exit;
}

// 从数据库加载产品模板
try {
    // 使用Laravel数据库连接
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    $db = app('db');
    
    // 记录查询开始
    file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Querying database for template ID: " . $templateId . "\n", FILE_APPEND);
    
    // 查询产品模板
    $template = $db->table('product_templates')->where('id', $templateId)->first();
    
    if (!$template) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => "Template not found with ID: $templateId"
        ]);
        file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Error: Template not found with ID: " . $templateId . "\n", FILE_APPEND);
        exit;
    }
    
    // 解析参数
    $parameters = json_decode($template->parameters, true);
    
    // 记录参数解析结果
    file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Parameters loaded: " . json_encode($parameters) . "\n", FILE_APPEND);
    
    if (empty($parameters)) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Template has no parameters defined',
            'parameters' => $parameters
        ]);
        file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Error: No parameters defined\n", FILE_APPEND);
        exit;
    }
    
    // 获取已链接的产品
    $linkedProducts = $db->table('product_template_product')
        ->where('product_template_id', $templateId)
        ->get();
    
    // 记录链接产品数量
    file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Found " . count($linkedProducts) . " linked products\n", FILE_APPEND);
    
    // 构建参数组合与产品的映射
    $linkedCombosMap = [];
    foreach ($linkedProducts as $link) {
        $productId = $link->product_id;
        $parameterGroup = $link->parameter_group;
        
        // 查询产品详情
        $product = $db->table('products')->where('id', $productId)->first();
        
        if ($product) {
            $linkedCombosMap[$parameterGroup] = [
                'id' => $productId,
                'name' => $product->name,
                'sku' => $product->sku
            ];
        }
    }
    
    // 记录映射结果
    file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Created mapping with " . count($linkedCombosMap) . " combinations\n", FILE_APPEND);
    
    // 生成所有可能的参数组合
    function generateParameterCombinations($parameters) {
        // 检查参数结构
        if (empty($parameters)) {
            return [];
        }
        
        // 参数格式可能有两种:
        // 1. 数组格式: ['color' => ['red', 'blue'], 'size' => ['small', 'big']]
        // 2. 对象格式: [{name: 'color', values: ['red', 'blue']}, {name: 'size', values: ['small', 'big']}]
        
        $paramMap = [];
        
        // 检测并转换为统一格式
        if (isset($parameters[0]) && is_array($parameters[0]) && isset($parameters[0]['name'])) {
            // 对象格式
            foreach ($parameters as $param) {
                if (isset($param['name']) && isset($param['values']) && is_array($param['values'])) {
                    $paramMap[$param['name']] = $param['values'];
                }
            }
        } else {
            // 数组格式，或者直接就是符合要求的格式
            $paramMap = $parameters;
        }
        
        // 准备生成组合
        $paramNames = array_keys($paramMap);
        $combinations = [];
        $current = [];
        
        generateCombinationsRecursive($paramMap, $paramNames, 0, $current, $combinations);
        
        return $combinations;
    }
    
    function generateCombinationsRecursive($paramMap, $paramNames, $index, $current, &$combinations) {
        if ($index >= count($paramNames)) {
            $combinations[] = $current;
            return;
        }
        
        $currentParam = $paramNames[$index];
        $values = $paramMap[$currentParam];
        
        if (!is_array($values)) {
            $values = [$values]; // 处理单个值
        }
        
        foreach ($values as $value) {
            $newCurrent = $current;
            $newCurrent[$currentParam] = $value;
            generateCombinationsRecursive($paramMap, $paramNames, $index + 1, $newCurrent, $combinations);
        }
    }
    
    function formatParameterCombo($combo) {
        if (empty($combo) || !is_array($combo)) {
            return '';
        }
        
        $parts = [];
        foreach ($combo as $name => $value) {
            // 确保值是字符串
            if (is_array($value)) {
                $value = implode(',', $value);
            } else if (is_object($value)) {
                $value = json_encode($value);
            } else if ($value === null) {
                $value = '';
            } else {
                $value = (string)$value;
            }
            
            $parts[] = $name . '=' . $value;
        }
        
        return implode(';', $parts);
    }
    
    $allCombinations = generateParameterCombinations($parameters);
    
    // 格式化组合并添加链接状态
    $formattedCombinations = [];
    foreach ($allCombinations as $combo) {
        $comboString = formatParameterCombo($combo);
        $isLinked = isset($linkedCombosMap[$comboString]);
        
        $formattedCombinations[] = [
            'parameter_group_string' => $comboString,
            'parameter_values' => $combo,
            'is_linked' => $isLinked,
            'product' => $isLinked ? $linkedCombosMap[$comboString] : null
        ];
    }
    
    // 记录生成的组合数量
    file_put_contents(
        'param_debug_log.txt', 
        date('Y-m-d H:i:s') . " - Generated " . count($formattedCombinations) . " formatted combinations\n", 
        FILE_APPEND
    );
    
    // 添加更多调试信息
    file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Parameters structure: " . json_encode($parameters) . "\n", FILE_APPEND);

    // 记录更详细的组合信息
    if (!empty($formattedCombinations)) {
        file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - First combination structure: " . json_encode($formattedCombinations[0]) . "\n", FILE_APPEND);
    }

    // 记录最终响应结构
    file_put_contents('param_debug_log.txt', date('Y-m-d H:i:s') . " - Response structure: status=success, params_count=" . count($parameters) . ", combinations_count=" . count($formattedCombinations) . "\n", FILE_APPEND);
    
    // 返回JSON响应
    $response = [
        'status' => 'success',
        'message' => 'Parameter combinations generated successfully',
        'parameters' => $parameters,
        'combinations' => $formattedCombinations,
        'total' => count($formattedCombinations),
        'linked' => count($linkedCombosMap)
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
    // 记录成功响应
    file_put_contents(
        'param_debug_log.txt', 
        date('Y-m-d H:i:s') . " - Success response sent with " . count($formattedCombinations) . " combinations\n", 
        FILE_APPEND
    );
    
} catch (\Exception $e) {
    // 记录异常
    file_put_contents(
        'param_debug_log.txt', 
        date('Y-m-d H:i:s') . " - Exception occurred: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", 
        FILE_APPEND
    );
    
    // 返回错误响应
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Error generating parameter combinations: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} 