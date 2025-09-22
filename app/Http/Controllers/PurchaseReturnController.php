<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Http\Requests\PurchaseReturnRequest;
use App\Services\PurchaseReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function __construct(
        private readonly PurchaseReturnService $purchaseReturnService
    ) {
        $this->authorizeResource(PurchaseReturn::class, 'purchase_return');
    }

    /**
     * Show return list
     */
    public function index(): View
    {
        $returns = PurchaseReturn::with(['purchase.supplier'])
            ->latest()
            ->paginate(10);

        return view('purchase-returns.index', compact('returns'));
    }

    /**
     * Show Create Return Form
     */
    public function create(): View
    {
        $purchases = Purchase::whereIn('status', ['received', 'partially_received'])
            ->whereHas('qualityInspection', function ($query) {
                $query->where('status', 'failed');
            })
            ->get();

        return view('purchase-returns.create', compact('purchases'));
    }

    /**
     * Save new return history
     */
    public function store(PurchaseReturnRequest $request): RedirectResponse
    {
        $return = $this->purchaseReturnService->createReturn($request->validated());

        return redirect()
            ->route('purchase-returns.show', $return)
            ->with('success', 'Return order has been created.');
    }

    /**
     * Show return details
     */
    public function show(PurchaseReturn $purchaseReturn): View
    {
        $purchaseReturn->load(['purchase.supplier', 'items.purchaseItem.product', 'refunds']);

        return view('purchase-returns.show', compact('purchaseReturn'));
    }

    /**
     * Show edit return form
     */
    public function edit(PurchaseReturn $purchaseReturn): View
    {
        $purchaseReturn->load(['items.purchaseItem.product']);

        return view('purchase-returns.edit', compact('purchaseReturn'));
    }

    /**
     * Update return history
     */
    public function update(PurchaseReturnRequest $request, PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $this->purchaseReturnService->updateReturn($purchaseReturn, $request->validated());

        return redirect()
            ->route('purchase-returns.show', $purchaseReturn)
            ->with('success', 'Return order has been updated.');
    }

    /**
     * Review and pass the return application
     */
    public function approve(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $this->authorize('approve', $purchaseReturn);

        $this->purchaseReturnService->approveReturn($purchaseReturn);

        return back()->with('success', 'Return application has been approved.');
    }

    /**
     * Reject return application
     */
    public function reject(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $this->authorize('reject', $purchaseReturn);

        $this->purchaseReturnService->rejectReturn($purchaseReturn);

        return back()->with('success', 'Return application has been rejected.');
    }

    /**
     * Complete the return processing
     */
    public function complete(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $this->authorize('complete', $purchaseReturn);

        $this->purchaseReturnService->completeReturn($purchaseReturn);

        return back()->with('success', 'Return processing has been completed.');
    }
} 