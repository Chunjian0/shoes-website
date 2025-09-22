<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Increase inventory
     *
     * @param int $productId merchandise ID
     * @param int $quantity quantity
     * @param string $description describe
     * @param int|null $warehouseId 仓库ID
     * @throws \Exception
     */
    public function increaseStock(int $productId, int $quantity, string $description, ?int $warehouseId = null): void
    {
        Log::info('Start increasing inventory', [
            'product_id' => $productId,
            'quantity' => $quantity,
            'description' => $description,
            'warehouse_id' => $warehouseId
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($productId);
            
            // 获取仓库信息，确认是否为门店
            $warehouse = null;
            if ($warehouseId) {
                $warehouse = \App\Models\Warehouse::find($warehouseId);
                Log::info('Warehouse information', [
                    'warehouse_id' => $warehouseId,
                    'warehouse_name' => $warehouse->name ?? 'Unknown',
                    'is_store' => $warehouse->is_store ?? false
                ]);
            }
            
            // 获取或创建库存记录
            // 注意：stock 表使用 store_id 字段，该字段引用的是 warehouses 表中的 ID
            $stock = $product->stocks()
                ->where('store_id', $warehouseId)
                ->first();
                
            if (!$stock) {
                Log::info('Creating new stock record', [
                    'product_id' => $productId,
                    'store_id' => $warehouseId
                ]);
                
                $stock = $product->stocks()->create([
                    'store_id' => $warehouseId,
                    'quantity' => 0
                ]);
            }
            
            // 记录原始库存
            $beforeQuantity = $stock->quantity;
            
            // 更新库存数量
            $stock->quantity += $quantity;
            $stock->save();

            Log::info('Stock updated', [
                'product_id' => $productId,
                'store_id' => $warehouseId,
                'before_quantity' => $beforeQuantity,
                'new_quantity' => $stock->quantity
            ]);

            // 记录库存变动
            $stockMovement = StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'reference_type' => 'quality_inspection',
                'reference_id' => $product->id,
                'movement_type' => 'increase',
                'quantity' => $quantity,
                'unit_cost' => $product->cost_price ?? 0,
                'total_cost' => ($product->cost_price ?? 0) * $quantity,
                'notes' => $description,
            ]);

            Log::info('Stock movement created', [
                'stock_movement_id' => $stockMovement->id,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity
            ]);

            Log::info('Inventory increase completed', [
                'product_id' => $productId,
                'store_id' => $warehouseId,
                'before_quantity' => $beforeQuantity,
                'after_quantity' => $stock->quantity,
                'increased_by' => $quantity
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory increase failed', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Reduce inventory
     *
     * @param int $productId merchandiseID
     * @param int $quantity quantity
     * @param string $description describe
     * @param int|null $warehouseId 仓库ID
     * @throws \Exception
     */
    public function decreaseStock(int $productId, int $quantity, string $description, ?int $warehouseId = null): void
    {
        Log::info('Start reducing inventory', [
            'product_id' => $productId,
            'quantity' => $quantity,
            'description' => $description,
            'warehouse_id' => $warehouseId
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($productId);
            
            // 获取库存记录
            $stock = $product->stocks()
                ->where('store_id', $warehouseId)
                ->first();
                
            if (!$stock) {
                throw new \Exception("商品 {$product->name} 在指定仓库中没有库存记录");
            }
            
            // 检查库存是否充足
            if ($stock->quantity < $quantity) {
                throw new \Exception("商品 {$product->name} 库存不足");
            }
            
            // 记录原始库存
            $beforeQuantity = $stock->quantity;
            
            // 更新库存数量
            $stock->quantity -= $quantity;
            $stock->save();

            // 记录库存变动
            StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'reference_type' => 'quality_inspection',
                'reference_id' => $product->id,
                'movement_type' => 'decrease',
                'quantity' => $quantity,
                'unit_cost' => $product->cost_price ?? 0,
                'total_cost' => ($product->cost_price ?? 0) * $quantity,
                'notes' => $description,
            ]);

            Log::info('Inventory reduction completed', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'before_quantity' => $beforeQuantity,
                'after_quantity' => $stock->quantity,
                'decreased_by' => $quantity
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory reduction failed', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Check if the inventory is sufficient
     *
     * @param int $productId merchandiseID
     * @param int $quantity The quantity required
     * @param int|null $warehouseId 仓库ID
     * @return bool
     */
    public function checkStock(int $productId, int $quantity, ?int $warehouseId = null): bool
    {
        $product = Product::find($productId);
        if (!$product) {
            return false;
        }
        
        $query = $product->stocks();
        if ($warehouseId !== null) {
            $query->where('store_id', $warehouseId);
        }
        
        $stock = $query->first();
        return $stock && $stock->quantity >= $quantity;
    }

    /**
     * Obtain the current inventory of the product
     *
     * @param int $productId merchandiseID
     * @param int|null $warehouseId 仓库ID
     * @return int
     */
    public function getCurrentStock(int $productId, ?int $warehouseId = null): int
    {
        $product = Product::find($productId);
        if (!$product) {
            return 0;
        }
        
        $query = $product->stocks();
        if ($warehouseId !== null) {
            $query->where('store_id', $warehouseId);
        }
        
        $stock = $query->first();
        return $stock ? $stock->quantity : 0;
    }

    /**
     * Obtain the history of product inventory changes
     *
     * @param int $productId merchandiseID
     * @param int $limit Limit quantity
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStockMovements(int $productId, int $limit = 10)
    {
        return StockMovement::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
} 