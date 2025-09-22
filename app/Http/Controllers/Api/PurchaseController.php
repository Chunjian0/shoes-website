<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    /**
     * Get a list of items for purchase orders
     */
    public function items(Purchase $purchase): JsonResponse
    {
        $items = $purchase->items()
            ->with(['product' => function ($query) {
                $query->select('id', 'name', 'sku');
            }])
            ->select([
                'id',
                'purchase_id',
                'product_id',
                'quantity',
                'unit_price',
                'total_amount'
            ])
            ->get();

        return response()->json($items);
    }
} 