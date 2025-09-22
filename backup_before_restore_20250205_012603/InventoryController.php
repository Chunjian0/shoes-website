<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\InventoryCheckRequest;
use App\Http\Requests\InventoryLossRequest;
use App\Models\InventoryCheck;
use App\Models\InventoryLoss;
use App\Models\Product;
use App\Repositories\InventoryRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryRepository $inventoryRepository
    ) {
        $this->middleware('permission:manage inventory');
    }

    // Inventory list
    public function checkIndex(): View
    {
        $checks = InventoryCheck::with(['user', 'items.product'])
            ->latest()
            ->paginate();

        return view('inventory.checks.index', compact('checks'));
    }

    // Create inventory points
    public function checkCreate(): View
    {
        $products = Product::where('is_active', true)
            ->with('category')
            ->get();

        return view('inventory.checks.create', compact('products'));
    }

    // Save inventory points
    public function checkStore(InventoryCheckRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $check = $this->inventoryRepository->createInventoryCheck($data);

        return redirect()
            ->route('inventory.checks.show', $check)
            ->with('success', 'Inventory order was created successfully');
    }

    // View inventory points
    public function checkShow(InventoryCheck $check): View
    {
        $check->load(['user', 'items.product']);
        return view('inventory.checks.show', compact('check'));
    }

    // Complete inventory points
    public function checkComplete(InventoryCheck $check): RedirectResponse
    {
        if ($check->status !== InventoryCheck::STATUS_PENDING) {
            return back()->with('error', 'Only pending inventory orders can be completed');
        }

        $this->inventoryRepository->completeInventoryCheck($check);

        return redirect()
            ->route('inventory.checks.show', $check)
            ->with('success', 'Inventory order has been completed');
    }

    // Inventory Loss List
    public function lossIndex(): View
    {
        $losses = InventoryLoss::with(['user', 'items.product'])
            ->latest()
            ->paginate();

        return view('inventory.losses.index', compact('losses'));
    }

    // Create inventory loss report
    public function lossCreate(): View
    {
        $products = Product::where('is_active', true)
            ->where('inventory_count', '>', 0)
            ->with('category')
            ->get();

        return view('inventory.losses.create', compact('products'));
    }

    // Save inventory loss report
    public function lossStore(InventoryLossRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $loss = $this->inventoryRepository->createInventoryLoss($data);

        return redirect()
            ->route('inventory.losses.show', $loss)
            ->with('success', 'The inventory loss order was created successfully');
    }

    // View inventory loss report
    public function lossShow(InventoryLoss $loss): View
    {
        $loss->load(['user', 'items.product']);
        return view('inventory.losses.show', compact('loss'));
    }

    // Review inventory loss report
    public function lossApprove(InventoryLoss $loss): RedirectResponse
    {
        if ($loss->status !== InventoryLoss::STATUS_PENDING) {
            return back()->with('error', 'Only pending damage orders can be reviewed');
        }

        $this->inventoryRepository->approveInventoryLoss($loss);

        return redirect()
            ->route('inventory.losses.show', $loss)
            ->with('success', 'Inventory damages have been reviewed');
    }

    // Inventory warning
    public function lowStock(): View
    {
        $products = Product::where('is_active', true)
            ->whereColumn('inventory_count', '<=', 'min_stock')
            ->with('category')
            ->paginate();

        return view('inventory.alerts.low-stock', compact('products'));
    }

    // Out of stock
    public function outOfStock(): View
    {
        $products = Product::where('inventory_count', 0)
            ->with('category')
            ->paginate();

        return view('inventory.alerts.out-of-stock', compact('products'));
    }

    // Product inventory history
    public function productHistory(Product $product): View
    {
        $records = $product->inventoryRecords()
            ->with(['source'])
            ->latest()
            ->paginate();

        return view('inventory.products.history', compact('product', 'records'));
    }
} 