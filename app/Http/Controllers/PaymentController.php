<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Payment;
use App\Http\Requests\PaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
        $this->authorizeResource(Payment::class, 'payment', [
            'except' => ['create', 'store'],
        ]);
    }

    /**
     * Show the Create Payment Record Form
     */
    public function create(Purchase $purchase): View
    {
        $this->authorize('create', [Payment::class, $purchase]);

        return view('purchases.payments.create', compact('purchase'));
    }

    /**
     * Save new payment history
     */
    public function store(PaymentRequest $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('create', [Payment::class, $purchase]);

        $payment = $this->paymentService->createPayment($purchase, $request->validated());

        return redirect()
            ->route('purchases.payments.show', [$purchase, $payment])
            ->with('success', 'Payment history was created successfully.');
    }

    /**
     * Show payment history details
     */
    public function show(Purchase $purchase, Payment $payment): View
    {
        return view('purchases.payments.show', compact('purchase', 'payment'));
    }

    /**
     * Delete payment history
     */
    public function destroy(Purchase $purchase, Payment $payment): RedirectResponse
    {
        $this->paymentService->deletePayment($payment);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Payment history has been deleted.');
    }
} 