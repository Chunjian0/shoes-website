<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * 产品类别API适配器控制器
 * 
 * 该控制器的目的是将ProductCategoryController的响应格式转换为与前端期望的格式一致
 * 前端期望格式: { "success": true, "categories": [...] }
 * 后端实际格式: { "status": "success", "data": { "categories": [...] } }
 */
class CategoryApiAdapter extends Controller
{
    protected $categoryController;
    
    public function __construct(ProductCategoryController $categoryController)
    {
        $this->categoryController = $categoryController;
    }
    
    /**
     * 获取所有产品类别
     */
    public function index(Request $request): JsonResponse
    {
        // 调用原始控制器方法
        $response = $this->categoryController->index($request);
        
        // 获取原始数据
        $originalData = json_decode($response->getContent(), true);
        
        // 记录原始数据结构
        Log::debug('Original categories response', [
            'structure' => $originalData
        ]);
        
        // 如果原始请求成功
        if (isset($originalData['status']) && $originalData['status'] === 'success') {
            // 提取类别数据
            $categories = $originalData['data']['categories'] ?? [];
            
            // 转换为前端期望的格式
            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);
        }
        
        // 转换错误响应
        return response()->json([
            'success' => false,
            'message' => $originalData['message'] ?? 'Failed to get categories'
        ], $response->getStatusCode());
    }
    
    /**
     * 获取单个产品类别
     */
    public function show(Request $request, $id): JsonResponse
    {
        // 调用原始控制器方法
        $response = $this->categoryController->show($id);
        
        // 获取原始数据
        $originalData = json_decode($response->getContent(), true);
        
        // 记录原始数据结构
        Log::debug('Original category detail response', [
            'category_id' => $id,
            'structure' => $originalData
        ]);
        
        // 如果原始请求成功
        if (isset($originalData['status']) && $originalData['status'] === 'success') {
            // 提取类别数据
            $category = $originalData['data']['category'] ?? null;
            
            // 转换为前端期望的格式
            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        }
        
        // 转换错误响应
        return response()->json([
            'success' => false,
            'message' => $originalData['message'] ?? 'Category not found'
        ], $response->getStatusCode());
    }
} 