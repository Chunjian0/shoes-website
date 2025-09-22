<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierProductRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;

class SupplierProductController extends Controller
{
    /**
     * Get a list of suppliers
     */
    public function index(Supplier $supplier): JsonResponse
    {
        return response()->json($supplier->products()->with('product')->get());
    }

    /**
     * Add supplier products
     */
    public function store(SupplierProductRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->products()->create($request->validated());
        return response()->json(['message' => 'Product added successfully']);
    }

    /**
     * Update supplier products
     */
    public function update(SupplierProductRequest $request, Supplier $supplier, $productId): JsonResponse
    {
        $product = $supplier->products()->findOrFail($productId);
        $product->update($request->validated());
        return response()->json(['message' => 'Product update successfully']);
    }

    /**
     * Delete supplier products
     */
    public function destroy(Supplier $supplier, $productId): JsonResponse
    {
        $product = $supplier->products()->findOrFail($productId);
        $product->delete();
        return response()->json(['message' => 'Product deletion successfully']);
    }
} 