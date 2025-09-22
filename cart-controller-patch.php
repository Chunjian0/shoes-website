<?php
/*
购物车控制器参数处理修复方案

问题描述：
当添加带有color=yellow和size=big参数的模板商品到购物车时，系统会错误地使用
默认的第一个参数组合（color=red和size=big），导致价格和显示不正确。

原因分析：
1. CartController在处理请求时，没有正确获取和处理color和size参数
2. 当缺少规格时，系统会从product_template_product表中获取第一个匹配的参数组合
3. 前端发送的规格参数没有被正确应用到购物车项中

修复步骤：
1. 修改CartController.php中的store方法，确保正确处理规格参数
2. 增强参数处理逻辑，优先使用直接指定的color和size参数
3. 添加详细的日志记录，帮助诊断和验证修复效果

修复位置：
app/Http/Controllers/Api/CartController.php 的 store 方法，
在处理规格信息部分（大约在 290-320 行附近）
*/

// 这里是需要替换的代码片段
// 将下面的代码替换到CartController.php中对应位置

// ===== 开始替换 =====

// 处理规格信息，确保参数名和值都被trim
$specifications = [];

// 记录输入的参数
file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 处理参数: color=" . $request->input('color') . ", size=" . $request->input('size') . "\n", FILE_APPEND);
file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 规格参数: " . json_encode($request->input('specifications')) . "\n", FILE_APPEND);

// 处理传入的parameter_group字符串
$parameterGroup = $request->input('parameter_group');
if ($parameterGroup) {
    file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 处理parameter_group: " . $parameterGroup . "\n", FILE_APPEND);
    $parameters = explode(';', $parameterGroup);
    foreach ($parameters as $param) {
        $parts = explode('=', $param);
        if (count($parts) == 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if (!empty($key) && !empty($value)) {
                $specifications[$key] = $value;
            }
        }
    }
}

// 优先处理直接的color和size参数
if ($request->has('color')) {
    $specifications['color'] = trim($request->color);
    file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 从直接参数添加color: " . $specifications['color'] . "\n", FILE_APPEND);
}

if ($request->has('size')) {
    $specifications['size'] = trim($request->size);
    file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 从直接参数添加size: " . $specifications['size'] . "\n", FILE_APPEND);
}

// 处理传入的specifications参数(合并而不是覆盖)
if ($request->has('specifications') && is_array($request->input('specifications'))) {
    foreach ($request->input('specifications') as $key => $value) {
        // 跳过已从直接参数设置的值，保持优先级
        if (($key == 'color' && $request->has('color')) || 
            ($key == 'size' && $request->has('size'))) {
            continue;
        }
        
        if (!empty($key) && $value !== null && $value !== '') {
            $specifications[trim($key)] = trim($value);
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 从specifications添加{$key}: {$value}\n", FILE_APPEND);
        }
    }
}

// 添加模板ID到规格中，以便后续查找
if ($request->has('template_id') && $request->template_id) {
    $specifications['template_id'] = (int)$request->template_id;
    file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 添加template_id: " . $specifications['template_id'] . "\n", FILE_APPEND);
    
    // 仅当color和size都没有指定时，才从模板获取默认参数
    if (empty($specifications['color']) && empty($specifications['size'])) {
        file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 颜色和尺寸都未指定，尝试从模板获取默认参数\n", FILE_APPEND);
        
        $product = Product::find($request->product_id);
        $template = ProductTemplate::find($request->template_id);
        
        if ($product && $template) {
            $pivot = DB::table('product_template_product')
                ->where('product_template_id', $template->id)
                ->where('product_id', $product->id)
                ->first();
                
            if ($pivot && $pivot->parameter_group) {
                $params = explode(';', $pivot->parameter_group);
                foreach ($params as $param) {
                    $parts = explode('=', $param);
                    if (count($parts) == 2) {
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        if (!empty($key) && !empty($value)) {
                            $specifications[$key] = $value;
                        }
                    }
                }
                file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 从模板获取默认参数: " . $pivot->parameter_group . "\n", FILE_APPEND);
            }
        }
    } else {
        file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 已指定颜色或尺寸，不从模板获取默认参数\n", FILE_APPEND);
    }
}

file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - 最终规格: " . json_encode($specifications) . "\n", FILE_APPEND);

// ===== 结束替换 =====

?>

<?php
/*
修复验证方法：

1. 应用此补丁后，通过前端添加不同规格的商品到购物车
2. 检查购物车中的商品是否显示正确的规格和价格
3. 检查storage/logs/cart-debug.log文件中的规格处理过程

可能需要的额外修复：

1. 在CartItem模型中确保specifications字段被正确序列化和反序列化
2. 检查cart_items表确保specifications列是JSON类型
3. 确保前端在添加商品到购物车时正确传递color和size参数
*/
?> 