<?php

namespace App\Http\Controllers\Stock;

use App\Events\StockChangedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\StockRequest;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display stock list
     */
    public function index(Request $request)
    {
        $query = Stock::query()
            ->with(['store', 'product'])
            ->latest('id');

        // Warehouse screening
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->input('store_id'));
        }

        // Product screening
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        $stocks = $query->paginate(10);
        $warehouses = Warehouse::where('status', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('stock.index', compact('stocks', 'warehouses', 'products'));
    }

    /**
     * Show the stock creation form
     */
    public function create()
    {
        $warehouses = Warehouse::where('status', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('stock.create', compact('warehouses', 'products'));
    }

    /**
     * Store a newly created stock record
     */
    public function store(StockRequest $request)
    {
        try {
            DB::beginTransaction();

            $stock = Stock::updateOrCreate(
                [
                    'store_id' => $request->input('store_id'),
                    'product_id' => $request->input('product_id'),
                ],
                [
                    'quantity' => $request->input('quantity'),
                    'minimum_quantity' => $request->input('minimum_quantity'),
                    'maximum_quantity' => $request->input('maximum_quantity'),
                    'location' => $request->input('location'),
                    'notes' => $request->input('notes'),
                ]
            );

            // 创建库存移动记录
            $this->stockService->createSimpleStockMovement(
                $request->input('product_id'),
                $request->input('store_id'),
                'adjustment',
                (float)$request->input('quantity'),
                'Stock adjustment via form',
                auth()->id()
            );
            
            // 触发库存变更事件
            $oldQuantity = 0; // 对于新记录，旧数量为0
            $newQuantity = $request->input('quantity');
            event(new StockChangedEvent($stock, $oldQuantity, $newQuantity, 'increase'));

            DB::commit();

            return redirect()->route('stock.index')
                ->with('success', 'Stock has been created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create stock: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified stock
     */
    public function show(Stock $stock)
    {
        $stock->load(['store', 'product']);
        return view('stock.show', compact('stock'));
    }

    /**
     * Show the form for editing the specified stock
     */
    public function edit(Stock $stock)
    {
        $warehouses = Warehouse::where('status', true)->get();
        $products = Product::where('is_active', true)->get();
        
        return view('stock.edit', compact('stock', 'warehouses', 'products'));
    }

    /**
     * Update the specified stock
     */
    public function update(StockRequest $request, Stock $stock)
    {
        try {
            DB::beginTransaction();

            $oldQuantity = $stock->quantity;
            $newQuantity = $request->input('quantity');
            $difference = $newQuantity - $oldQuantity;
            
            $stock->update([
                'quantity' => $newQuantity,
                'minimum_quantity' => $request->input('minimum_quantity'),
                'maximum_quantity' => $request->input('maximum_quantity'),
                'location' => $request->input('location'),
                'notes' => $request->input('notes'),
            ]);

            // 如果数量发生变化，创建库存移动记录
            if ($difference != 0) {
                $movementType = $difference > 0 ? 'adjustment_increase' : 'adjustment_decrease';
                $this->stockService->createSimpleStockMovement(
                    $stock->product_id,
                    $stock->store_id,
                    $movementType,
                    abs((float)$difference),
                    'Stock adjustment via update form',
                    auth()->id()
                );
                
                // 触发库存变更事件
                $changeType = $difference > 0 ? 'increase' : 'decrease';
                event(new StockChangedEvent($stock, $oldQuantity, $newQuantity, $changeType));
            }

            DB::commit();

            return redirect()->route('stock.index')
                ->with('success', 'Stock has been updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update stock: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified stock
     */
    public function destroy(Stock $stock)
    {
        try {
            DB::beginTransaction();

            // 创建库存移动记录 - 移除库存
            $this->stockService->createSimpleStockMovement(
                $stock->product_id,
                $stock->store_id,
                'removal',
                (float)$stock->quantity,
                'Stock removed',
                auth()->id()
            );
            
            // 触发库存变更事件
            $oldQuantity = $stock->quantity;
            $newQuantity = 0;
            event(new StockChangedEvent($stock, $oldQuantity, $newQuantity, 'decrease'));

            $stock->delete();

            DB::commit();

            return redirect()->route('stock.index')
                ->with('success', 'Stock has been removed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to remove stock: ' . $e->getMessage());
        }
    }

    /**
     * 显示低库存商品
     */
    public function lowStock()
    {
        $products = Product::where('is_active', true)
            ->whereColumn('inventory_count', '<=', 'min_stock')
            ->where('inventory_count', '>', 0)
            ->with('category')
            ->paginate(15);
            
        return view('stock.low-stock', compact('products'));
    }
    
    /**
     * 显示缺货商品
     */
    public function outOfStock()
    {
        $products = Product::where('is_active', true)
            ->where('inventory_count', '<=', 0)
            ->with('category')
            ->paginate(15);
            
        return view('stock.out-of-stock', compact('products'));
    }
} 