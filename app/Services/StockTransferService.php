<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use App\Mail\LowStockNotification;
use Illuminate\Support\Facades\Mail;

class StockTransferService
{
    /**
     * Create inventory transfer records
     */
    public function createTransfer(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            // Create a transfer record
            $transfer = StockTransfer::create([
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'transfer_date' => $data['transfer_date'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // Create a transfer detail
            foreach ($data['items'] as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $transfer;
        });
    }

    /**
     * Confirm inventory transfer
     */
    public function confirmTransfer(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // Reduce source warehouse inventory
                StockMovement::create([
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'product_id' => $item->product_id,
                    'type' => 'transfer_out',
                    'quantity' => -$item->quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                ]);

                // Increase target warehouse inventory
                StockMovement::create([
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'product_id' => $item->product_id,
                    'type' => 'transfer_in',
                    'quantity' => $item->quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                ]);
            }

            $transfer->update(['status' => 'completed']);
        });
    }

    /**
     * Check inventory and send notifications if needed
     */
    public function checkAndNotifyLowStock(Product $product, Warehouse $warehouse): void
    {
        // Get current inventory
        $currentStock = $product->getStockInWarehouse($warehouse->id);

        // If it is below the minimum inventory
        if ($currentStock <= $product->min_stock) {
            // Check if there is enough inventory in other warehouses
            $otherWarehouses = Warehouse::where('id', '!=', $warehouse->id)
                ->where('status', true)
                ->get();

            $availableStock = false;
            foreach ($otherWarehouses as $otherWarehouse) {
                if ($product->getStockInWarehouse($otherWarehouse->id) > 0) {
                    $availableStock = true;
                    break;
                }
            }

            // If other warehouses don't have inventory,Send email notification
            if (!$availableStock) {
                $this->sendLowStockNotification($product, $warehouse);
            }
        }
    }

    /**
     * Send notice of insufficient inventory
     */
    private function sendLowStockNotification(Product $product, Warehouse $warehouse): void
    {
        $adminEmails = config('mail.stock_notification_recipients', []);
        
        if (!empty($adminEmails)) {
            Mail::to($adminEmails)->send(new LowStockNotification($product, $warehouse));
        }
    }
} 