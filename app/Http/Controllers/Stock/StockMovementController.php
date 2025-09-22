<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the stock movements.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with(['product', 'warehouse'])
            ->latest();
            
        // 仓库筛选
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }
        
        // 产品筛选
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }
        
        // 移动类型筛选
        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->input('movement_type'));
        }
        
        $movements = $query->paginate(15);
        $warehouses = Warehouse::where('status', true)->get();
        $products = Product::where('is_active', true)->get();
        $movementTypes = [
            'in' => '入库',
            'out' => '出库',
            'adjustment' => '调整',
            'transfer' => '转移',
            'removal' => '移除'
        ];
        
        return view('stock.movements.index', compact('movements', 'warehouses', 'products', 'movementTypes'));
    }
} 