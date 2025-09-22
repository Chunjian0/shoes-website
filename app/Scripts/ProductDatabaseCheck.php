<?php

namespace App\Scripts;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 产品数据库检查工具类
 * 用于测试中验证产品是否正确创建/更新/删除
 */
class ProductDatabaseCheck
{
    /**
     * 验证产品是否成功创建
     *
     * @param array $criteria 搜索条件
     * @return bool
     */
    public static function productExists(array $criteria): bool
    {
        try {
            $query = DB::table('products');
            
            foreach ($criteria as $key => $value) {
                $query->where($key, $value);
            }
            
            return $query->whereNull('deleted_at')->exists();
        } catch (\Exception $e) {
            Log::error('验证产品存在失败', [
                'error' => $e->getMessage(),
                'criteria' => $criteria
            ]);
            return false;
        }
    }
    
    /**
     * 验证产品是否已被删除（软删除）
     *
     * @param array $criteria 搜索条件
     * @return bool
     */
    public static function productDeleted(array $criteria): bool
    {
        try {
            $query = DB::table('products');
            
            foreach ($criteria as $key => $value) {
                $query->where($key, $value);
            }
            
            return $query->whereNotNull('deleted_at')->exists();
        } catch (\Exception $e) {
            Log::error('验证产品删除失败', [
                'error' => $e->getMessage(),
                'criteria' => $criteria
            ]);
            return false;
        }
    }
    
    /**
     * 获取产品详情
     *
     * @param array $criteria 搜索条件
     * @return object|null
     */
    public static function getProductDetails(array $criteria)
    {
        try {
            $query = DB::table('products');
            
            foreach ($criteria as $key => $value) {
                $query->where($key, $value);
            }
            
            return $query->whereNull('deleted_at')->first();
        } catch (\Exception $e) {
            Log::error('获取产品详情失败', [
                'error' => $e->getMessage(),
                'criteria' => $criteria
            ]);
            return null;
        }
    }
    
    /**
     * 验证产品属性是否符合预期
     *
     * @param int $productId 产品ID
     * @param array $expectedValues 预期值
     * @return bool
     */
    public static function validateProductAttributes(int $productId, array $expectedValues): bool
    {
        try {
            $product = DB::table('products')->where('id', $productId)->whereNull('deleted_at')->first();
            
            if (!$product) {
                return false;
            }
            
            foreach ($expectedValues as $attribute => $expectedValue) {
                if (!property_exists($product, $attribute) || $product->$attribute != $expectedValue) {
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('验证产品属性失败', [
                'product_id' => $productId,
                'expected_values' => $expectedValues,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 删除测试产品
     * 
     * @param array $criteria 搜索条件
     * @return bool
     */
    public static function cleanupTestProduct(array $criteria): bool
    {
        try {
            $query = DB::table('products');
            
            foreach ($criteria as $key => $value) {
                $query->where($key, $value);
            }
            
            // 只做软删除
            return $query->update(['deleted_at' => now()]) > 0;
        } catch (\Exception $e) {
            Log::error('清理测试产品失败', [
                'error' => $e->getMessage(),
                'criteria' => $criteria
            ]);
            return false;
        }
    }
}
