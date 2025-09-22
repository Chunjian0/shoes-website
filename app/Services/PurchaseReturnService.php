<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PurchaseReturn;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseReturnService
{
    /**
     * Create a return record
     */
    public function createReturn(array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data) {
            // Create a return order
            $return = PurchaseReturn::create([
                'purchase_id' => $data['purchase_id'],
                'return_number' => 'RET' . date('Ymd') . Str::padLeft((string) random_int(1, 999), 3, '0'),
                'return_date' => $data['return_date'],
                'status' => 'draft',
                'refund_status' => 'pending',
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Create a return item and calculate the amount
            $totalAmount = 0;
            $taxAmount = 0;

            foreach ($data['items'] as $item) {
                $subtotal = $item['return_quantity'] * $item['unit_price'];
                $itemTaxAmount = $subtotal * ($item['tax_rate'] / 100);

                $return->items()->create([
                    'purchase_item_id' => $item['purchase_item_id'],
                    'return_quantity' => $item['return_quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $itemTaxAmount,
                    'subtotal' => $subtotal,
                    'reason' => $item['reason'] ?? null,
                ]);

                $totalAmount += $subtotal;
                $taxAmount += $itemTaxAmount;
            }

            // Update the return order amount
            $return->update([
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'final_amount' => $totalAmount + $taxAmount,
            ]);

            return $return;
        });
    }

    /**
     * Update return history
     */
    public function updateReturn(PurchaseReturn $return, array $data): void
    {
        DB::transaction(function () use ($return, $data) {
            // Update basic information of return order
            $return->update([
                'return_date' => $data['return_date'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Delete the original return item
            $return->items()->delete();

            // Recreate the return item and calculate the amount
            $totalAmount = 0;
            $taxAmount = 0;

            foreach ($data['items'] as $item) {
                $subtotal = $item['return_quantity'] * $item['unit_price'];
                $itemTaxAmount = $subtotal * ($item['tax_rate'] / 100);

                $return->items()->create([
                    'purchase_item_id' => $item['purchase_item_id'],
                    'return_quantity' => $item['return_quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $itemTaxAmount,
                    'subtotal' => $subtotal,
                    'reason' => $item['reason'] ?? null,
                ]);

                $totalAmount += $subtotal;
                $taxAmount += $itemTaxAmount;
            }

            // Update the return order amount
            $return->update([
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'final_amount' => $totalAmount + $taxAmount,
            ]);
        });
    }

    /**
     * Review and pass the return application
     */
    public function approveReturn(PurchaseReturn $return): void
    {
        DB::transaction(function () use ($return) {
            $return->update(['status' => 'approved']);
        });
    }

    /**
     * Reject return application
     */
    public function rejectReturn(PurchaseReturn $return): void
    {
        DB::transaction(function () use ($return) {
            $return->update(['status' => 'rejected']);
        });
    }

    /**
     * Complete the return processing
     */
    public function completeReturn(PurchaseReturn $return): void
    {
        DB::transaction(function () use ($return) {
            // Update return order status
            $return->update(['status' => 'completed']);

            // Update inventory
            foreach ($return->items as $item) {
                // Here you need to call the inventory service to handle inventory changes
                // TODO: Implement inventory return logic
            }
        });
    }
} 