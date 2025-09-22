<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * Get the price agreement for supplier products
     */
    public function getProductAgreements(Supplier $supplier, Product $product): JsonResponse
    {
        try {
            $agreements = $supplier->priceAgreements()
                ->where('product_id', $product->id)
                ->where('start_date', '<=', now())
                ->where(function ($query) {
                    $query->where('end_date', '>=', now())
                        ->orWhereNull('end_date');
                })
                ->get()
                ->map(function ($agreement) {
                    return [
                        'id' => $agreement->id,
                        'min_quantity' => $agreement->min_quantity,
                        'price' => $agreement->price,
                        'discount_rate' => $agreement->discount_rate,
                        'start_date' => $agreement->start_date->format('Y-m-d'),
                        'end_date' => $agreement->end_date?->format('Y-m-d'),
                        'status' => $agreement->status
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $agreements
            ]);
        } catch (\Exception $e) {
            Log::error('Acquisition of supplier product price agreement failed', [
                'supplier_id' => $supplier->id,
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to obtain price agreement'
            ], 500);
        }
    }
} 