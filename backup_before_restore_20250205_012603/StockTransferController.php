<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StockTransferRequest;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    /**
     * Show inventory transfer list
     */
    public function index(): View
    {
        $transfers = $this->stockService->getStockMovements(
            type: 'stock_transfer',
            perPage: 15
        );

        return view('stock.transfers.index', compact('transfers'));
    }

    /**
     * Show Create Inventory Transfer Form
     */
    public function create(): View
    {
        $warehouses = Warehouse::where('status', true)->get();
        
        return view('stock.transfers.create', compact('warehouses'));
    }

    /**
     * Save new stock transfers
     */
    public function store(StockTransferRequest $request): RedirectResponse
    {
        $sourceWarehouse = Warehouse::findOrFail($request->input('source_warehouse_id'));
        $targetWarehouse = Warehouse::findOrFail($request->input('target_warehouse_id'));
        
        try {
            $this->stockService->handleStockTransfer(
                sourceWarehouse: $sourceWarehouse,
                targetWarehouse: $targetWarehouse,
                transferDate: $request->date('transfer_date'),
                items: $request->input('items'),
            );

            return redirect()
                ->route('stock.transfers.index')
                ->with('success', 'Inventory transfer completed');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Inventory transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Show inventory transfer details
     */
    public function show(string $id): View
    {
        $transfer = $this->stockService->getStockMovement($id);

        if (!$transfer || $transfer->reference_type !== 'stock_transfer') {
            abort(404);
        }

        return view('stock.transfers.show', compact('transfer'));
    }
} 