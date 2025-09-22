<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Purchase;
use App\Models\PaymentPlan;
use Carbon\Carbon;

class PaymentPlanService
{
    /**
     * Generate payment plan for purchase orders
     */
    public function generatePaymentPlan(Purchase $purchase): void
    {
        // Generate payment plan based on the supplier's payment terms
        $paymentTerms = $purchase->supplier->payment_terms ?? 30; // default30sky
        
        PaymentPlan::create([
            'purchase_id' => $purchase->id,
            'amount' => $purchase->total_amount,
            'due_date' => Carbon::now()->addDays($paymentTerms),
            'status' => 'pending',
            'description' => "Purchase Order #{$purchase->id} Payment Plan",
        ]);
    }

    /**
     * Cancel payment plan
     */
    public function cancelPaymentPlan(Purchase $purchase): void
    {
        PaymentPlan::query()
            ->where('purchase_id', $purchase->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }

    /**
     * Update payment plan status
     */
    public function updatePaymentPlanStatus(Purchase $purchase, float $totalPaid): void
    {
        $paymentPlan = PaymentPlan::query()
            ->where('purchase_id', $purchase->id)
            ->where('status', 'pending')
            ->first();

        if (!$paymentPlan) {
            return;
        }

        if ($totalPaid >= $purchase->total_amount) {
            $paymentPlan->update(['status' => 'completed']);
        } elseif ($totalPaid > 0) {
            $paymentPlan->update([
                'amount' => $purchase->total_amount - $totalPaid,
                'status' => 'partially_paid'
            ]);
        }
    }

    /**
     * Check late payments
     */
    public function checkOverduePayments(): void
    {
        PaymentPlan::query()
            ->where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->update(['status' => 'overdue']);
    }
} 