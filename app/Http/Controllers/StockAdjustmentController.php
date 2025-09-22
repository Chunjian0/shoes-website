<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    /**
     * Show inventory adjustment list
     */
    public function index(): View
    {
        $adjustments = $this->stockService->getStockMovements(
            type: 'stock_adjustment',
            perPage: 15
        );

        return view('stock.adjustments.index', compact('adjustments'));
    }

    /**
     * Show Create Inventory Adjustment Form
     */
    public function create(): View
    {
        $warehouses = Warehouse::where('status', true)->get();
        
        return view('stock.adjustments.create', compact('warehouses'));
    }

    /**
     * Save new inventory adjustments
     */
    public function store(StockAdjustmentRequest $request): RedirectResponse
    {
        $warehouse = Warehouse::findOrFail($request->input('warehouse_id'));
        
        try {
            $this->stockService->handleStockAdjustment(
                warehouse: $warehouse,
                adjustmentDate: $request->date('adjustment_date'),
                adjustmentType: $request->input('adjustment_type'),
                reason: $request->input('reason'),
                items: $request->input('items'),
            );

            return redirect()
                ->route('stock.adjustments.index')
                ->with('success', 'Inventory adjustment completed');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Inventory adjustment failed: ' . $e->getMessage());
        }
    }

    /**
     * Show inventory adjustment details
     */
    public function show(string $id): View
    {
        $adjustment = $this->stockService->getStockMovement($id);

        if (!$adjustment || $adjustment->reference_type !== 'stock_adjustment') {
            abort(404);
        }

        return view('stock.adjustments.show', compact('adjustment'));
    }
} 