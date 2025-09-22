<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierProductRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\SupplierProduct;

class SupplierProductController extends Controller
{
    /**
     * Get supplier's product list
     */
    public function index(Supplier $supplier): JsonResponse
    {
        return response()->json($supplier->products()->with('product')->get());
    }

    /**
     * Add supplier product
     */
    public function store(Request $request, Supplier $supplier): JsonResponse
    {
        $supplier->products()->create($request->validated());
        return response()->json(['message' => 'Product added successfully']);
    }

    /**
     * Update supplier product
     */
    public function update(Request $request, Supplier $supplier, SupplierProduct $product): JsonResponse
    {
        $product->update($request->validated());
        return response()->json(['message' => 'Product updated successfully']);
    }

    /**
     * Delete supplier product
     */
    public function destroy(Supplier $supplier, SupplierProduct $product): JsonResponse
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
} 