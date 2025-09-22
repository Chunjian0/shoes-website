<?php

declare(strict_types=1);

namespace App\Scripts;

use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\SupplierPriceAgreement;
use Illuminate\Support\Facades\DB;

/**
 * 供应商数据库检查工具类
 */
class SupplierDatabaseCheck
{
    /**
     * 检查供应商是否存在
     * 
     * @param array $criteria 查询条件
     * @return bool 是否存在
     */
    public static function supplierExists(array $criteria): bool
    {
        $query = Supplier::query();
        
        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->exists();
    }
    
    /**
     * 检查供应商是否已删除
     * 
     * @param array $criteria 查询条件
     * @return bool 是否已删除
     */
    public static function supplierDeleted(array $criteria): bool
    {
        $query = Supplier::onlyTrashed();
        
        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->exists();
    }
    
    /**
     * 获取供应商详情
     * 
     * @param array $criteria 查询条件
     * @return Supplier|null 供应商对象
     */
    public static function getSupplierDetails(array $criteria): ?Supplier
    {
        $query = Supplier::query();
        
        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->first();
    }
    
    /**
     * 检查供应商产品关联是否存在
     * 
     * @param array $criteria 查询条件
     * @return bool 是否存在
     */
    public static function supplierProductExists(array $criteria): bool
    {
        $query = SupplierProduct::query();
        
        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->exists();
    }
    
    /**
     * 获取供应商产品关联详情
     * 
     * @param array $criteria 查询条件
     * @return SupplierProduct|null 供应商产品对象
     */
    public static function getSupplierProductDetails(array $criteria): ?SupplierProduct
    {
        $query = SupplierProduct::query();
        
        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->first();
    }
    
    /**
     * 检查价格协议是否存在
     * 
     * @param array $criteria 查询条件
     * @return bool 是否存在
     */
    public static function priceAgreementExists(array $criteria): bool
    {
        $query = SupplierPriceAgreement::query();
        
        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->exists();
    }
    
    /**
     * 清理测试供应商
     * 
     * @param array $criteria 查询条件
     * @return bool 清理是否成功
     */
    public static function cleanupTestSupplier(array $criteria): bool
    {
        try {
            $query = Supplier::query();
            
            foreach ($criteria as $field => $value) {
                $query->where($field, $value);
            }
            
            $supplier = $query->first();
            
            if ($supplier) {
                // 级联删除相关记录
                $supplier->contacts()->delete();
                
                // 查找供应商产品
                $supplierProducts = SupplierProduct::where('supplier_id', $supplier->id)->get();
                
                foreach ($supplierProducts as $supplierProduct) {
                    // 删除价格协议
                    SupplierPriceAgreement::where('supplier_product_id', $supplierProduct->id)->delete();
                    // 删除供应商产品
                    $supplierProduct->delete();
                }
                
                // 删除供应商
                $supplier->delete();
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
} 