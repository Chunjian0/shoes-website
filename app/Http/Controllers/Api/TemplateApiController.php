<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductTemplate;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TemplateApiController extends Controller
{
    /**
     * 查找下一个未链接的参数
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function nextUnlinkedParameter(Request $request, $id)
    {
        try {
            $template = ProductTemplate::findOrFail($id);
            $currentGroup = $request->input('current_group');
            $currentValue = $request->input('current_value');
            
            // 如果没有参数信息，返回错误
            if (!$currentGroup || !$currentValue) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Missing current parameter information'
                ], 400);
            }
            
            // 获取模板参数
            $parameters = $template->parameters;
            if (!is_array($parameters) || empty($parameters)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template has no parameters'
                ], 404);
            }
            
            // 获取已链接的参数
            $linkedParams = DB::table('product_template_product')
                ->where('product_template_id', $id)
                ->get()
                ->pluck('parameter_group')
                ->toArray();
            
            // 创建一个字典存储所有参数组合
            $allCombinations = [];
            $foundCurrent = false;
            
            // 先遍历所有参数，确定顺序
            foreach ($parameters as $param) {
                if (!isset($param['name']) || !isset($param['values']) || !is_array($param['values'])) {
                    continue;
                }
                
                foreach ($param['values'] as $value) {
                    $paramIdentifier = $param['name'] . '=' . $value;
                    
                    // 如果是当前参数，标记已找到
                    if ($param['name'] === $currentGroup && $value === $currentValue) {
                        $foundCurrent = true;
                        continue;
                    }
                    
                    // 如果已经找到当前参数，且此参数未链接，返回它
                    if ($foundCurrent && !in_array($paramIdentifier, $linkedParams)) {
                        return response()->json([
                            'status' => 'success',
                            'next_parameter' => [
                                'group' => $param['name'],
                                'value' => $value,
                                'identifier' => $paramIdentifier
                            ]
                        ]);
                    }
                    
                    // 存储所有未链接的参数，以便在没有找到当前参数后的未链接参数时使用
                    if (!in_array($paramIdentifier, $linkedParams)) {
                        $allCombinations[] = [
                            'group' => $param['name'],
                            'value' => $value,
                            'identifier' => $paramIdentifier
                        ];
                    }
                }
            }
            
            // 如果没有找到当前参数后的未链接参数，但有其他未链接的参数，返回第一个
            if (!empty($allCombinations)) {
                return response()->json([
                    'status' => 'success',
                    'next_parameter' => $allCombinations[0]
                ]);
            }
            
            // 没有未链接的参数
            return response()->json([
                'status' => 'success',
                'message' => 'All parameters have been linked',
                'next_parameter' => null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error finding next unlinked parameter: ' . $e->getMessage(), [
                'exception' => $e,
                'template_id' => $id,
                'current_group' => $request->input('current_group'),
                'current_value' => $request->input('current_value')
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to find next parameter: ' . $e->getMessage()
            ], 500);
        }
    }
} 