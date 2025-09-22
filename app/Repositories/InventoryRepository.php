<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Events\StockChangedEvent;
use App\Models\InventoryRecord;
use App\Models\Product;
use App\Models\InventoryCheck;
use App\Models\InventoryLoss;
use App\Models\Stock;
use App\Services\InventoryMailService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryRepository extends BaseRepository
{
    private InventoryMailService $mailService;

    public function __construct(InventoryMailService $mailService)
    {
        $this->mailService = $mailService;
        parent::__construct();
    }

    protected function getModelClass(): Model
    {
        return new InventoryRecord();
    }

    // Update product inventory
    public function updateStock(Product $product, int $quantity, string $type = 'in', array $data = []): bool
    {
        try {
            DB::beginTransaction();

            // Create inventory records
            $inventory = InventoryRecord::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'type' => $type,
                'batch_number' => $data['batch_number'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'location' => $data['location'] ?? null,
                'status' => 'available',
                'additional_data' => $data['additional_data'] ?? null,
            ]);

            // Update the total inventory of goods
            $oldCount = $product->inventory_count;
            $newCount = match($type) {
                'in' => $product->inventory_count + $quantity,
                'out' => $product->inventory_count - $quantity,
                default => $product->inventory_count,
            };

            $product->update(['inventory_count' => $newCount]);

            // 触发库存变更事件
            $changeType = ($type == 'in') ? 'increase' : 'decrease';
            
            // 找到该产品的库存记录或创建一个临时记录用于事件
            $stockEntry = Stock::where('product_id', $product->id)->first();
            if ($stockEntry) {
                event(new StockChangedEvent($stockEntry, $oldCount, $newCount, $changeType));
            }

            // Check whether the product needs to be automatically disabled
            if ($newCount <= 0) {
                $product->update(['is_active' => false]);
                $this->mailService->sendOutOfStockNotification($product);
            } elseif ($newCount <= $product->min_stock) {
                $this->mailService->sendLowStockNotification($product);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update inventory:' . $e->getMessage());
            throw $e;
        }
    }

    // Create inventory orders
    public function createInventoryCheck(array $data): InventoryCheck
    {
        try {
            DB::beginTransaction();

            // Create an inventory list
            $check = InventoryCheck::create([
                'check_number' => InventoryCheck::generateCheckNumber(),
                'check_date' => $data['check_date'],
                'user_id' => $data['user_id'],
                'status' => InventoryCheck::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create an inventory details
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $check->items()->create([
                    'product_id' => $item['product_id'],
                    'system_count' => $product->inventory_count,
                    'actual_count' => $item['actual_count'],
                    'difference' => $item['actual_count'] - $product->inventory_count,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();
            return $check;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create inventory orders:' . $e->getMessage());
            throw $e;
        }
    }

    // Complete inventory points
    public function completeInventoryCheck(InventoryCheck $check): bool
    {
        try {
            DB::beginTransaction();

            // Update the actual inventory of all items
            foreach ($check->items as $item) {
                $item->product->update([
                    'inventory_count' => $item->actual_count
                ]);

                // If the inventory is0, automatically disable products
                if ($item->actual_count <= 0) {
                    $item->product->update(['is_active' => false]);
                    $this->mailService->sendOutOfStockNotification($item->product);
                } elseif ($item->actual_count <= $item->product->min_stock) {
                    $this->mailService->sendLowStockNotification($item->product);
                }
            }

            // Update the inventory status
            $check->update(['status' => InventoryCheck::STATUS_COMPLETED]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete inventory count:' . $e->getMessage());
            throw $e;
        }
    }

    // Create inventory loss orders
    public function createInventoryLoss(array $data): InventoryLoss
    {
        try {
            DB::beginTransaction();

            // Create a loss statement
            $loss = InventoryLoss::create([
                'loss_number' => InventoryLoss::generateLossNumber(),
                'loss_date' => $data['loss_date'],
                'user_id' => $data['user_id'],
                'status' => InventoryLoss::STATUS_PENDING,
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Create a loss report details
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $loss->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $product->cost_price,
                    'total_amount' => $product->cost_price * $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();
            return $loss;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create an inventory loss order:' . $e->getMessage());
            throw $e;
        }
    }

    // Approve inventory loss statement
    public function approveInventoryLoss(InventoryLoss $loss, int $userId, bool $isApproved): bool
    {
        try {
            DB::beginTransaction();

            if ($isApproved) {
                // Update inventory of all items
                foreach ($loss->items as $item) {
                    $this->updateStock(
                        $item->product,
                        $item->quantity,
                        'out',
                        ['additional_data' => ['loss_number' => $loss->loss_number]]
                    );
                }

                // Update the status of the loss order
                $loss->update([
                    'status' => InventoryLoss::STATUS_APPROVED,
                    'approved_by' => $userId,
                    'approved_at' => now(),
                ]);
            } else {
                $loss->update([
                    'status' => InventoryLoss::STATUS_REJECTED,
                    'approved_by' => $userId,
                    'approved_at' => now(),
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve inventory loss statement:' . $e->getMessage());
            throw $e;
        }
    }

    // Obtain inventory warning products
    public function getLowStockProducts()
    {
        return Product::where('inventory_count', '<=', DB::raw('min_stock'))
            ->where('is_active', true)
            ->with('category')
            ->get();
    }

    // Getting stock exhausted items
    public function getOutOfStockProducts()
    {
        return Product::where('inventory_count', '<=', 0)
            ->with('category')
            ->get();
    }

    // Obtain the history of product inventory changes
    public function getProductInventoryHistory(Product $product)
    {
        return $this->model->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
} 