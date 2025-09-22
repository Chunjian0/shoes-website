<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payment;
use App\Models\Purchase;
use App\Events\PaymentStatusChanged;
use Illuminate\Support\Facades\DB;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * Create a payment history
     */
    public function createPayment(Purchase $purchase, array $data): Payment
    {
        return DB::transaction(function () use ($purchase, $data) {
            $payment = $purchase->payments()->create([
                'payment_number' => $this->generatePaymentNumber(),
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'],
                'notes' => $data['notes'],
            ]);

            $this->updatePurchasePaymentStatus($purchase);

            $this->activityLogService->logCreated($payment, [
                'purchase_number' => $purchase->purchase_number,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method->value,
            ]);

            return $payment;
        });
    }

    /**
     * Delete payment history
     */
    public function deletePayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $purchase = $payment->purchase;
            
            $this->activityLogService->logDeleted($payment, [
                'purchase_number' => $purchase->purchase_number,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method->value,
            ]);
            
            $payment->delete();
            
            $this->updatePurchasePaymentStatus($purchase);
        });
    }

    /**
     * Update the purchase order payment status
     */
    private function updatePurchasePaymentStatus(Purchase $purchase): void
    {
        $oldStatus = $purchase->payment_status;
        $totalPaid = $purchase->payments()->sum('amount');
        
        if ($totalPaid >= $purchase->final_amount) {
            $purchase->payment_status = \App\Enums\PaymentStatus::PAID;
        } elseif ($totalPaid > 0) {
            $purchase->payment_status = \App\Enums\PaymentStatus::PARTIALLY_PAID;
        } else {
            $purchase->payment_status = \App\Enums\PaymentStatus::UNPAID;
        }
        
        if ($purchase->payment_status !== $oldStatus) {
            event(new PaymentStatusChanged(
                $purchase,
                $oldStatus->value,
                $purchase->payment_status->value,
                $totalPaid
            ));
        }
        
        $purchase->save();
    }

    /**
     * Generate payment order number
     */
    private function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $date = date('Ymd');
        $sequence = Payment::whereDate('created_at', today())->count() + 1;

        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }

    /**
     * Process payment for a given sales order.
     * (Stub implementation - logs action and returns success)
     *
     * @param SalesOrder $salesOrder
     * @param string $paymentMethod
     * @param array $paymentData (e.g., credit card details, transaction reference)
     * @return array ['success' => bool, 'message' => string, 'status' => string (payment status)]
     */
    public function processPayment(SalesOrder $salesOrder, string $paymentMethod, array $paymentData = []): array
    {
        Log::info('[Stub] Processing payment', [
            'sales_order_id' => $salesOrder->id,
            'payment_method' => $paymentMethod,
            'amount' => $salesOrder->total_amount,
            'customer_id' => $salesOrder->customer_id
        ]);

        // TODO: Implement actual payment gateway integration or logic based on $paymentMethod
        // For 'cash', maybe just mark as completed.
        // For 'bank_transfer', mark as 'pending confirmation'.
        // For gateway, call the gateway API.

        $status = 'pending'; // Default status
        if ($paymentMethod === 'cash') {
            $status = 'completed';
        } elseif ($paymentMethod === 'bank_transfer') {
            $status = 'pending confirmation';
        }

        return [
            'success' => true, 
            'message' => '[Stub] Payment processed successfully.', 
            'status' => $status // Return the likely payment status for the order
        ];
    }
} 