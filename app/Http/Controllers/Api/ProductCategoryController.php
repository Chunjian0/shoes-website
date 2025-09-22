<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\CategoryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProductCategoryController extends Controller
{
    /**
     * Get all product categories
     */
    public function index()
    {
        // Define cache key and TTL (Time To Live) in seconds (e.g., 1 hour)
        $cacheKey = 'product_categories_all_active';
        $ttl = 3600; // 1 hour

        try {
            // Use Cache::remember to get or store the result
            $categories = Cache::remember($cacheKey, $ttl, function () use ($cacheKey) {
                Log::info("[Cache Miss] Fetching active product categories from DB for key: {$cacheKey}");
                return ProductCategory::where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'code' => $category->code,
                            'description' => $category->description,
                            'image' => $category->image ?? 'https://via.placeholder.com/300x300?text=Category',
                        ];
                    });
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'categories' => $categories
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get product categories', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // On error, maybe try to forget the potentially bad cache entry
            Cache::forget($cacheKey);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get product categories. Please try again.',
            ], 500);
        }
    }

    /**
     * Get category by ID
     */
    public function show($id)
    {
        // Define cache key and TTL
        $cacheKey = "product_category_{$id}";
        $ttl = 3600; // 1 hour

        try {
            // Use Cache::remember
            $categoryData = Cache::remember($cacheKey, $ttl, function () use ($id, $cacheKey) {
                Log::info("[Cache Miss] Fetching product category from DB for key: {$cacheKey}");
                $category = ProductCategory::findOrFail($id);
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'code' => $category->code,
                    'description' => $category->description,
                    'image' => $category->image ?? 'https://via.placeholder.com/300x300?text=Category',
                    'is_active' => $category->is_active,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'category' => $categoryData
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             Log::warning('Product category not found', ['category_id' => $id]);
             // If not found, remove from cache if it exists
             Cache::forget($cacheKey);
             return response()->json(['status' => 'error', 'message' => 'Category not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to get product category', [
                'category_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
             // On error, maybe try to forget the potentially bad cache entry
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve category details.', // Generic message
            ], 500);
        }
    }

    /**
     * Get category parameters
     */
    public function parameters($id)
    {
        // Define cache key and TTL
        $cacheKey = "category_parameters_{$id}";
        $ttl = 3600; // 1 hour

        try {
            // Use Cache::remember
             $parameters = Cache::remember($cacheKey, $ttl, function () use ($id, $cacheKey) {
                Log::info("[Cache Miss] Fetching category parameters from DB for key: {$cacheKey}");
                // Ensure category exists first before fetching parameters
                ProductCategory::findOrFail($id); // This will throw ModelNotFoundException if category doesn't exist
                return CategoryParameter::where('category_id', $id)
                    ->get()
                    ->map(function ($parameter) {
                        return [
                            'id' => $parameter->id,
                            'name' => $parameter->name,
                            'type' => $parameter->type,
                            'options' => $parameter->options, // Assuming options are already decoded if JSON
                            'is_required' => $parameter->is_required,
                        ];
                    });
            });


            return response()->json([
                'status' => 'success',
                'data' => [
                    'parameters' => $parameters
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             Log::warning('Category not found when fetching parameters', ['category_id' => $id]);
             // If category not found, remove from cache if it exists
             Cache::forget($cacheKey);
             return response()->json(['status' => 'error', 'message' => 'Category not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to get category parameters', [
                'category_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
             // On error, maybe try to forget the potentially bad cache entry
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get category parameters. Please try again.',
            ], 500);
        }
    }
} 