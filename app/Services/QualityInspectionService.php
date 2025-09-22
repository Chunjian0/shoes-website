<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\QualityInspection;
use App\Models\Purchase;
use App\Enums\QualityInspectionStatus;
use App\Enums\PurchaseInspectionStatus;
use App\Events\QualityInspectionCreated;
use App\Events\QualityInspectionStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Enums\PurchaseStatus;
use App\Models\Stock;
use App\Models\Warehouse;

class QualityInspectionService
{
    /**
     * Generate inspection number
     */
    private function generateInspectionNumber(): string
    {
        $today = Carbon::now();
        $prefix = 'QC' . $today->format('Ymd');
        
        // Get the last number today
        $lastNumber = QualityInspection::where('inspection_number', 'like', $prefix . '%')
            ->orderBy('inspection_number', 'desc')
            ->value('inspection_number');
            
        if ($lastNumber) {
            $sequence = (int)substr($lastNumber, -4);
            $sequence++;
        } else {
            $sequence = 1;
        }
        
        return $prefix . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create quality inspection records
     */
    public function createInspection(array $data): QualityInspection
    {
        return DB::transaction(function () use ($data) {
            Log::info('Start a transaction: Create quality inspection', ['data' => $data]);

            // Verify the purchase order status
            $purchase = Purchase::findOrFail($data['purchase_id']);
            Log::info('Purchase Order Status Check', [
                'purchase_id' => $purchase->id,
                'current_status' => $purchase->purchase_status,
                'allowed_statuses' => [PurchaseStatus::RECEIVED, PurchaseStatus::PARTIALLY_RECEIVED]
            ]);

            if (!in_array($purchase->purchase_status, [PurchaseStatus::RECEIVED, PurchaseStatus::PARTIALLY_RECEIVED])) {
                Log::warning('Purchase order status does not meet the requirements', [
                    'purchase_id' => $purchase->id,
                    'current_status' => $purchase->purchase_status
                ]);
                throw new \RuntimeException('Quality inspection can be created only if the received purchase orders');
            }

            // Check whether it is a partial inspection
            $isPartialInspection = false;
            $purchaseItems = $purchase->items()->get();
            $inspectionItemIds = collect($data['items'])->pluck('purchase_item_id');
            
            // Check for uninspected products
            if ($purchaseItems->count() !== $inspectionItemIds->count()) {
                $isPartialInspection = true;
                Log::info('Partial inspection was detected: All products were not inspected', [
                    'total_items' => $purchaseItems->count(),
                    'inspected_items' => $inspectionItemIds->count()
                ]);
            }
            
            // Verification inspection quantity
            foreach ($data['items'] as $item) {
                $purchaseItem = $purchase->items()->findOrFail($item['purchase_item_id']);
                Log::info('Number of verification inspection items', [
                    'purchase_item_id' => $purchaseItem->id,
                    'product_name' => $purchaseItem->product->name,
                    'inspected_quantity' => $item['inspected_quantity'],
                    'purchase_quantity' => $purchaseItem->quantity,
                    'passed_quantity' => $item['passed_quantity'],
                    'failed_quantity' => $item['failed_quantity']
                ]);

                if ($item['inspected_quantity'] > $purchaseItem->quantity) {
                    Log::warning('The inspection quantity exceeds the purchase quantity', [
                        'purchase_item_id' => $purchaseItem->id,
                        'inspected_quantity' => $item['inspected_quantity'],
                        'purchase_quantity' => $purchaseItem->quantity
                    ]);
                    throw new \RuntimeException("The inspection quantity cannot be greater than the purchase quantity: {$purchaseItem->product->name}");
                }

                // Check whether it is a partial inspection (quantity)
                if ($item['inspected_quantity'] < $purchaseItem->quantity) {
                    $isPartialInspection = true;
                    Log::info('Partial inspection was detected: the inspection quantity is less than the purchase quantity', [
                        'product_name' => $purchaseItem->product->name,
                        'inspected_quantity' => $item['inspected_quantity'],
                        'purchase_quantity' => $purchaseItem->quantity
                    ]);
                }

                if ($item['passed_quantity'] + $item['failed_quantity'] != $item['inspected_quantity']) {
                    Log::warning('Quantity verification failed', [
                        'purchase_item_id' => $purchaseItem->id,
                        'passed_quantity' => $item['passed_quantity'],
                        'failed_quantity' => $item['failed_quantity'],
                        'inspected_quantity' => $item['inspected_quantity']
                    ]);
                    throw new \RuntimeException("The sum of the qualified quantity and the unqualified quantity must be equal to the inspection quantity: {$purchaseItem->product->name}");
                }
            }

            // Create quality inspection records
            $inspection = QualityInspection::create([
                'purchase_id' => $data['purchase_id'],
                'inspector_id' => Auth::id(),
                'inspection_number' => $this->generateInspectionNumber(),
                'inspection_date' => $data['inspection_date'],
                'status' => QualityInspectionStatus::PASSED,
                'remarks' => $data['remarks'] ?? null,
                'is_partial' => $isPartialInspection,
            ]);

            Log::info('Quality inspection record has been created', [
                'inspection_id' => $inspection->id,
                'inspection_number' => $inspection->inspection_number,
                'is_partial' => $isPartialInspection
            ]);

            // Create an inspection project
            foreach ($data['items'] as $item) {
                $inspectionItem = $inspection->items()->create([
                    'purchase_item_id' => $item['purchase_item_id'],
                    'inspected_quantity' => $item['inspected_quantity'],
                    'passed_quantity' => $item['passed_quantity'],
                    'failed_quantity' => $item['failed_quantity'],
                    'defect_description' => $item['defect_description'] ?? null,
                ]);

                Log::info('Inspection project has been created', [
                    'inspection_item_id' => $inspectionItem->id,
                    'purchase_item_id' => $item['purchase_item_id'],
                    'quantities' => [
                        'inspected' => $item['inspected_quantity'],
                        'passed' => $item['passed_quantity'],
                        'failed' => $item['failed_quantity']
                    ]
                ]);
            }

            // Update the inspection status of the purchase order
            $purchase->update([
                'inspection_status' => $isPartialInspection ? 
                    PurchaseInspectionStatus::PARTIALLY_INSPECTED : 
                    PurchaseInspectionStatus::IN_PROGRESS
            ]);
            
            Log::info('Purchase order inspection status has been updated', [
                'purchase_id' => $purchase->id,
                'new_status' => $isPartialInspection ? 'PARTIALLY_INSPECTED' : 'IN_PROGRESS'
            ]);
            
            // Trigger the creation event
            event(new QualityInspectionCreated($inspection));

            Log::info('Quality inspection creation is completed', [
                'inspection_id' => $inspection->id,
                'inspection_number' => $inspection->inspection_number
            ]);

            return $inspection;
        });
    }

    /**
     * Update quality inspection records
     */
    public function updateInspection(QualityInspection $inspection, array $data): void
    {
        if (!$inspection->isEditable()) {
            throw new \RuntimeException('The current status does not allow editing');
        }

        DB::transaction(function () use ($inspection, $data) {
            // Verification inspection quantity
            foreach ($data['items'] as $item) {
                $purchaseItem = $inspection->purchase->items()->findOrFail($item['purchase_item_id']);
                if ($item['inspected_quantity'] > $purchaseItem->quantity) {
                    throw new \RuntimeException("The inspection quantity cannot be greater than the purchase quantity: {$purchaseItem->product->name}");
                }
                if ($item['passed_quantity'] + $item['failed_quantity'] != $item['inspected_quantity']) {
                    throw new \RuntimeException("The sum of the qualified quantity and the unqualified quantity must be equal to the inspection quantity: {$purchaseItem->product->name}");
                }
            }

            // Update quality inspection records
            $inspection->update([
                'inspection_date' => $data['inspection_date'],
                'remarks' => $data['remarks'] ?? null,
            ]);

            // Update inspection items
            foreach ($data['items'] as $item) {
                $inspection->items()->updateOrCreate(
                    ['purchase_item_id' => $item['purchase_item_id']],
                    [
                        'inspected_quantity' => $item['inspected_quantity'],
                        'passed_quantity' => $item['passed_quantity'],
                        'failed_quantity' => $item['failed_quantity'],
                        'defect_description' => $item['defect_description'] ?? null,
                    ]
                );
            }
        });
    }

    /**
     * Passed the quality inspection
     */
    public function approveInspection(QualityInspection $inspection): void
    {
        if (!$inspection->isReviewable()) {
            throw new \RuntimeException('The current status does not allow for auditing');
        }

        DB::transaction(function () use ($inspection) {
            $oldStatus = $inspection->status;
            
            // Update the verification status
            $inspection->update(['status' => QualityInspectionStatus::PASSED]);

            // Update the inspection status of the purchase order
            // Ensure the purchase relationship is loaded
            $purchase = $inspection->purchase;
            if (!$purchase) {
                throw new \RuntimeException('Associated purchase order not found for inspection ID: ' . $inspection->id);
            }
            $purchase->update(['inspection_status' => 'passed']);

            // Obtain the target warehouse for purchasing orders more reliably
            $warehouseId = $purchase->warehouse_id;
            if (!$warehouseId) {
                 throw new \RuntimeException('Purchase order (ID: ' . $purchase->id . ') does not have a warehouse_id specified.');
            }
            $targetWarehouse = Warehouse::find($warehouseId);
            if (!$targetWarehouse) {
                throw new \RuntimeException('Target warehouse not found for ID: ' . $warehouseId);
            }

            // Add qualified items to warehouse
            foreach ($inspection->items as $item) {
                if ($item->passed_quantity > 0) {
                    // Find or create inventory records
                    $stock = Stock::firstOrCreate(
                        [
                            'store_id' => $targetWarehouse->id,
                            'product_id' => $item->purchaseItem->product_id,
                        ],
                        [
                            'quantity' => 0,
                            'minimum_quantity' => 0,
                            'maximum_quantity' => 0,
                        ]
                    );

                    // Create in-store records - Removed created_by
                    $stockMovement = $targetWarehouse->stockMovements()->create([
                        'product_id' => $item->purchaseItem->product_id,
                        'movement_type' => 'in',
                        'quantity' => $item->passed_quantity,
                        'reference_type' => QualityInspection::class,
                        'reference_id' => $inspection->id,
                        'notes' => 'Quality inspection and entry into the warehouse',
                        'unit_cost' => $item->purchaseItem->unit_price ?? $item->purchaseItem->product->cost_price ?? 0,
                        'total_cost' => ($item->purchaseItem->unit_price ?? $item->purchaseItem->product->cost_price ?? 0) * $item->passed_quantity,
                    ]);

                    // Update inventory quantity
                    $stock->increment('quantity', $item->passed_quantity);
                    
                    // Update product inventory_count field directly with DB query
                    DB::table('products')
                        ->where('id', $item->purchaseItem->product_id)
                        ->increment('inventory_count', $item->passed_quantity);
                    
                    // Refresh the product model to get the updated inventory count
                    $product = $item->purchaseItem->product->fresh();

                    Log::info('Products were successfully stored', [
                        'stock_movement_id' => $stockMovement->id,
                        'warehouse' => $targetWarehouse->name,
                        'product' => $product->name,
                        'quantity' => $item->passed_quantity,
                        'current_stock' => $stock->quantity,
                        'product_inventory_count' => $product->inventory_count
                    ]);
                }
            }
            
            // Trigger status change event
            event(new QualityInspectionStatusChanged($inspection, $oldStatus->value, QualityInspectionStatus::PASSED->value));
        });
    }

    /**
     * Reject quality inspection
     */
    public function rejectInspection(QualityInspection $inspection): void
    {
        if (!$inspection->isReviewable()) {
            throw new \RuntimeException('The current status does not allow for auditing');
        }

        DB::transaction(function () use ($inspection) {
            $oldStatus = $inspection->status;
            
            // Update the verification status
            $inspection->update(['status' => QualityInspectionStatus::FAILED]);

            // Update the inspection status of the purchase order
            $inspection->purchase->update(['inspection_status' => 'failed']);
            
            // Trigger status change event
            event(new QualityInspectionStatusChanged($inspection, $oldStatus->value, QualityInspectionStatus::FAILED->value));
        });
    }
} 