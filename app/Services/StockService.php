<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\StockUnavailableException;

class StockService
{
    /**
     * Handle purchase and storage
     * Records movement AND updates stocks table.
     */
    public function handlePurchaseReceived(Purchase $purchase, Warehouse $warehouse): void
    {
        DB::transaction(function () use ($purchase, $warehouse) {
            foreach ($purchase->items as $item) {
                $product = $item->product; // Get product from item relation
                $increaseQuantity = (float)$item->quantity;
                $unitCostFloat = (float)$item->unit_price;
                $totalCostFloat = (float)$item->total_amount;

                // 1. Record Movement
                $this->createStockMovement(
                    product: $product,
                    warehouse: $warehouse,
                    referenceType: 'purchase_received',
                    referenceId: $purchase->id,
                    movementType: 'purchase_received',
                    quantity: $increaseQuantity,
                    unitCost: $unitCostFloat,
                    totalCost: $totalCostFloat,
                    batchNumber: null,
                    notes: "Purchase Order {$purchase->purchase_number} Into the warehouse"
                );

                // 2. Update Stocks Table (Assuming warehouse_id maps to store_id)
                Stock::updateOrCreate(
                    ['product_id' => $product->id, 'store_id' => $warehouse->id], // Find by product and store/warehouse
                    ['quantity' => DB::raw("quantity + {$increaseQuantity}")]  // Increment quantity
                );
            }
             Log::info('StockService::handlePurchaseReceived - Success', ['purchase_id' => $purchase->id, 'warehouse_id' => $warehouse->id]);
        });
    }

    /**
     * Handle inventory adjustments
     * Records movement AND updates stocks table.
     */
    public function handleStockAdjustment(
        Warehouse $warehouse,
        Carbon $adjustmentDate,
        string $adjustmentType, // 'increase' or 'decrease'
        string $reason,
        array $items
    ): void {
        DB::transaction(function () use ($warehouse, $adjustmentDate, $adjustmentType, $reason, $items) {
            $adjustment = DB::table('stock_adjustments')->insertGetId([
                'warehouse_id' => $warehouse->id,
                'adjustment_date' => $adjustmentDate,
                'adjustment_type' => $adjustmentType,
                'reason' => $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $adjustmentQuantity = (float)$item['quantity']; // Absolute quantity for adjustment
                $unitCostFloat = (float)$item['unit_cost'];

                // Determine movement quantity (+ or -) and type
                $movementQuantity = ($adjustmentType === 'decrease') ? -$adjustmentQuantity : $adjustmentQuantity;
                $movementType = ($adjustmentType === 'decrease') ? 'adjustment_out' : 'adjustment_in';
                
                 // *** Add stock check for decrease adjustments ***
                 if ($adjustmentType === 'decrease') {
                     // Cast quantity to int before passing to checkStock
                     $this->checkStock($product->id, $warehouse->id, (int)$adjustmentQuantity, true); // Throws if insufficient
                 }

                // 1. Record Movement
                $this->createStockMovement(
                    product: $product,
                    warehouse: $warehouse,
                    referenceType: 'stock_adjustment',
                    referenceId: $adjustment,
                    movementType: $movementType, 
                    quantity: $movementQuantity,
                    unitCost: $unitCostFloat,
                    totalCost: $unitCostFloat * $movementQuantity,
                    batchNumber: $item['batch_number'] ?? null,
                    notes: $item['notes'] ?? $reason // Use item notes or adjustment reason
                );

                // 2. Update Stocks Table (Assuming warehouse_id maps to store_id)
                if ($adjustmentType === 'decrease') {
                    Stock::where('product_id', $product->id)
                         ->where('store_id', $warehouse->id)
                         ->decrement('quantity', $adjustmentQuantity);
                } else { // 'increase'
                    Stock::updateOrCreate(
                        ['product_id' => $product->id, 'store_id' => $warehouse->id],
                        ['quantity' => DB::raw("quantity + {$adjustmentQuantity}")]
                    );
                }
            }
            Log::info('StockService::handleStockAdjustment - Success', ['adjustment_id' => $adjustment, 'warehouse_id' => $warehouse->id, 'type' => $adjustmentType]);
        });
    }

    /**
     * Handle inventory transfers
     * Records TWO movements AND updates stocks table for BOTH warehouses.
     */
    public function handleStockTransfer(
        Warehouse $sourceWarehouse,
        Warehouse $targetWarehouse,
        Carbon $transferDate,
        array $items
    ): void {
        DB::transaction(function () use ($sourceWarehouse, $targetWarehouse, $transferDate, $items) {
            $transfer = DB::table('stock_transfers')->insertGetId([
                'source_warehouse_id' => $sourceWarehouse->id,
                'target_warehouse_id' => $targetWarehouse->id,
                'transfer_date' => $transferDate,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $transferQuantity = (float)$item['quantity'];
                $batchNumber = $item['batch_number'] ?? null;
                $notes = $item['notes'] ?? null;
                
                // *** Check stock at source warehouse BEFORE transfer ***
                // Cast quantity to int before passing to checkStock
                $this->checkStock($product->id, $sourceWarehouse->id, (int)$transferQuantity, true); // Throws if insufficient

                // 1a. Deduct inventory from source warehouse (Movement)
                $this->createStockMovement(
                    product: $product,
                    warehouse: $sourceWarehouse,
                    referenceType: 'stock_transfer',
                    referenceId: $transfer,
                    movementType: 'stock_transfer_out',
                    quantity: -$transferQuantity,
                    unitCost: 0, // Cost is usually handled differently in transfers
                    totalCost: 0,
                    batchNumber: $batchNumber,
                    notes: $notes ?? "Transfer to warehouse {$targetWarehouse->name}"
                );
                
                // 1b. Update Source Stocks Table (Use store_id)
                Stock::where('product_id', $product->id)
                     ->where('store_id', $sourceWarehouse->id) // Correct column name
                     ->decrement('quantity', $transferQuantity);

                // 2a. Increase inventory in the target warehouse (Movement)
                $this->createStockMovement(
                    product: $product,
                    warehouse: $targetWarehouse,
                    referenceType: 'stock_transfer',
                    referenceId: $transfer,
                    movementType: 'stock_transfer_in',
                    quantity: $transferQuantity,
                    unitCost: 0,
                    totalCost: 0,
                    batchNumber: $batchNumber,
                    notes: $notes ?? "From the warehouse {$sourceWarehouse->name} Transfer to"
                );
                
                // 2b. Update Target Stocks Table (Use store_id)
                Stock::updateOrCreate(
                    ['product_id' => $product->id, 'store_id' => $targetWarehouse->id], // Correct column name
                    ['quantity' => DB::raw("quantity + {$transferQuantity}")]
                );
            }
            Log::info('StockService::handleStockTransfer - Success', ['transfer_id' => $transfer, 'source_id' => $sourceWarehouse->id, 'target_id' => $targetWarehouse->id]);
        });
    }

    /**
     * Create inventory movement records
     */
    public function createStockMovement(
        Product $product,
        Warehouse $warehouse,
        string $referenceType,
        int $referenceId,
        string $movementType,
        float $quantity,
        float $unitCost,
        float $totalCost,
        ?string $batchNumber = null,
        ?string $notes = null
    ): StockMovement {
        return StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'batch_number' => $batchNumber,
            'notes' => $notes,
        ]);
    }

    /**
     * Obtain the quantity of goods in stock for a specific warehouse or all warehouses
     * by reading directly from the stocks table.
     */
    public function getProductStock(Product $product, ?int $warehouseId = null): float
    {
        $query = Stock::where('product_id', $product->id);

        if ($warehouseId) {
            // Use store_id to query stocks table
            $query->where('store_id', $warehouseId); // Correct column name
            $stockRecord = $query->first();
            return (float)($stockRecord->quantity ?? 0.0);
        } else {
            return (float) $query->sum('quantity');
        }
    }

    /**
     * Obtain inventory movement records of goods
     */
    public function getProductStockMovements(
        Product $product,
        ?Warehouse $warehouse = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = StockMovement::with(['warehouse'])
            ->where('product_id', $product->id);

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get a list of inventory movement records
     */
    public function getStockMovements(
        ?string $type = null,
        ?Warehouse $warehouse = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = StockMovement::with(['product', 'warehouse']);

        if ($type) {
            $query->where('reference_type', $type);
        }

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get a single inventory movement record
     */
    public function getStockMovement(string $id): ?StockMovement
    {
        return StockMovement::with(['product', 'warehouse'])
            ->find($id);
    }

    /**
     * Check if there is sufficient inventory of goods in a specific warehouse
     * by reading directly from the stocks table.
     * Throws InsufficientStockException if stock is not enough (optional behavior).
     */
    public function checkStock(int $productId, int $warehouseId, int $quantity, bool $throwException = false): bool
    {
        // Use store_id to query stocks table
        $currentStock = Stock::where('product_id', $productId)
                             ->where('store_id', $warehouseId) // Correct column name
                             ->value('quantity'); 

        $hasEnough = ($currentStock !== null) && ($currentStock >= $quantity);

        if (!$hasEnough && $throwException) {
            $product = Product::find($productId); // Find product only if needed for exception message
            $productName = $product ? $product->name : 'Unknown Product';
            // Consider using the custom StockUnavailableException if defined
            throw new InsufficientStockException("Insufficient stock for product {$productName} (ID: {$productId}) in store {$warehouseId}. Required: {$quantity}, Available: " . ($currentStock ?? 0)); // Updated message
        }

        Log::info('StockService::checkStock result (from stocks table)', [
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'required_quantity' => $quantity,
            'current_stock' => $currentStock ?? 0,
            'has_enough' => $hasEnough
        ]);

        return $hasEnough;
    }

    /**
     * Get the total number of items in stock in the store
     */
    public function getStoreProductCount(int $storeId): int
    {
        return Stock::where('store_id', $storeId)
            ->where('quantity', '>', 0)
            ->distinct('product_id')
            ->count('product_id');
    }

    /**
     * Get the inventory quantity of goods in the designated store
     */
    public function getProductStockInStore(Product $product, int $storeId): int
    {
        return Stock::where('store_id', $storeId)
            ->where('product_id', $product->id)
            ->sum('quantity');
    }

    /**
     * 创建简化版的库存移动记录
     * Records movement AND updates stocks table based on movementType.
     * Assumes movementType indicates direction e.g., 'manual_increase', 'manual_decrease'.
     */
    public function createSimpleStockMovement(
        int $productId,
        int $warehouseId,
        string $movementType, // e.g., 'manual_increase', 'manual_decrease'
        float $quantity,
        string $notes,
        int $userId // Used as referenceId for 'manual_adjustment'
    ): StockMovement
    {
        $product = Product::findOrFail($productId);
        $warehouse = Warehouse::findOrFail($warehouseId);
        $adjustmentQuantity = abs($quantity); // Use absolute value for stock update
        $movementQuantity = (str_contains($movementType, 'decrease')) ? -$adjustmentQuantity : $adjustmentQuantity;
        $referenceType = 'manual_adjustment'; // Keep reference type generic

        DB::beginTransaction();
        try {
            // *** Add stock check for decrease ***
            if (str_contains($movementType, 'decrease')) {
                 // Use store_id in checkStock (implicitly handled by checkStock update)
                $this->checkStock($productId, $warehouseId, (int)$adjustmentQuantity, true); 
            }
            
            // 1. Record Movement
            $movement = $this->createStockMovement(
            product: $product,
            warehouse: $warehouse,
                referenceType: $referenceType, 
                referenceId: $userId, // Use userId as reference
            movementType: $movementType,
                quantity: $movementQuantity, // Use signed quantity for movement
                unitCost: (float)($product->cost_price ?? 0.0),
                totalCost: (float)($product->cost_price ?? 0.0) * $movementQuantity,
                batchNumber: null, // No batch number in simple movement
            notes: $notes
            );
            
            // 2. Update Stocks Table (Use store_id)
            if (str_contains($movementType, 'decrease')) {
                Stock::where('product_id', $productId)
                     ->where('store_id', $warehouseId) // Correct column name
                     ->decrement('quantity', $adjustmentQuantity);
            } else { // Assume increase for other types
                Stock::updateOrCreate(
                    ['product_id' => $productId, 'store_id' => $warehouseId], // Correct column name
                    ['quantity' => DB::raw("quantity + {$adjustmentQuantity}")]
                );
            }

            DB::commit();
            Log::info('StockService::createSimpleStockMovement - Success', [/* context */]);
            return $movement; // Return the created movement record

        } catch (InsufficientStockException $e) {
            DB::rollBack();
            Log::warning('StockService::createSimpleStockMovement - Insufficient stock', ['error' => $e->getMessage(), /* context */]);
            throw $e; // Re-throw
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StockService::createSimpleStockMovement - Failed', [
                 'error' => $e->getMessage(),
                 'trace' => $e->getTraceAsString(),
                 /* context */
            ]);
            throw $e; // Re-throw generic exception
        }
    }

    /**
     * Decrease stock for a product.
     * Creates a movement log AND updates the stocks table.
     *
     * @param int $productId
     * @param int $warehouseId The warehouse to decrease stock from.
     * @param int $quantity The positive quantity to decrease.
     * @param string $reason ('sales', 'adjustment', etc.)
     * @param int|null $referenceId (e.g., SalesOrder ID)
     * @return bool
     * @throws InsufficientStockException If stock is not enough before decreasing.
     */
    public function decreaseStock(int $productId, int $warehouseId, int $quantity, string $reason = 'sales', ?int $referenceId = null): bool
    {
        $product = Product::find($productId);
        $warehouse = Warehouse::find($warehouseId);

        if (!$product || !$warehouse) {
            Log::error('StockService::decreaseStock - Product or Warehouse not found', [
            'product_id' => $productId, 
                'warehouse_id' => $warehouseId
            ]);
            return false; // Indicate failure
        }

        $decreaseQuantity = abs($quantity); // Ensure positive value for decrease amount

        // *** Check stock BEFORE attempting to decrease ***
        // Use store_id in checkStock (implicitly handled by checkStock update)
        $this->checkStock($productId, $warehouseId, $decreaseQuantity, true); 

        $movementType = match ($reason) {
            'sales' => 'sales_order_fulfillment',
            'adjustment' => 'adjustment_out',
            'transfer' => 'stock_transfer_out',
            default => 'unknown_decrease'
        };

        DB::beginTransaction();
        try {
            // 1. Log the movement
            $unitCostFloat = (float)($product->cost_price ?? 0.0);
            $totalCostFloat = $unitCostFloat * -$decreaseQuantity;
            $this->createStockMovement(
                product: $product,
                warehouse: $warehouse,
                referenceType: $reason,
                referenceId: $referenceId ?? 0, 
                movementType: $movementType,
                quantity: (float)-$decreaseQuantity, 
                unitCost: $unitCostFloat, 
                totalCost: $totalCostFloat, 
                batchNumber: null, 
                notes: ucfirst($reason) . ($referenceId ? " #{$referenceId}" : '')
            );

            // 2. Update the stocks table (Use store_id)
            Stock::where('product_id', $productId)
                 ->where('store_id', $warehouseId) // Correct column name
                 ->decrement('quantity', $decreaseQuantity);
            
            // Optional: Verify stock didn't go below zero if decrement doesn't prevent it
            // $newStock = Stock::where...value('quantity'); if ($newStock < 0) throw... 

            DB::commit();
            Log::info('StockService::decreaseStock - Success', [/* context */]);
            return true;

        } catch (InsufficientStockException $e) {
            // Catch the specific exception from checkStock
            DB::rollBack();
            Log::warning('StockService::decreaseStock - Insufficient stock', ['error' => $e->getMessage(), /* context */]);
            throw $e; // Re-throw for the caller (e.g., CheckoutController) to handle
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StockService::decreaseStock - Failed to decrease stock', [
                 'error' => $e->getMessage(),
                 'trace' => $e->getTraceAsString(),
                 /* context */
            ]);
            // Depending on policy, you might want to throw a generic exception here
            // throw new \Exception("Failed to decrease stock.", 0, $e);
            return false; // Indicate failure
        }
    }

    /**
     * Get current stock for a product across all warehouses by reading stocks table.
     *
     * @param int $productId
     * @return int
     */
    public function getStock(int $productId): int
    {
        // Read sum directly from the stocks table
        $totalStock = Stock::where('product_id', $productId)->sum('quantity');
        Log::info('StockService::getStock - Fetched stock from stocks table', ['product_id' => $productId, 'total_stock' => $totalStock]);
        return (int) $totalStock; // Cast to int as per original signature
    }

    /**
     * Check stock availability for a specific product in a specific warehouse
     * by reading directly from the stocks table.
     *
     * @param int $productId
     * @param int $quantity The quantity required (positive integer).
     * @param int $warehouseId The ID of the warehouse to check.
     * @return bool True if stock is sufficient, false otherwise.
     */
    public function checkStockAvailabilityAtWarehouse(int $productId, int $quantity, int $warehouseId): bool
    {
        // Uses the updated checkStock which now uses store_id
        try {
            return $this->checkStock($productId, $warehouseId, $quantity, false); 
        } catch (\Exception $e) {
            // Should not happen if throwException is false, but good practice
            Log::error('StockService::checkStockAvailabilityAtWarehouse - Unexpected error during checkStock call', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Increase stock for a product.
     * Creates a movement log AND updates the stocks table.
     *
     * @param int $productId
     * @param int $warehouseId The warehouse to increase stock in.
     * @param int $quantity The positive quantity to increase.
     * @param string $reason ('return', 'adjustment', 'purchase', etc.)
     * @param int|null $referenceId (e.g., Return ID, Purchase Order ID)
     * @return bool
     */
    public function increaseStock(int $productId, int $warehouseId, int $quantity, string $reason = 'return', ?int $referenceId = null): bool
    {
        $product = Product::find($productId);
        $warehouse = Warehouse::find($warehouseId);

        if (!$product || !$warehouse) {
            Log::error('StockService::increaseStock - Product or Warehouse not found', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId
            ]);
            return false; // Indicate failure
        }

        $increaseQuantity = abs($quantity);
        $movementType = match ($reason) {
            'return' => 'sales_return',
            'adjustment' => 'adjustment_in',
            'purchase' => 'purchase_received',
            'transfer' => 'stock_transfer_in',
            default => 'unknown_increase'
        };

        DB::beginTransaction();
        try {
            // 1. Log the movement
            $unitCostFloat = (float)($product->cost_price ?? 0.0);
            $totalCostFloat = $unitCostFloat * $increaseQuantity;
            $this->createStockMovement(
                product: $product,
                warehouse: $warehouse,
                referenceType: $reason,
                referenceId: $referenceId ?? 0, 
                movementType: $movementType,
                quantity: (float)$increaseQuantity,
                unitCost: $unitCostFloat,
                totalCost: $totalCostFloat, 
                batchNumber: null, 
                notes: ucfirst($reason) . ($referenceId ? " #{$referenceId}" : '')
            );

            // 2. Update the stocks table (Use store_id)
            Stock::updateOrCreate(
                ['product_id' => $productId, 'store_id' => $warehouseId], // Correct column name
                ['quantity' => DB::raw("quantity + {$increaseQuantity}")]
            );

            DB::commit();
            Log::info('StockService::increaseStock - Success', [/* context */]);
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StockService::increaseStock - Failed to increase stock', [
                 'error' => $e->getMessage(),
                 'trace' => $e->getTraceAsString(),
                 /* context */
            ]);
            return false; // Indicate failure
        }
    }
} 