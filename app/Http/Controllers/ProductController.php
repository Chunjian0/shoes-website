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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Media;
use App\Services\NotificationService;
use App\Notifications\ProductCreated;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Models\ProductTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly NotificationService $notificationService
    ) {
        $this->middleware('permission:view products')->only('index', 'show');
        $this->middleware('permission:create products')->only('create', 'store');
        $this->middleware('permission:edit products')->only('edit', 'update');
        $this->middleware('permission:delete products')->only('destroy');
    }

    public function index(Request $request): View|RedirectResponse
    {
        // 获取查询参数
        $search = $request->input('search');
        $category_id = $request->input('category_id');
        $sku = $request->input('sku');
        $brand = $request->input('brand');
        $model = $request->input('model');
        $sort_by = $request->input('sort_by') ?: 'created_at';
        $sort_dir = $request->input('sort_dir') ?: 'desc';
        $status = $request->input('status');
        $stock_status = $request->input('stock_status');
        
        // 检查是否有链接请求
        $templateId = $request->input('link_template');
        $parameterGroup = $request->input('parameter_group');
        $parameterValue = $request->input('parameter_value');
        $parameterCombo = $request->input('parameter_combo');
        
        // 如果是链接参数组合，走特殊处理流程
        if ($templateId && $parameterCombo) {
            return $this->showLinkParameterCombo($request, $templateId, $parameterCombo);
        }
        
        // 如果是链接参数值，走原有流程
        if ($templateId && $parameterGroup && $parameterValue) {
            return $this->showLinkParameterValue($request, $templateId, $parameterGroup, $parameterValue);
        }
        
        // 构建查询条件
        $query = Product::query()->with('category');
        
        // 搜索条件
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // 分类过滤
        if ($category_id) {
            $query->where('category_id', $category_id);
        }
        
        // SKU过滤
        if ($sku) {
            $query->where('sku', 'like', "%{$sku}%");
        }
        
        // 品牌过滤
        if ($brand) {
            $query->where('brand', 'like', "%{$brand}%");
        }
        
        // 型号过滤
        if ($model) {
            $query->where('model', 'like', "%{$model}%");
        }
        
        // 新增：状态过滤
        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool)$status);
        }
        
        // 新增：库存状态过滤
        if ($stock_status) {
            match ($stock_status) {
                'sufficient' => $query->whereColumn('inventory_count', '>', 'min_stock'),
                'low' => $query->whereColumn('inventory_count', '<=', 'min_stock')->where('inventory_count', '>', 0),
                'out' => $query->where('inventory_count', '<= ', 0),
                default => null,
            };
            // 注意: 这里的 `inventory_count` 和 `min_stock` 假设是 `products` 表上的列
            // 如果不是, 你可能需要调整查询, 比如使用 join 或子查询
        }
        
        // 排序
        if (in_array($sort_by, ['name', 'sku', 'price', 'inventory_count', 'created_at'])) {
            $query->orderBy($sort_by, $sort_dir);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // 分页
        $products = $query->paginate(10)
            ->appends($request->except('page'));
            
        // 获取所有分类
        $categories = ProductCategory::all();
        
        // 如果有模板ID，获取模板信息
        $linkTemplate = null;
        if ($templateId) {
            $linkTemplate = ProductTemplate::find($templateId);
        }
        
        // 获取其他过滤参数以便传递
        $search = $request->input('search');
        $status = $request->input('status');
        $stock_status = $request->input('stock_status');

        // 返回视图
        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'category_id' => $category_id,
            'sku' => $sku,
            'brand' => $brand,
            'model' => $model,
            'sort_by' => $sort_by,
            'sort_dir' => $sort_dir,
            'status' => $status,
            'stock_status' => $stock_status,
            'linkTemplate' => $linkTemplate,
        ]);
    }

    public function create(Request $request): View
    {
        $categories = ProductCategory::where('is_active', true)->get();
        $tempId = 'temp_' . Str::random(32);
        
        // 如果提供了template_id，加载模板数据
        $template = null;
        
        
        return view('products.create', compact('categories', 'tempId', 'template'));
    }

    public function store(ProductRequest $request)
    {
        // 在验证前检查SKU和条形码是否已存在
        $existingWithSku = Product::where('sku', $request->sku)->first();
        if ($existingWithSku) {
            return back()->withInput()
                ->with('error', 'A product with this SKU already exists. Please use a different SKU.')
                ->with('error_field', 'sku');
        }
        
        if ($request->filled('barcode')) {
            $existingWithBarcode = Product::where('barcode', $request->barcode)->first();
            if ($existingWithBarcode) {
                return back()->withInput()
                    ->with('error', 'A product with this barcode already exists. Please use a different barcode.')
                    ->with('error_field', 'barcode');
            }
        }

        try {
            // 验证数据 - ProductRequest中的规则已经应用
            $validatedData = $request->validated();
            
            // 开始事务
            DB::beginTransaction();

            // 创建产品
            $product = $this->productRepository->create($validatedData);

            // 关联媒体文件（如果有）
            if ($request->has('media_ids') && is_array($request->media_ids)) {
                $this->associateMedia($product, $request->media_ids);
            }

            // 提交事务
            DB::commit();

            // 发送通知
            if ($product->category) {
                $users = User::whereHas('roles', function ($query) {
                    $query->where('name', 'Admin');
                })->get();

                $notification = new ProductCreated($product);
                Notification::send($users, $notification);
            }

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Product created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 捕获验证异常
            DB::rollBack();
            Log::warning('Validation failed when creating product:', [
                'errors' => $e->errors(),
            ]);
            
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            // 捕获数据库查询异常
            DB::rollBack();
            Log::error('Database error when creating product:', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            $errorMessage = $e->getMessage();
            // 处理重复键错误
            if (stripos($errorMessage, 'Duplicate entry') !== false) {
                if (stripos($errorMessage, 'products_sku_unique') !== false) {
                    return back()->withInput()
                        ->with('error', 'A product with this SKU already exists. Please use a different SKU.')
                        ->with('error_field', 'sku');
                } elseif (stripos($errorMessage, 'products_barcode_unique') !== false) {
                    return back()->withInput()
                        ->with('error', 'A product with this barcode already exists. Please use a different barcode.')
                        ->with('error_field', 'barcode');
                }
            }
            
            // 其他数据库错误的用户友好消息
            return back()->withInput()
                ->with('error', 'Unable to create product due to a database error. Please check your input and try again.')
                ->with('error_field', 'name');
        } catch (\Exception $e) {
            // 捕获所有其他异常
            DB::rollBack();
            Log::error('Unexpected error when creating product:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()
                ->with('error', 'An unexpected error occurred. Our team has been notified. Please try again later.');
        }
    }

    /**
     * Related temporary files to products
     */
    private function associateMedia(Product $product, $mediaIds)
    {
        if (!is_array($mediaIds)) {
            return;
        }
        
        foreach ($mediaIds as $mediaId) {
            $media = Media::find($mediaId);
            if ($media) {
                $media->model_id = $product->id;
                $media->model_type = get_class($product);
                $media->save();
            }
        }
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'media']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $categories = ProductCategory::where('is_active', true)->get();
        $product->load('category', 'media');

        // Prepare images array specifically for the Alpine component
        $imagesForAlpine = $product->media->map(function ($mediaItem) {
            return [
                'id' => $mediaItem->id,
                'name' => $mediaItem->name,
                'url' => Storage::url($mediaItem->path),
                'path' => $mediaItem->path,
            ];
        })->values()->toArray();

        return view('products.edit', compact('product', 'categories', 'imagesForAlpine'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        try {
            $this->productRepository->update($product, $request->validated());

            // 清除缓存，确保产品列表更新
            Cache::flush();

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'The product update is successful!');
        } catch (\Exception $e) {
            Log::error('Update the product failed:' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Update the product failure, please try it out!');
        }
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $productName = $product->name;
            $product->delete();

            // 清除缓存，确保产品列表更新
            Cache::flush();

            return redirect()->route('products.index')
                ->with('success', "Product \"$productName\" has been successfully deleted.");
        } catch (\Exception $e) {
            Log::error('Failed to delete product:', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('products.index')
                ->with('error', 'Failed to delete product. Please try again.');
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
                    'message' => 'Unauthorized visit'
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
                'message' => 'List of product list failed'
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
                'message' => 'Successful product creation'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failure to create goods:' . $e->getMessage());
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
            Log::error('Failure to obtain product details:' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failure to obtain product details failed'
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
                'message' => 'Product update success'
            ]);
        } catch (\Exception $e) {
            Log::error('Update the product failed:' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Update the product failed'
            ], 500);
        }
    }

    public function apiDestroy(Product $product): JsonResponse
    {
        try {
            $this->productRepository->delete($product);

            return response()->json([
                'status' => 'success',
                'message' => 'Deletion of goods successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete goods failed:' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Delete the product failed'
            ], 500);
        }
    }

    /**
     * Get a list of all items(Including supplier information)
     */
    public function getProducts(): JsonResponse
    {
        try {
            Log::info('Start getting product list');
            
            // Get all the products in the active state first
            $query = Product::whereNull('deleted_at')
                ->with(['suppliers' => function($query) {
                    $query->where('suppliers.is_active', true)
                        ->select([
                            'suppliers.id',
                            'suppliers.name',
                            'supplier_products.purchase_price',
                            'supplier_products.min_order_quantity',
                            'supplier_products.lead_time'
                        ]);
                }]);
            
            Log::info('Product InquirySQL:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
            
            $products = $query->get();
            
            Log::info('Query original product data:', [
                'count' => $products->count(),
                'sample' => $products->take(1)->toArray()
            ]);
            
            $transformedProducts = $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->sku, // useskuAs product number
                    'suppliers' => $product->suppliers->map(function($supplier) {
                        return [
                            'id' => $supplier->id,
                            'name' => $supplier->name,
                            'unit_price' => $supplier->pivot->purchase_price,
                            'moq' => $supplier->pivot->min_order_quantity,
                            'lead_time' => $supplier->pivot->lead_time
                        ];
                    })->values()->all() // Make sure to return an array instead of a collection
                ];
            });
            
            Log::info('Converted product data:', [
                'count' => $transformedProducts->count(),
                'sample' => $transformedProducts->take(1)->toArray()
            ]);

            return response()->json($transformedProducts);
            
        } catch (\Exception $e) {
            Log::error('Failed to get product list:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to get product list',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新产品折扣设置
     */
    public function updateDiscount(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'discount_percentage' => 'required|numeric|min:0|max:100',
                'show_in_sale' => 'required|boolean',
                'discount_start_date' => 'nullable|date',
                'discount_end_date' => 'nullable|date|after_or_equal:discount_start_date',
            ]);
            
            $product = Product::findOrFail($validated['product_id']);
            
            $product->discount_percentage = $validated['discount_percentage'];
            $product->show_in_sale = $validated['show_in_sale'];
            
            if ($request->filled('discount_start_date')) {
                $product->discount_start_date = $validated['discount_start_date'];
            } else {
                $product->discount_start_date = null;
            }
            
            if ($request->filled('discount_end_date')) {
                $product->discount_end_date = $validated['discount_end_date'];
            } else {
                $product->discount_end_date = null;
            }
            
            $product->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Discount settings updated successfully',
                'discount_percentage' => $product->discount_percentage,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating discount settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating discount settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取精选产品
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeaturedProducts(): JsonResponse
    {
        try {
            Log::info('获取精选产品');
            
            $query = Product::with('category', 'media')
                ->where('is_featured', true)
                ->orderBy('featured_order', 'asc');
            
            $products = $query->paginate(12);
            
            // 确保每个产品都有图片URL
            $products->getCollection()->transform(function ($product) {
                $images = $product->media->map(function ($media) {
                    return asset('storage/' . $media->path);
                })->toArray();
                
                $product->images = $images;
                $product->image = !empty($images) ? $images[0] : null;
                
                // 移除不需要的media关系数据以减小响应大小
                unset($product->media);
                
                return $product;
            });
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'products' => $products->items(),
                    'pagination' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取精选产品失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch featured products: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get promotion products
     */
    public function getPromotionProducts(): JsonResponse
    {
        try {
            $products = Product::with(['category', 'media'])
                ->where('is_active', true)
                ->where(function($query) {
                    $query->where('on_sale', true)
                        ->orWhere('is_sale', true)
                        ->orWhere('discount_percentage', '>', 0);
                })
                ->orderBy('sale_order', 'asc')
                ->take(12)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get promotion products', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get promotion products'
            ], 500);
        }
    }
    
    /**
     * 获取产品详情
     * 
     * @param int $id 产品ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductDetails($id): JsonResponse
    {
        try {
            Log::info('获取产品详情', ['product_id' => $id]);
            
            $product = Product::with(['category', 'media'])
                ->findOrFail($id);
            
            // 处理产品图片
            $images = $product->media->map(function ($media) {
                return asset('storage/' . $media->path);
            })->toArray();
            
            $product->images = $images;
            $product->image = !empty($images) ? $images[0] : null;
            
            // 如果有折扣，计算折扣价格
            if ($product->discount_percentage > 0) {
                $product->discounted_price = round($product->selling_price * (1 - $product->discount_percentage / 100), 2);
            }
            
            // 移除不需要的media关系数据以减小响应大小
            unset($product->media);
            
            return response()->json([
                'status' => 'success',
                'data' => $product
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('产品不存在', ['product_id' => $id]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('获取产品详情失败', [
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch product details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取产品图片
     * 
     * @param int $id 产品ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductImages($id): JsonResponse
    {
        try {
            Log::info('获取产品图片', ['product_id' => $id]);
            
            $product = Product::with('media')
                ->findOrFail($id);
            
            // 处理产品图片
            $images = $product->media->map(function ($media) {
                return asset('storage/' . $media->path);
            })->toArray();
            
            // 获取特色图片索引等
            $featuredImageIndex = $product->featured_image_index ?? 0;
            $newImageIndex = $product->new_image_index ?? 0;
            $saleImageIndex = $product->sale_image_index ?? 0;
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'images' => $images,
                    'featured_image_index' => $featuredImageIndex,
                    'new_image_index' => $newImageIndex,
                    'sale_image_index' => $saleImageIndex
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('产品不存在', ['product_id' => $id]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('获取产品图片失败', [
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch product images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get new arrivals products
     */
    public function getNewArrivals(): JsonResponse
    {
        try {
            $days = config('settings.new_products_days', 30);
            $products = Product::with(['category', 'media'])
                ->where('is_active', true)
                ->where(function($query) use ($days) {
                    $query->where('created_at', '>=', now()->subDays($days))
                        ->orWhere('show_in_new_arrivals', true);
                })
                ->orderBy('created_at', 'desc')
                ->take(12)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $products
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get new arrivals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get new arrivals'
            ], 500);
        }
    }

    /**
     * 搜索产品，用于首页管理模态框
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $page = (int)$request->input('page', 1);
        $perPage = (int)$request->input('per_page', 10);
        $forSale = (bool)$request->input('for_sale', false);
        
        $query = Product::query()
            ->where('is_active', true)
            ->orderBy('name');
        
        // 如果是为了促销区域添加产品，则只返回有折扣的产品
        if ($forSale) {
            $query->where('discount_percentage', '>', 0);
        }
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        
        $totalProducts = $query->count();
        $products = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($product) {
                $stock = DB::table('stocks')->where('product_id', $product->id)->sum('quantity');
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock' => $stock,
                    'selling_price' => $product->selling_price,
                    'discount_percentage' => $product->discount_percentage,
                    'final_price' => $product->discount_percentage > 0 
                        ? round($product->selling_price * (1 - $product->discount_percentage / 100), 2) 
                        : $product->selling_price,
                    'is_active' => $product->is_active,
                    'image' => count($product->getAllImages()) > 0 ? $product->getMainImage() : null
                ];
            });
        
        return response()->json([
            'products' => $products,
            'pagination' => [
                'total' => $totalProducts,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($totalProducts / $perPage)
            ]
        ]);
    }

    /**
     * 处理参数值链接页面
     */
    public function showLinkParameterValue(Request $request, $templateId, $parameterGroup, $parameterValue)
    {
        // 获取模板
        $linkTemplate = ProductTemplate::with(['category', 'linkedProducts'])->find($templateId);
        if (!$linkTemplate) {
            return redirect()->route('product-templates.index')
                             ->with('error', 'Template not found');
        }
        
        // 处理参数
        $parameters = $linkTemplate->parameters;
        if (is_string($parameters)) {
            try {
                $parameters = json_decode($parameters, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $parameters = [];
                }
            } catch (\Exception $e) {
                $parameters = [];
            }
        } else if (!is_array($parameters)) {
            $parameters = [];
        }
        $linkTemplate->setAttribute('parameters', $parameters);
        
        // 检查参数值是否存在
        $valueFound = false;
        foreach ($parameters as $param) {
            if (isset($param['name']) && $param['name'] === $parameterGroup && 
                isset($param['values']) && is_array($param['values'])) {
                foreach ($param['values'] as $value) {
                    if ($value === $parameterValue) {
                        $valueFound = true;
                        break 2;
                    }
                }
            }
        }
        
        if (!$valueFound) {
            return redirect()->route('product-templates.show', $templateId)
                             ->with('error', 'Parameter value not found');
        }
        
        // 创建参数值完整标识符（用于在后端存储）
        $parameterIdentifier = $parameterGroup . '=' . $parameterValue;
        
        // 查找链接到此参数值的产品
        $linkedProduct = $linkTemplate->linkedProducts()
            ->wherePivot('parameter_group', $parameterIdentifier)
            ->first();
        
        // 查询产品列表，分页展示
        $query = Product::query()->with('category');
        $products = $query->latest()->paginate(10);
        
        // 获取所有分类
        $categories = ProductCategory::all();
        
        // 获取其他过滤参数以便传递
        $search = $request->input('search');
        $status = $request->input('status');
        $stock_status = $request->input('stock_status');

        // 记录日志，方便调试
        Log::info('显示链接参数值页面', [
            'template_id' => $templateId,
            'parameter_group' => $parameterGroup,
            'parameter_value' => $parameterValue,
            'parameter_identifier' => $parameterIdentifier,
            'linked_product' => $linkedProduct ? $linkedProduct->id : null,
            'search' => $search,
            'status' => $status,
            'stock_status' => $stock_status,
        ]);
        
        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'linkTemplate' => $linkTemplate,
            'parameterGroup' => $parameterGroup,
            'parameterValue' => $parameterValue,
            'linkedProduct' => $linkedProduct,
            'search' => $search,
            'status' => $status,
            'stock_status' => $stock_status,
        ]);
    }
    
    /**
     * 处理链接产品到模板的参数值
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeParameterValueLink(Request $request): JsonResponse
    {
        try {
            // 验证请求
            $validated = $request->validate([
                'template_id' => 'required|exists:product_templates,id',
                'product_id' => 'required|exists:products,id',
                'parameter_group' => 'required|string',
                'parameter_value' => 'required|string',
            ]);
            
            $template = ProductTemplate::findOrFail($validated['template_id']);
            $product = Product::findOrFail($validated['product_id']);
            
            // 创建参数值完整标识符（如：color=red）
            $parameterIdentifier = $validated['parameter_group'] . '=' . $validated['parameter_value'];
            
            // 记录日志，帮助调试
            Log::info('链接产品到参数值', [
                'template_id' => $template->id,
                'product_id' => $product->id,
                'parameter_group' => $validated['parameter_group'],
                'parameter_value' => $validated['parameter_value'],
                'parameter_identifier' => $parameterIdentifier
            ]);
            
            // 检查是否已有产品链接到此参数值
            $existingLink = $template->linkedProducts()
                ->wherePivot('parameter_group', $parameterIdentifier)
                ->first();
                
            if ($existingLink && $existingLink->id !== (int)$validated['product_id']) {
                // 先删除现有链接
                $template->linkedProducts()->detach($existingLink->id);
                
                // 记录日志
                Log::info('替换参数值链接', [
                    'template_id' => $template->id,
                    'parameter_group' => $parameterIdentifier,
                    'old_product_id' => $existingLink->id,
                    'new_product_id' => $validated['product_id']
                ]);
            }
            
            // 创建新链接 - 确保使用完整的参数标识符
            $template->linkedProducts()->syncWithoutDetaching([
                $validated['product_id'] => ['parameter_group' => $parameterIdentifier]
            ]);
            
            // 清除缓存
            Cache::flush();
            
            // 返回成功响应
            return response()->json([
                'status' => 'success',
                'message' => '产品已成功链接到参数值',
                'data' => [
                    'template_id' => $template->id,
                    'template_name' => $template->name,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'parameter_group' => $validated['parameter_group'],
                    'parameter_value' => $validated['parameter_value'],
                    'parameter_identifier' => $parameterIdentifier
                ]
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('链接产品到参数值失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => '链接产品失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 移除参数值与产品的链接
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unlinkParameterValue(Request $request): JsonResponse
    {
        try {
            // 验证请求
            $validated = $request->validate([
                'template_id' => 'required|exists:product_templates,id',
                'parameter_group' => 'required|string',
            ]);
            
            $template = ProductTemplate::findOrFail($validated['template_id']);
            
            // 参数组标识符已经是完整格式（如: color=red）
            $parameterGroup = $validated['parameter_group'];
            
            // 查找链接到此参数值的产品
            $linkedProduct = $template->linkedProducts()
                ->wherePivot('parameter_group', $parameterGroup)
                ->first();
                
            if ($linkedProduct) {
                // 删除链接
                $template->linkedProducts()->wherePivot('parameter_group', $parameterGroup)->detach();
                
                // 清除缓存
                Cache::flush();
                
                return response()->json([
                    'status' => 'success',
                    'message' => '参数值链接已成功移除',
                    'data' => [
                        'template_id' => $template->id,
                        'parameter_group' => $parameterGroup
                    ]
                ]);
            }
            
            return response()->json([
                'status' => 'info',
                'message' => '未找到链接的产品',
                'data' => [
                    'template_id' => $template->id,
                    'parameter_group' => $parameterGroup
                ]
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('移除参数值链接失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => '移除链接失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 显示批量设置折扣页面
     *
     * @param Request $request
     * @return View
     */
    public function bulkDiscountForm(Request $request): View
    {
        // 获取产品分类
        $categories = ProductCategory::where('is_active', true)->get();
        
        // 查询产品
        $query = Product::with('category');
        
        // 应用过滤条件
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('sku', 'like', $search)
                  ->orWhere('barcode', 'like', $search);
            });
        }
        
        if ($request->filled('discount_status')) {
            if ($request->discount_status == 'with_discount') {
                $query->where('discount_percentage', '>', 0);
            } elseif ($request->discount_status == 'without_discount') {
                $query->where('discount_percentage', 0);
            }
        }
        
        // 分页获取数据
        $products = $query->paginate(20)->appends($request->query());
        
        return view('products.bulk_discount', compact('products', 'categories'));
    }
    
    /**
     * 更新批量折扣设置
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulkDiscountUpdate(Request $request): RedirectResponse
    {
        // 验证请求数据
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'show_in_sale' => 'boolean',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after_or_equal:discount_start_date',
            'min_quantity_for_discount' => 'nullable|integer|min:0',
            'max_quantity_for_discount' => 'nullable|integer|min:0|gte:min_quantity_for_discount',
        ]);
        
        try {
            DB::beginTransaction();
            
            // 准备数据更新
            $data = [
                'discount_percentage' => $validated['discount_percentage'],
                'show_in_sale' => $request->has('show_in_sale') ? 1 : 0,
            ];
            
            // 可选字段
            if ($request->filled('discount_start_date')) {
                $data['discount_start_date'] = $validated['discount_start_date'];
            }
            
            if ($request->filled('discount_end_date')) {
                $data['discount_end_date'] = $validated['discount_end_date'];
            }
            
            if ($request->filled('min_quantity_for_discount')) {
                $data['min_quantity_for_discount'] = $validated['min_quantity_for_discount'];
            }
            
            if ($request->filled('max_quantity_for_discount')) {
                $data['max_quantity_for_discount'] = $validated['max_quantity_for_discount'];
            }
            
            // 更新产品折扣信息
            $updatedCount = Product::whereIn('id', $validated['product_ids'])->update($data);
            
            DB::commit();
            
            return redirect()->route('products.discounts.bulk')
                ->with('success', "成功更新 {$updatedCount} 个产品的折扣信息");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('批量更新折扣失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '更新折扣失败: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * 处理参数组合链接页面
     */
    public function showLinkParameterCombo(Request $request, $templateId, $parameterCombo)
    {
        // 获取模板
        $linkTemplate = ProductTemplate::with(['category', 'linkedProducts'])->find($templateId);
        if (!$linkTemplate) {
            return redirect()->route('product-templates.index')
                             ->with('error', 'Template not found');
        }
        
        // 处理参数
        $parameters = $linkTemplate->parameters;
        if (is_string($parameters)) {
            try {
                $parameters = json_decode($parameters, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $parameters = [];
                }
            } catch (\Exception $e) {
                $parameters = [];
            }
        } else if (!is_array($parameters)) {
            $parameters = [];
        }
        $linkTemplate->setAttribute('parameters', $parameters);
        
        // 查找链接到此参数组合的产品
        $linkedProduct = $linkTemplate->linkedProducts()
            ->wherePivot('parameter_group', $parameterCombo)
            ->first();
        
        // 查询产品列表，分页展示
        $query = Product::query()->with('category');
        $products = $query->latest()->paginate(10);
        
        // 获取所有分类
        $categories = ProductCategory::all();
        
        // 获取其他过滤参数以便传递
        $search = $request->input('search');
        $status = $request->input('status');
        $stock_status = $request->input('stock_status');

        // 记录日志，方便调试
        Log::info('显示链接参数组合页面', [
            'template_id' => $templateId,
            'parameter_combo' => $parameterCombo,
            'linked_product' => $linkedProduct ? $linkedProduct->id : null,
            'search' => $search,
            'status' => $status,
            'stock_status' => $stock_status,
        ]);
        
        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'linkTemplate' => $linkTemplate,
            'parameterCombo' => $parameterCombo,
            'linkedProduct' => $linkedProduct,
            'search' => $search,
            'status' => $status,
            'stock_status' => $stock_status,
        ]);
    }
} 