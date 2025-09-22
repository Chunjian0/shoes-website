<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PurchaseReturn;
use App\Models\PurchaseRefund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseRefundService
{
    /**
     * Create a refund record
     */
    public function createRefund(PurchaseReturn $return, array $data): PurchaseRefund
    {
        return DB::transaction(function () use ($return, $data) {
            // Check whether the refund amount exceeds the refundable amount
            if ($return->refunded_amount + $data['amount'] > $return->final_amount) {
                throw new \InvalidArgumentException('The refund amount exceeds the refundable amount');
            }

            // Create a refund record
            $refund = $return->refunds()->create([
                'refund_number' => 'REF' . date('Ymd') . Str::padLeft((string) random_int(1, 999), 3, '0'),
                'refund_date' => $data['refund_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update the refund status of the return order
            $this->updateRefundStatus($return);

            return $refund;
        });
    }

    /**
     * Delete the refund history
     */
    public function deleteRefund(PurchaseRefund $refund): void
    {
        DB::transaction(function () use ($refund) {
            $return = $refund->purchaseReturn;
            
            // Delete the refund history
            $refund->delete();

            // Update the refund status of the return order
            $this->updateRefundStatus($return);
        });
    }

    /**
     * Update the refund status of the return order
     */
    private function updateRefundStatus(PurchaseReturn $return): void
    {
        $refundedAmount = $return->refunded_amount;
        $finalAmount = $return->final_amount;

        $status = match (true) {
            $refundedAmount <= 0 => 'pending',
            $refundedAmount < $finalAmount => 'partial',
            default => 'completed',
        };

        $return->update(['refund_status' => $status]);
    }
} 