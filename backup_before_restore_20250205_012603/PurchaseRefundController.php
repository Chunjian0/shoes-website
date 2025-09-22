<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PurchaseReturn;
use App\Models\PurchaseRefund;
use App\Http\Requests\PurchaseRefundRequest;
use App\Services\PurchaseRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PurchaseRefundController extends Controller
{
    public function __construct(
        private readonly PurchaseRefundService $purchaseRefundService
    ) {
        $this->authorizeResource(PurchaseRefund::class, 'refund');
    }

    /**
     * Show the Create Refund Record Form
     */
    public function create(PurchaseReturn $purchaseReturn): View
    {
        return view('purchase-returns.refunds.create', compact('purchaseReturn'));
    }

    /**
     * Save a new refund history
     */
    public function store(PurchaseRefundRequest $request, PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $refund = $this->purchaseRefundService->createRefund($purchaseReturn, $request->validated());

        return redirect()
            ->route('purchase-returns.show', $purchaseReturn)
            ->with('success', 'Refund history has been created.');
    }

    /**
     * Show refund record details
     */
    public function show(PurchaseRefund $refund): View
    {
        $refund->load('purchaseReturn');

        return view('purchase-returns.refunds.show', compact('refund'));
    }

    /**
     * Delete the refund history
     */
    public function destroy(PurchaseRefund $refund): RedirectResponse
    {
        $this->purchaseRefundService->deleteRefund($refund);

        return redirect()
            ->route('purchase-returns.show', $refund->purchase_return_id)
            ->with('success', 'Refund history has been deleted.');
    }
} 