<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * 产品API适配器控制器
 * 
 * 该控制器的目的是将ProductController的响应格式转换为与前端期望的格式一致
 * 前端期望格式: { "success": true, "products": [...] }
 * 后端实际格式: { "status": "success", "data": { "products": [...], "pagination": {...} } }
 */
class ProductApiAdapter extends Controller
{
    protected $productController;
    
    public function __construct(ProductController $productController)
    {
        $this->productController = $productController;
    }
    
    /**
     * 获取精选产品
     */
    public function getFeaturedProducts(Request $request): JsonResponse
    {
        // 调用原始控制器方法
        $response = $this->productController->getFeaturedProducts($request);
        
        // 获取原始数据
        $originalData = json_decode($response->getContent(), true);
        
        // 记录原始数据结构
        Log::debug('Original featured products response', [
            'structure' => $originalData
        ]);
        
        // 如果原始请求成功
        if (isset($originalData['status']) && $originalData['status'] === 'success') {
            // 提取产品数据
            $products = $originalData['data']['products'] ?? [];
            
            // 转换为前端期望的格式
            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        }
        
        // 转换错误响应
        return response()->json([
            'success' => false,
            'message' => $originalData['message'] ?? 'Failed to get featured products'
        ], $response->getStatusCode());
    }
    
    /**
     * 获取新品
     */
    public function getNewArrivals(Request $request): JsonResponse
    {
        // 调用原始控制器方法
        $response = $this->productController->getNewArrivals($request);
        
        // 获取原始数据
        $originalData = json_decode($response->getContent(), true);
        
        // 记录原始数据结构
        Log::debug('Original new arrivals response', [
            'structure' => $originalData
        ]);
        
        // 如果原始请求成功
        if (isset($originalData['status']) && $originalData['status'] === 'success') {
            // 提取产品数据
            $products = $originalData['data']['products'] ?? [];
            
            // 转换为前端期望的格式
            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        }
        
        // 转换错误响应
        return response()->json([
            'success' => false,
            'message' => $originalData['message'] ?? 'Failed to get new arrivals'
        ], $response->getStatusCode());
    }
    
    /**
     * 获取促销产品
     */
    public function getPromotionProducts(Request $request): JsonResponse
    {
        // 调用原始控制器方法
        $response = $this->productController->getPromotionProducts($request);
        
        // 获取原始数据
        $originalData = json_decode($response->getContent(), true);
        
        // 记录原始数据结构
        Log::debug('Original promotion products response', [
            'structure' => $originalData
        ]);
        
        // 如果原始请求成功
        if (isset($originalData['status']) && $originalData['status'] === 'success') {
            // 提取产品数据
            $products = $originalData['data']['products'] ?? [];
            
            // 转换为前端期望的格式
            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        }
        
        // 转换错误响应
        return response()->json([
            'success' => false,
            'message' => $originalData['message'] ?? 'Failed to get promotion products'
        ], $response->getStatusCode());
    }
} 