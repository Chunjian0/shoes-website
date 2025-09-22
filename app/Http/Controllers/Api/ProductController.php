<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $products = Product::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%");
            })
            ->with(['suppliers' => function($query) {
                $query->select('suppliers.id', 'suppliers.name', 'suppliers.code')
                    ->wherePivot('is_active', true)
                    ->withPivot('unit_price', 'moq', 'lead_time')
                    ->orderBy('supplier_products.unit_price', 'asc');
            }])
            ->select('id', 'name', 'code')
            ->limit(10)
            ->get()
            ->map(function($product) {
                $bestSupplier = $product->suppliers->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'suppliers' => $product->suppliers->map(function($supplier) {
                        return [
                            'id' => $supplier->id,
                            'name' => $supplier->name,
                            'code' => $supplier->code,
                            'unit_price' => $supplier->pivot->unit_price,
                            'moq' => $supplier->pivot->moq,
                            'lead_time' => $supplier->pivot->lead_time,
                        ];
                    }),
                    'best_supplier' => $bestSupplier ? [
                        'id' => $bestSupplier->id,
                        'name' => $bestSupplier->name,
                        'code' => $bestSupplier->code,
                        'unit_price' => $bestSupplier->pivot->unit_price,
                        'moq' => $bestSupplier->pivot->moq,
                        'lead_time' => $bestSupplier->pivot->lead_time,
                    ] : null
                ];
            });

        return response()->json($products);
    }

    /**
     * Get product supplier information
     */
    public function suppliers(Product $product): JsonResponse
    {
        try {
            Log::info('Start getting product supplier information', [
                'product_id' => $product->id,
                'product_name' => $product->name
            ]);

            // Get the structure of the supplier product association table
            $columns = DB::select('SHOW COLUMNS FROM supplier_products');
            Log::info('Supplier product table structure:', ['columns' => $columns]);

            // Get the original database record
            $rawSuppliers = DB::select('SELECT * FROM supplier_products WHERE product_id = ?', [$product->id]);
            Log::info('Original supplier product record:', ['raw_records' => $rawSuppliers]);

            $suppliers = $product->suppliers()
                ->with(['priceAgreements' => function($query) use ($product) {
                    $query->where('product_id', $product->id)
                        ->where(function($q) {
                            $q->where('start_date', '<=', now())
                                ->where(function($q) {
                                    $q->where('end_date', '>=', now())
                                        ->orWhereNull('end_date');
                                });
                        })
                        ->orderBy('min_quantity', 'desc');
                }])
                ->get()
                ->map(function ($supplier) {
                    // Record the original pivot data
                    Log::debug('Supplier raw data', [
                        'supplier_id' => $supplier->id,
                        'supplier_name' => $supplier->name,
                        'pivot_data' => $supplier->pivot->toArray(),
                        'pivot_attributes' => get_object_vars($supplier->pivot),
                        'lead_time' => $supplier->pivot->lead_time ?? 'not set',
                        'raw_pivot' => DB::select('SELECT * FROM supplier_products WHERE supplier_id = ? LIMIT 1', [$supplier->id])
                    ]);

                    $agreements = $supplier->priceAgreements->map(function($agreement) {
                        return [
                            'discount_type' => $agreement->discount_type,
                            'price' => $agreement->price,
                            'discount_rate' => $agreement->discount_rate,
                            'min_quantity' => $agreement->min_quantity,
                            'start_date' => $agreement->start_date->format('Y-m-d'),
                            'end_date' => $agreement->end_date?->format('Y-m-d')
                        ];
                    });
                    
                    $supplierData = [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'purchase_price' => $supplier->pivot->purchase_price,
                        'tax_rate' => $supplier->pivot->tax_rate,
                        'min_order_quantity' => $supplier->pivot->min_order_quantity,
                        'lead_time' => $supplier->pivot->lead_time ?? 0,
                        'price_agreements' => $agreements
                    ];

                    Log::debug('Processed supplier data', [
                        'supplier_id' => $supplier->id,
                        'processed_data' => $supplierData
                    ]);

                    return $supplierData;
                });

            Log::info('Successfully obtain product supplier information', [
                'product_id' => $product->id,
                'suppliers_count' => $suppliers->count(),
                'first_supplier_sample' => $suppliers->first(),
                'supplier_lead_times' => $suppliers->pluck('lead_time', 'name')->toArray(),
                'all_suppliers' => $suppliers->toArray()
            ]);

            $response = [
                'status' => 'success',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'selling_price' => $product->selling_price,
                    'suppliers' => $suppliers
                ]
            ];

            Log::info('APIResponse data:', [
                'response' => $response
            ]);

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Failed to obtain product supplier information', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'sql_queries' => DB::getQueryLog()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to obtain product supplier information'
            ], 500);
        }
    }

    public function getProducts(Request $request)
    {
        Log::info('Product search request', [
            'search' => $request->input('search'),
            'category_id' => $request->input('category_id')
        ]);

        $query = Product::query()
            ->with(['category', 'suppliers' => function($query) {
                $query->where('suppliers.is_active', true)
                    ->whereNull('suppliers.deleted_at')
                    ->select([
                        'suppliers.id',
                        'suppliers.name',
                        'supplier_products.purchase_price',
                        'supplier_products.tax_rate',
                        'supplier_products.min_order_quantity',
                        'supplier_products.lead_time'
                    ])
                    ->withPivot([
                        'purchase_price',
                        'tax_rate',
                        'min_order_quantity',
                        'lead_time',
                        'is_preferred'
                    ]);
            }])
            ->whereNull('products.deleted_at');

        // Search criteria
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Classification filtering
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->get();
        
        Log::info('Products retrieved', [
            'count' => $products->count(),
            'sample' => $products->take(2)->toArray()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function index(): JsonResponse
    {
        Log::info('Getting products for purchase order');
        
        $products = Product::with(['category', 'suppliers'])
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($product) {
                // 为每个产品添加供应商信息
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'category_id' => $product->category_id,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'selling_price' => $product->selling_price,
                    'cost_price' => $product->cost_price,
                    'parameters' => $product->parameters,
                    'suppliers' => $product->suppliers->map(function ($supplier) {
                        return [
                            'id' => $supplier->id,
                            'name' => $supplier->name,
                            'purchase_price' => $supplier->pivot->purchase_price,
                            'tax_rate' => $supplier->pivot->tax_rate,
                            'min_order_quantity' => $supplier->pivot->min_order_quantity,
                            'lead_time' => $supplier->pivot->lead_time ?? 0,
                            'supplier_product_code' => $supplier->pivot->supplier_product_code,
                            'is_preferred' => $supplier->pivot->is_preferred ?? false
                        ];
                    }),
                ];
            });
        
        Log::info('Products retrieved', [
            'count' => $products->count(),
            'sample' => $products->take(2)->toArray()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Get products for customer portal
     */
    public function getProductsForCustomer(Request $request)
    {
        // Generate a cache key based on relevant request parameters
        $queryParams = $request->only(['search', 'category_id', 'sort_by', 'sort_direction', 'price_min', 'price_max', 'brand', 'page', 'per_page', 'featured', 'new', 'sale']);
        ksort($queryParams); // Ensure consistent order
        $cacheKey = 'customer_products_' . md5(http_build_query($queryParams));
        $ttl = 300; // 5 minutes - Shorter TTL due to dynamic nature

        try {
            Log::info('Customer product search request', $queryParams);

            $paginatedResult = Cache::remember($cacheKey, $ttl, function () use ($request, $cacheKey) {
                 Log::info("[Cache Miss] Fetching customer products from DB for key: {$cacheKey}");
                $query = Product::query()
                    ->with(['category']) // Eager load only necessary relations
                    ->where('is_active', true)
                    ->whereNull('deleted_at');

                // Apply filters...
                if ($request->filled('search')) {
                    $search = $request->input('search');
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%")
                          // ... other search fields ...
                          ->orWhere('brand', 'like', "%{$search}%");
                    });
                }
                if ($request->filled('category_id')) {
                    $query->where('category_id', $request->input('category_id'));
                }
                if ($request->filled('brand')) {
                    $query->whereIn('brand', explode(',', $request->input('brand')));
                }
                if ($request->filled('price_min')) {
                    $query->where('selling_price', '>=', $request->input('price_min'));
                }
                if ($request->filled('price_max')) {
                    $query->where('selling_price', '<=', $request->input('price_max'));
                }
                if ($request->boolean('featured')) {
                    $query->where('is_featured', true);
                }
                if ($request->boolean('new')) {
                    // Rely on sorting or specific logic
                }
                if ($request->boolean('sale')) {
                    $query->where(function($q) {
                         $q->where('discount_percentage', '>', 0)
                           ->orWhere('show_in_sale', true);
                    });
                }

                // Apply sorting...
                $sortBy = $request->input('sort_by', 'created_at');
                $sortDirection = $request->input('sort_direction', 'desc');
                $allowedSortFields = ['name', 'selling_price', 'created_at'];
                if (!in_array($sortBy, $allowedSortFields)) {
                    $sortBy = 'created_at';
                }
                $query->orderBy($sortBy, $sortDirection);

                // Paginate
                $perPage = $request->input('per_page', 12);
                return $query->paginate($perPage);
             });

            // Format products for response (map outside the cache block)
            $formattedProducts = $paginatedResult->map(function ($product) {
                 return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category_id' => $product->category_id,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'brand' => $product->brand,
                    'model' => $product->model,
                    'selling_price' => $product->selling_price,
                    'description' => $product->description,
                    'images' => $product->images ? $product->images : ['https://via.placeholder.com/300x300?text=No+Image'],
                    'is_active' => $product->is_active,
                    'parameters' => $product->parameters,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

            Log::info('Products retrieved for customer', [
                'count' => $paginatedResult->count(),
                'total' => $paginatedResult->total(),
                'current_page' => $paginatedResult->currentPage(),
                'last_page' => $paginatedResult->lastPage(),
            ]);

            // Return the paginated response structure
            return response()->json([
                'status' => 'success',
                'data' => [ // Keep the original response structure
                    'products' => $formattedProducts,
                    'pagination' => [
                        'total' => $paginatedResult->total(),
                        'per_page' => $paginatedResult->perPage(),
                        'current_page' => $paginatedResult->currentPage(),
                        'last_page' => $paginatedResult->lastPage(),
                        'from' => $paginatedResult->firstItem(),
                        'to' => $paginatedResult->lastItem(),
                    ]
                 ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get products for customer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
             // On error, maybe try to forget the potentially bad cache entry
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get products. Please try again.',
            ], 500);
        }
    }

    /**
     * Get product details for customer portal
     */
    public function getProductDetails($id)
    {
        $cacheKey = "product_details_{$id}";
        $ttl = 3600; // 1 hour

        try {
             $productData = Cache::remember($cacheKey, $ttl, function () use ($id, $cacheKey) {
                Log::info("[Cache Miss] Fetching product details from DB for key: {$cacheKey}");
                $product = Product::with('category')->findOrFail($id);

                // Format product for response
                $formattedProduct = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'category_id' => $product->category_id,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'description' => $product->category->description,
                    ] : null,
                    'brand' => $product->brand,
                    'model' => $product->model,
                    'selling_price' => $product->selling_price,
                    'description' => $product->description,
                    'images' => $product->images ? $product->images : ['https://via.placeholder.com/300x300?text=No+Image'],
                    'is_active' => $product->is_active,
                    'parameters' => $product->parameters,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];

                // Get related products (same category) - This query should also be efficient
                $relatedProducts = Product::where('category_id', $product->category_id)
                    ->where('id', '!=', $product->id)
                    ->where('is_active', true)
                    ->limit(4)
                    ->get()
                    ->map(function ($relatedProduct) {
                        return [
                            'id' => $relatedProduct->id,
                            'name' => $relatedProduct->name,
                            'sku' => $relatedProduct->sku,
                            'brand' => $relatedProduct->brand,
                            'selling_price' => $relatedProduct->selling_price,
                            'images' => $relatedProduct->images ? $relatedProduct->images : ['https://via.placeholder.com/300x300?text=No+Image'],
                        ];
                    });

                 return [
                    'product' => $formattedProduct,
                    'related_products' => $relatedProducts,
                 ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $productData
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Product not found', ['product_id' => $id]);
            Cache::forget($cacheKey);
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to get product details', [
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve product details.',
            ], 500);
        }
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 12);
        $cacheKey = "featured_products_page_{$page}_perpage_{$perPage}";
        $ttl = 1800; // 30 minutes

        try {
             $paginatedData = Cache::remember($cacheKey, $ttl, function () use ($page, $perPage, $cacheKey) {
                Log::info("[Cache Miss] Fetching featured products from DB for key: {$cacheKey}");
                // 获取标记为精选的产品，并按featured_order排序
                $query = Product::where('is_active', true)
                    ->where('is_featured', true)
                    ->orderBy('featured_order');

                // 使用 paginate 直接获取分页对象
                $productsPaginated = $query->paginate($perPage, ['*'], 'page', $page);

                // Format products within the cache block
                $formattedProducts = $productsPaginated->map(function ($product) {
                    $imageUrl = $product->getImageForType('featured');
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'brand' => $product->brand,
                        'price' => $product->price,
                        'selling_price' => $product->selling_price,
                        'image' => $imageUrl,
                        'images' => $product->getAllImages(),
                        'image_index' => $product->featured_image_index ?? 0,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name
                        ] : null,
                        'created_at' => $product->created_at,
                    ];
                });

                // Return the necessary data for the response structure
                return [
                    'products' => $formattedProducts,
                    'pagination' => [
                        'total' => $productsPaginated->total(),
                        'per_page' => $productsPaginated->perPage(),
                        'current_page' => $productsPaginated->currentPage(),
                        'last_page' => $productsPaginated->lastPage(),
                    ]
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $paginatedData // Use the cached data directly
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get featured products', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get featured products. Please try again.',
            ], 500);
        }
    }

    /**
     * Get new arrivals
     */
    public function getNewArrivals(Request $request)
    {
        $limit = $request->input('limit', 8);
        $cacheKey = "new_arrivals_limit_{$limit}";
        $ttl = 900; // 15 minutes

        try {
            $products = Cache::remember($cacheKey, $ttl, function () use ($limit, $cacheKey) {
                Log::info("[Cache Miss] Fetching new arrivals from DB for key: {$cacheKey}");
                return Product::where('is_active', true)
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get()
                    ->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'brand' => $product->brand,
                            'selling_price' => $product->selling_price,
                            'images' => $product->images ? $product->images : ['https://via.placeholder.com/300x300?text=No+Image'],
                            'created_at' => $product->created_at,
                        ];
                    });
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'products' => $products
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get new arrivals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get new arrivals. Please try again.',
            ], 500);
        }
    }

    /**
     * Get product stock status
     */
    public function getProductStock($id)
    {
        $cacheKey = "product_stock_{$id}";
        $ttl = 60; // 1 minute - very short TTL for stock

        try {
            $stockData = Cache::remember($cacheKey, $ttl, function () use ($id, $cacheKey) {
                Log::info("[Cache Miss] Fetching product stock from DB for key: {$cacheKey}");
                $product = Product::findOrFail($id);
                return [
                    'in_stock' => $product->inventory_count > 0,
                    'quantity' => $product->inventory_count,
                    'available_sizes' => $product->parameters && isset($product->parameters['sizes']) 
                        ? $product->parameters['sizes'] 
                        : ['38', '39', '40', '41', '42'] // Default sizes if not specified
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $stockData
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Product not found when getting stock', ['product_id' => $id]);
            Cache::forget($cacheKey);
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to get product stock', [
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get product stock. Please try again.',
            ], 500);
        }
    }

    /**
     * Get promotion products
     */
    public function getPromotionProducts(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 12);
        $cacheKey = "promotion_products_page_{$page}_perpage_{$perPage}";
        $ttl = 1800; // 30 minutes

        try {
            $paginatedData = Cache::remember($cacheKey, $ttl, function () use ($page, $perPage, $cacheKey) {
                Log::info("[Cache Miss] Fetching promotion products from DB for key: {$cacheKey}");
                // 获取标记为促销的产品
                $query = Product::where('is_active', true)
                    ->where(function ($q) {
                        $q->where('discount_percentage', '>', 0)
                          ->orWhere('show_in_sale', true);
                    })
                    ->orderBy('created_at', 'desc');

                $productsPaginated = $query->paginate($perPage, ['*'], 'page', $page);

                $formattedProducts = $productsPaginated->map(function ($product) {
                    $imageUrl = $product->getImageForType('sale');
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'brand' => $product->brand,
                        'price' => $product->price,
                        'selling_price' => $product->selling_price,
                        'discount_percentage' => $product->discount_percentage ?? 0,
                        'image' => $imageUrl,
                        'images' => $product->getAllImages(),
                        'image_index' => $product->sale_image_index ?? 0,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name
                        ] : null,
                        'created_at' => $product->created_at,
                    ];
                });

                return [
                    'products' => $formattedProducts,
                    'pagination' => [
                        'total' => $productsPaginated->total(),
                        'per_page' => $productsPaginated->perPage(),
                        'current_page' => $productsPaginated->currentPage(),
                        'last_page' => $productsPaginated->lastPage(),
                    ]
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $paginatedData
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get promotion products', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get promotion products. Please try again.',
            ], 500);
        }
    }

    /**
     * Get product details including all images 
     * 
     * @param int $id The product ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductImages($id)
    {
        $cacheKey = "product_images_{$id}";
        $ttl = 3600; // 1 hour

        try {
            $imageData = Cache::remember($cacheKey, $ttl, function() use ($id, $cacheKey) {
                Log::info("[Cache Miss] Fetching product images from DB for key: {$cacheKey}");
                $product = Product::findOrFail($id);
                
                // 获取产品的所有图片
                $images = $product->getAllImages();
                
                // 获取各种图片显示索引
                $featuredImageIndex = $product->featured_image_index;
                $newImageIndex = $product->new_image_index;
                $saleImageIndex = $product->sale_image_index;
                
                Log::info('Product images retrieval', [
                    'product_id' => $id, 
                    'image_count' => count($images),
                    'featured_image_index' => $featuredImageIndex,
                    'new_image_index' => $newImageIndex,
                    'sale_image_index' => $saleImageIndex
                ]);
                
                return [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'images' => $images,
                        'featured_image_index' => $featuredImageIndex,
                        'new_image_index' => $newImageIndex,
                        'sale_image_index' => $saleImageIndex
                    ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $imageData
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Product not found when getting images', ['product_id' => $id]);
            Cache::forget($cacheKey);
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to get product images', [
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get product images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取所有产品，包含标记信息
     */
    public function getAllProducts(Request $request): JsonResponse
    {
        // Generate a cache key based on relevant request parameters
        $queryParams = $request->only(['page', 'per_page', 'search', 'category_id']);
        ksort($queryParams);
        $cacheKey = 'all_products_' . md5(http_build_query($queryParams));
        $ttl = 300; // 5 minutes

        try {
            $paginatedData = Cache::remember($cacheKey, $ttl, function() use ($request, $cacheKey) {
                Log::info("[Cache Miss] Fetching all products from DB for key: {$cacheKey}");
                $perPage = $request->input('per_page', 50);
                $page = $request->input('page', 1);
                $search = $request->input('search', '');
                $categoryId = $request->input('category_id', '');
                
                // 基础查询
                $query = Product::with(['category', 'media']) // Eager load needed relations
                    ->where('is_active', true);
                
                // Apply filters...
                if (!empty($search)) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }
                if (!empty($categoryId)) {
                    $query->where('category_id', $categoryId);
                }
                
                // Use paginate
                $productsPaginated = $query->orderBy('created_at', 'desc')
                                           ->paginate($perPage, ['*'], 'page', $page);

                // Format results
                $formattedProducts = $productsPaginated->map(function ($product) {
                    // Determine product type logic...
                    $newProductDays = (int)Setting::where('key', 'homepage_new_product_days')->value('value') ?: 30;
                    $newUntilDate = $product->new_until_date ? Carbon::parse($product->new_until_date) : null;
                    $isNewProduct = $product->is_new_arrival || ($newUntilDate && $newUntilDate->isFuture()) || $product->created_at->diffInDays(now()) <= $newProductDays;
                    $isSaleProduct = $product->show_in_sale || ($product->discount_percentage && $product->discount_percentage > 0);
                    $originalPrice = $product->price;
                    $sellingPrice = $product->selling_price;
                    if ($product->discount_percentage > 0) {
                        $sellingPrice = round($originalPrice * (1 - $product->discount_percentage / 100), 2);
                    }
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'brand' => $product->brand,
                        'price' => $originalPrice,
                        'selling_price' => $sellingPrice,
                        'discount_percentage' => $product->discount_percentage,
                        'images' => $product->getAllImages(),
                        'featured_image' => $product->getImageForType('featured') ?? '',
                        'new_image' => $product->getImageForType('new') ?? '',
                        'sale_image' => $product->getImageForType('sale') ?? '',
                        'featured_image_index' => $product->featured_image_index ?? 0,
                        'new_image_index' => $product->new_image_index ?? 0,
                        'sale_image_index' => $product->sale_image_index ?? 0,
                        'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                        'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                        'is_featured' => (bool)$product->is_featured,
                        'is_new_arrival' => $isNewProduct,
                        'is_sale' => $isSaleProduct,
                        'new_until_date' => $newUntilDate ? $newUntilDate->format('Y-m-d') : null,
                    ];
                });
                
                // Return data needed for the final response
                return [
                    'products' => $formattedProducts,
                    'pagination' => [
                        'total' => $productsPaginated->total(),
                        'per_page' => $productsPaginated->perPage(),
                        'current_page' => $productsPaginated->currentPage(),
                        'last_page' => $productsPaginated->lastPage(),
                    ]
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $paginatedData
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get all products', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get products. Please try again.',
            ], 500);
        }
    }
} 