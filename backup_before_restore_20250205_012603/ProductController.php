<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ProductRequest;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
        $this->middleware('permission:view products')->only('index', 'show');
        $this->middleware('permission:create products')->only('create', 'store');
        $this->middleware('permission:edit products')->only('edit', 'update');
        $this->middleware('permission:delete products')->only('destroy');
    }

    public function index(Request $request): View
    {
        $products = $this->productRepository->getAllWithCategory();
        $categories = ProductCategory::where('is_active', true)->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = ProductCategory::where('is_active', true)->get();
        return view('products.create', compact('categories'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        try {
            // Record requested data
            Log::info('Create product request data:', $request->all());

            // Verify data
            $validated = $request->validated();
            Log::info('Verified data:', $validated);

            // Create a product
            $product = $this->productRepository->create($validated);
            Log::info('Product creation successfully:', ['id' => $product->id]);

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Product creation was successful!');
        } catch (\Exception $e) {
            Log::error('Product creation failed:' . $e->getMessage());
            Log::error('Error stack:' . $e->getTraceAsString());
            return back()
                ->withInput()
                ->with('error', 'The product creation failed, please try again!error message:' . $e->getMessage());
        }
    }

    public function show(Product $product): View
    {
        $product->load('category');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->load('category');
        $categories = ProductCategory::where('is_active', true)->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        try {
            $this->productRepository->update($product, $request->validated());

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Product update successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update the product:' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'The product has failed to be updated, please try again!');
        }
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $this->productRepository->delete($product);

            return redirect()
                ->route('products.index')
                ->with('success', 'Product deletion successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete a product:' . $e->getMessage());
            return back()->with('error', 'Deletion of the product failed, please try again!');
        }
    }

    // APIinterface
    public function apiIndex()
    {
        try {
            Log::info('API Index called - User:', [
                'user_id' => auth()->id() ?? 'guest',
                'request' => request()->all()
            ]);
            
            if (!auth()->check()) {
                Log::warning('Unauthorized access attempt to product API');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 401);
            }

            $query = Product::query()
                ->with('category:id,name');
                
            Log::info('Initial query built', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $products = $query->get();
            
            Log::info('Raw products data:', [
                'count' => $products->count(),
                'products' => $products->toArray()
            ]);

            $transformedProducts = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->selling_price,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name
                    ] : null
                ];
            });

            Log::info('Transformed products data:', [
                'count' => $transformedProducts->count(),
                'products' => $transformedProducts->toArray()
            ]);
            
            return response()->json([
                'status' => 'success',
                'data' => $transformedProducts
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiIndex', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get product list'
            ], 500);
        }
    }

    public function apiStore(ProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productRepository->create($request->validated());

            return response()->json([
                'status' => 'success',
                'data' => $product,
                'message' => 'Product creation successfully'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Product creation failed:' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create a product'
            ], 500);
        }
    }

    public function apiShow(Product $product): JsonResponse
    {
        try {
            $product->load('category');

            return response()->json([
                'status' => 'success',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to obtain product details:' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to obtain product details'
            ], 500);
        }
    }

    public function apiUpdate(ProductRequest $request, Product $product): JsonResponse
    {
        try {
            $this->productRepository->update($product, $request->validated());

            return response()->json([
                'status' => 'success',
                'data' => $product,
                'message' => 'Product update successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update the product:' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update the product'
            ], 500);
        }
    }

    public function apiDestroy(Product $product): JsonResponse
    {
        try {
            $this->productRepository->delete($product);

            return response()->json([
                'status' => 'success',
                'message' => 'Product deletion successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete a product:' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete a product'
            ], 500);
        }
    }
} 