<?php

namespace App\Http\Controllers;

use App\Models\ProductTemplate;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\ProductTemplateProduct;
use App\Models\Media;
use App\Components\TinyMCE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Services\StorageService;
use Carbon\Carbon;

class ProductTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductTemplate::with(['category', 'media'])
            ->withCount('products');

        // 筛选条件处理
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status') && !empty($request->status)) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // 排序处理
        $sortField = $request->sort_by ?? 'name';
        $sortDirection = $request->sort_direction ?? 'asc';
        $validSortFields = ['name', 'base_price', 'created_at', 'products_count'];
        
        if (in_array($sortField, $validSortFields)) {
            if ($sortField === 'products_count') {
                $query->orderBy('products_count', $sortDirection);
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        }

        $templates = $query->paginate(10)
            ->appends($request->except('page'));
            
        // 格式化图片数据
        foreach ($templates as $template) {
            // 默认为空
            $template->image_url = null;
            
            // 从media关联中加载图片
            if ($template->media->isNotEmpty()) {
                $template->image_url = asset('storage/' . $template->media->first()->path);
            } 
            // 尝试从images JSON字段加载图片
            else if (!empty($template->images) && is_array($template->images) && count($template->images) > 0) {
                $firstImage = $template->images[0];
                // 检查图片URL的来源
                if (isset($firstImage['path'])) {
                    $template->image_url = asset('storage/' . $firstImage['path']);
                } else if (isset($firstImage['url'])) {
                    // 检查URL是否已经是完整的URL
                    if (strpos($firstImage['url'], 'http') === 0) {
                        $template->image_url = $firstImage['url'];
                    } else {
                        $template->image_url = asset('storage/' . $firstImage['url']);
                    }
                }
            }
            
            // 如果仍然没有图片，使用默认图片
            if (empty($template->image_url)) {
                $template->image_url = asset('images/product-placeholder.png');
            }
        }

        $categories = ProductCategory::orderBy('name')->get();

        return view('product-templates.index', compact('templates', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 获取所有产品类别
        $categories = ProductCategory::orderBy('name')->get();
        
        // 创建一个空的ProductTemplate对象，方便视图处理
        $productTemplate = new ProductTemplate();
        $productTemplate->is_active = true;
        
        // 初始化空的图片数组
        $productTemplate->images = [];
        
        return view('product-templates.create', compact('categories', 'productTemplate'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:product_categories,id',
            'description' => 'nullable|string',
            'parameters' => 'nullable|string',
            'is_active' => 'boolean',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'base_price' => 'nullable|numeric|min:0',
        ]);

        // 开始事务
        DB::beginTransaction();

        try {
            // 处理parameters
            $parameters = null;
            if (!empty($validatedData['parameters'])) {
                try {
                    $parameters = json_decode($validatedData['parameters'], true);
                    
                    // 如果JSON解码失败，尝试修复
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', $validatedData['parameters']);
                        $parameters = json_decode($cleaned, true);
                        
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception('Invalid JSON format for parameters');
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('JSON decode error in store(): ' . $e->getMessage());
                    return back()->withInput()->withErrors(['parameters' => 'Invalid parameter format: ' . $e->getMessage()]);
                }
            }

            // 创建新的产品模板
            $productTemplate = new ProductTemplate([
                'name' => $validatedData['name'],
                'category_id' => $validatedData['category_id'],
                'description' => $validatedData['description'] ?? null,
                'parameters' => $parameters,
                'is_active' => $validatedData['is_active'] ?? true,
                'brand' => $validatedData['brand'] ?? null,
                'model' => $validatedData['model'] ?? null,
                'base_price' => $validatedData['base_price'] ?? 0,
            ]);

            // 保存产品模板
            $productTemplate->save();

            // 关联临时上传的媒体文件
            if ($request->has('temp_id') && !empty($request->temp_id)) {
                try {
                    // 查询临时媒体文件
                    $tempMedia = \App\Models\Media::where('temp_id', $request->temp_id)
                                                 ->where('model_type', 'App\\Models\\ProductTemplate')
                                                 ->whereNull('model_id')
                                                 ->get();
                    
                    // 如果存在临时媒体文件，更新它们关联到此产品模板
                    if ($tempMedia->count() > 0) {
                        foreach ($tempMedia as $media) {
                            $media->model_id = $productTemplate->id;
                            $media->temp_id = null; // 清除临时ID
                            $media->save();
                        }
                        
                        // 刷新图片数组以包含新关联的媒体
                        $productTemplate->load('media');
                        
                        // 格式化图片数据
                        $productTemplate->images = $productTemplate->media->map(function($media) {
                            return [
                                'id' => $media->id,
                                'name' => $media->name,
                                'path' => $media->path,
                                'url' => $media->path
                            ];
                        })->toArray();
                        
                        // 保存更新后的图片数组
                        $productTemplate->save();
                    }
                } catch (\Exception $e) {
                    Log::error('Error associating temp media: ' . $e->getMessage(), [
                        'exception' => $e,
                        'temp_id' => $request->temp_id,
                        'product_template_id' => $productTemplate->id
                    ]);
                    // 不中断流程，只记录错误
                }
            }
            
            // 提交事务
            DB::commit();
            
            return redirect()->route('product-templates.show', $productTemplate)
                            ->with('success', 'Product template created successfully.');
                            
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            
            Log::error('Error creating product template: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return back()->withInput()->withErrors(['error' => 'Failed to create product template: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductTemplate $productTemplate)
    {
        // 加载关联的模型
        $productTemplate->load('category', 'media');
        
        // 加载链接的产品
        $productTemplate->load('linkedProducts');
        
        // 格式化图片数据，确保视图中能够正确使用
        if ($productTemplate->media->isNotEmpty()) {
            $productTemplate->images = $productTemplate->media->map(function($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->name,
                    'path' => $media->path,
                    'url' => $media->path
                ];
            })->toArray();
        }
        
        // 产品集合，分类了
        $products = Product::where('template_id', $productTemplate->id)
                        ->orderBy('id', 'desc')
                        ->paginate(10);
        
        return view('product-templates.show', [
            'productTemplate' => $productTemplate,
            'products' => $products,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductTemplate $productTemplate)
    {
        // 获取产品模板的信息
        $productTemplate->load('category', 'media');

        // 格式化图片数据
        $productTemplate->images = $productTemplate->media->map(function($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'path' => $media->path,
                'url' => asset('storage/' . $media->path)
            ];
        })->toArray();

        // 获取所有类别
        $categories = ProductCategory::all();

        return view('product-templates.edit', [
            'productTemplate' => $productTemplate,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductTemplate $productTemplate)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
            'parameters' => 'nullable',
            'is_active' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
        ]);

        // 获取 store_id（如果存在）
        $storeId = $request->input('store_id');

        // 开始事务
        DB::beginTransaction();

        try {
            // 处理parameters
            $parameters = null;
            if (!empty($validatedData['parameters'])) {
                try {
                    if (is_string($validatedData['parameters'])) {
                        $parameters = json_decode($validatedData['parameters'], true);
                        
                        // 如果JSON解码失败，尝试修复
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            // 尝试一些基本的修复
                            $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', $validatedData['parameters']);
                            $parameters = json_decode($cleaned, true);
                        }
                    } else {
                        $parameters = $validatedData['parameters'];
                    }
                } catch (\Exception $e) {
                    Log::error('JSON decode error: ' . $e->getMessage());
                }
            }

            // 获取模型的fillable字段
            $fillableFields = $productTemplate->getFillable();
            
            // 准备更新数据 - 只使用模型中定义为fillable的字段
            $updateData = array_intersect_key([
                'name' => $validatedData['name'],
                'category_id' => $validatedData['category_id'] ?? null,
                'description' => $validatedData['description'] ?? null,
                'parameters' => $parameters,
                'is_active' => $validatedData['is_active'] ?? true,
            ], array_flip($fillableFields));
            
            // 执行更新操作
            $updateResult = $productTemplate->update($updateData);
            
            // 获取请求中的门店IDs
            $storeIds = $request->input('store_ids', []);
            
            // 更新相关门店 - 使用try-catch处理可能的表不存在错误
            try {
                $associatedStoreIds = DB::table('product_template_store')
                    ->where('product_template_id', $productTemplate->id)
                    ->pluck('store_id')
                    ->toArray();
                
                // 移除不在当前列表中的门店关联
                if (count($associatedStoreIds) > 0) {
                    DB::table('product_template_store')
                        ->where('product_template_id', $productTemplate->id)
                        ->whereNotIn('store_id', $storeIds)
                        ->delete();
                }
                
                // 创建或更新现有门店关联
                foreach ($storeIds as $storeId) {
                    try {
                        $exists = DB::table('product_template_store')
                            ->where('product_template_id', $productTemplate->id)
                            ->where('store_id', $storeId)
                            ->exists();
                        
                        if ($exists) {
                            // 更新现有关联
                            DB::table('product_template_store')
                                ->where('product_template_id', $productTemplate->id)
                                ->where('store_id', $storeId)
                                ->update([
                                    'updated_at' => now(),
                                ]);
                        } else {
                            // 创建新关联
                            DB::table('product_template_store')->insert([
                                'product_template_id' => $productTemplate->id,
                                'store_id' => $storeId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to update store association: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                // 表不存在或其他问题，记录日志但不中断流程
                Log::warning('Could not update product template store associations: ' . $e->getMessage());
            }

            // 处理要删除的图片
            if (!empty($validatedData['remove_images'])) {
                $images = $productTemplate->images ?? [];
                $newImages = [];
                foreach ($images as $index => $image) {
                    if (!in_array($index, $validatedData['remove_images'])) {
                        $newImages[] = $image;
                    } else {
                        // 删除文件
                        if (isset($image['url'])) {
                            Storage::disk('public')->delete($image['url']);
                        }
                    }
                }
                $productTemplate->images = $newImages;
                $productTemplate->save();
            }

            // 处理新上传的图片
            if ($request->hasFile('images')) {
                $images = $productTemplate->images ?? [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product_templates', 'public');
                    $images[] = [
                        'url' => $path,
                        'name' => $image->getClientOriginalName(),
                    ];
                }
                $productTemplate->update(['images' => $images]);
            }

            // 关联临时上传的媒体文件
            if ($request->has('temp_id') && !empty($request->temp_id)) {
                try {
                    // 先查询临时媒体文件是否存在
                    $tempMedia = \App\Models\Media::where('temp_id', $request->temp_id)
                                                 ->where('model_type', 'App\\Models\\ProductTemplate')
                                                 ->whereNull('model_id')
                                                 ->get();
                    
                    if ($tempMedia->count() > 0) {
                        // 调用媒体控制器的关联方法
                        app(\App\Http\Controllers\MediaController::class)->associate(new Request([
                            'temp_id' => $request->temp_id,
                            'model_type' => 'App\\Models\\ProductTemplate',
                            'model_id' => $productTemplate->id,
                        ]));
                        
                        // 重新加载模板，确保媒体关联已反映到模型中
                        $productTemplate->load('media');
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to associate media files: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()->route('product-templates.index')
                ->with('success', 'Product template updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update product template', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update product template: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductTemplate $productTemplate)
    {
        DB::beginTransaction();

        try {
            // 删除图片
            if (!empty($productTemplate->images)) {
                foreach ($productTemplate->images as $image) {
                    if (isset($image['url'])) {
                        Storage::disk('public')->delete($image['url']);
                    }
                }
            }
            
            // 删除模板
            $productTemplate->delete();

            DB::commit();
            return redirect()->route('product-templates.index')
                ->with('success', 'Product template deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete product template: ' . $e->getMessage());
        }
    }

    /**
     * Link a product to the template
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function linkProduct(Request $request)
    {
        // 处理DELETE请求，解除产品与模板的链接
        if ($request->isMethod('delete')) {
            $request->validate([
                'template_id' => 'required|exists:product_templates,id',
                'product_id' => 'required|exists:products,id',
            ]);
            
            $template = ProductTemplate::findOrFail($request->template_id);
            $product = Product::findOrFail($request->product_id);
            
            // 查询条件
            $query = [
                'product_template_id' => $template->id,
                'product_id' => $product->id,
            ];
            
            // 如果有参数组，加入查询条件
            if ($request->has('parameter_group') && !empty($request->parameter_group)) {
                $query['parameter_group'] = $request->parameter_group;
            } else {
                $query['parameter_group'] = null;
            }
            
            // 删除关联
            $deleted = ProductTemplateProduct::where($query)->delete();
            
            if ($deleted) {
                $message = "已成功取消产品\"{$product->name}\"与模板的链接";
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => $message
                    ]);
                }
                
                return redirect()->route('product-templates.show', $template)
                        ->with('success', $message);
            }
            
            $message = "未找到产品\"{$product->name}\"与模板的链接";
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message
                ], 404);
            }
            
            return redirect()->route('product-templates.show', $template)
                    ->with('error', $message);
        }
        
        // 处理POST请求，建立产品与模板的链接
        $request->validate([
            'template_id' => 'required|exists:product_templates,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $template = ProductTemplate::findOrFail($request->template_id);
        $product = Product::findOrFail($request->product_id);
        
        // If parameter_group is provided, link with parameter group
        if ($request->has('parameter_group') && !empty($request->parameter_group)) {
            $parameterGroup = $request->parameter_group;
            
            // Check if product is already linked to this template with this parameter group
            $existingLink = ProductTemplateProduct::where('product_template_id', $template->id)
                ->where('product_id', $product->id)
                ->where('parameter_group', $parameterGroup)
                ->first();
                
            if (!$existingLink) {
                ProductTemplateProduct::create([
                    'product_template_id' => $template->id,
                    'product_id' => $product->id,
                    'parameter_group' => $parameterGroup
                ]);
                
                $message = "成功将产品\"{$product->name}\"链接到模板\"{$template->name}\"的参数组\"{$parameterGroup}\"";
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => $message,
                        'product' => $product,
                        'template' => $template,
                        'parameter_group' => $parameterGroup
                    ]);
                }
                
                return redirect()->route('products.index', [
                    'link_template' => $template->id,
                    'parameter_group' => $parameterGroup
                ])->with('success', $message);
            }
            
            $message = "产品\"{$product->name}\"已经链接到模板\"{$template->name}\"的参数组\"{$parameterGroup}\"";
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'info',
                    'message' => $message,
                    'product' => $product,
                    'template' => $template,
                    'parameter_group' => $parameterGroup
                ]);
            }
            
            return redirect()->route('products.index', [
                'link_template' => $template->id,
                'parameter_group' => $parameterGroup
            ])->with('info', $message);
        } else {
            // Link without parameter group
            $existingLink = ProductTemplateProduct::where('product_template_id', $template->id)
                ->where('product_id', $product->id)
                ->whereNull('parameter_group')
                ->first();
                
            if (!$existingLink) {
                ProductTemplateProduct::create([
                    'product_template_id' => $template->id,
                    'product_id' => $product->id
                ]);
                
                $message = "成功将产品\"{$product->name}\"链接到模板\"{$template->name}\"";
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => $message,
                        'product' => $product,
                        'template' => $template
                    ]);
                }
                
                return redirect()->route('products.index', ['link_template' => $template->id])
                    ->with('success', $message);
            }
            
            $message = "产品\"{$product->name}\"已经链接到模板\"{$template->name}\"";
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'info',
                    'message' => $message,
                    'product' => $product,
                    'template' => $template
                ]);
            }
            
            return redirect()->route('products.index', ['link_template' => $template->id])
                ->with('info', $message);
        }
    }

    /**
     * 从精选列表中移除产品模板
     *
     * @param  \App\Models\ProductTemplate  $productTemplate
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromFeatured(\App\Models\ProductTemplate $productTemplate)
    {
        try {
            // 记录模板ID用于响应
            $id = $productTemplate->id;
            $name = $productTemplate->name;
            
            // 只需移除关联关系即可，不删除模板本身
            $productTemplate->is_featured = false;
            $productTemplate->save();
            
            // 记录操作日志
            Log::info('Product template removed from featured list', [
                'id' => $id,
                'name' => $name,
                'user' => auth()->user()->email ?? 'system'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '产品模板已从精选列表中移除',
                'template_id' => $id
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing product template from featured list', [
                'error' => $e->getMessage(),
                'template_id' => $productTemplate->id ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '移除产品模板失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 从新品列表中移除产品模板
     *
     * @param  \App\Models\ProductTemplate  $productTemplate
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromNewArrival(\App\Models\ProductTemplate $productTemplate)
    {
        try {
            // 记录模板ID用于响应
            $id = $productTemplate->id;
            $name = $productTemplate->name;
            
            // 只需移除新品标记即可，不删除模板本身
            $productTemplate->is_new_arrival = false;
            $productTemplate->save();
            
            // 记录操作日志
            Log::info('Product template removed from new arrivals list', [
                'id' => $id,
                'name' => $name,
                'user' => auth()->user()->email ?? 'system'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '产品模板已从新品列表中移除',
                'template_id' => $id
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing product template from new arrivals list', [
                'error' => $e->getMessage(),
                'template_id' => $productTemplate->id ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '移除产品模板失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 从促销列表中移除产品模板
     *
     * @param  \App\Models\ProductTemplate  $productTemplate
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromSale(\App\Models\ProductTemplate $productTemplate)
    {
        try {
            // 记录模板ID用于响应
            $id = $productTemplate->id;
            $name = $productTemplate->name;
            
            // 只需移除促销标记即可，不删除模板本身
            $productTemplate->is_sale = false;
            $productTemplate->save();
            
            // 记录操作日志
            Log::info('Product template removed from sale list', [
                'id' => $id,
                'name' => $name,
                'user' => auth()->user()->email ?? 'system'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '产品模板已从促销列表中移除',
                'template_id' => $id
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing product template from sale list', [
                'error' => $e->getMessage(),
                'template_id' => $productTemplate->id ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '移除产品模板失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取消链接参数组合
     */
    public function unlinkParameterCombo(Request $request)
    {
        try {
            // 验证请求
            $validated = $request->validate([
                'template_id' => 'required|exists:product_templates,id',
                'parameter_group' => 'required|string'
            ]);
            
            $templateId = $validated['template_id'];
            $parameterGroup = $validated['parameter_group'];
            
            $template = ProductTemplate::findOrFail($templateId);
            
            // 记录操作
            Log::info('解除参数组合链接', [
                'template_id' => $templateId,
                'parameter_group' => $parameterGroup,
                'user' => auth()->user()->email ?? 'system'
            ]);
            
            // 查找匹配此参数组合的链接
            $links = DB::table('product_template_product')
                    ->where('product_template_id', $templateId)
                    ->where('parameter_group', $parameterGroup)
                    ->get();
            
            if ($links->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => '未找到参数组合对应的链接'
                ], 404);
            }
            
            // 删除链接
            $deleted = DB::table('product_template_product')
                ->where('product_template_id', $templateId)
                ->where('parameter_group', $parameterGroup)
                ->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => '已成功取消参数组合的链接',
                'removed_count' => $deleted
            ]);
        } catch (ValidationException $e) {
            Log::error('解除参数组合链接验证失败: ' . json_encode($e->errors()));
            
            return response()->json([
                'status' => 'error',
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('解除参数组合链接时出错: ' . $e->getMessage(), [
                'template_id' => $request->template_id ?? 'unknown',
                'parameter_group' => $request->parameter_group ?? 'unknown',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => '处理请求时出错: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 链接产品到参数组合
     */
    public function linkParameterCombo(Request $request)
    {
        try {
            // 验证请求
            $validated = $request->validate([
                'template_id' => 'required|exists:product_templates,id',
                'product_id' => 'required|exists:products,id',
                'parameter_combo' => 'required|string'
            ]);
            
            $templateId = $validated['template_id'];
            $productId = $validated['product_id'];
            $parameterCombo = $validated['parameter_combo'];
            
            $template = ProductTemplate::findOrFail($templateId);
            $product = Product::findOrFail($productId);
            
            // 记录操作
            Log::info('链接产品到参数组合', [
                'template_id' => $templateId,
                'product_id' => $productId,
                'parameter_combo' => $parameterCombo,
                'user' => auth()->user()->email ?? 'system'
            ]);
            
            // 检查组合是否已链接
            $existingLink = DB::table('product_template_product')
                        ->where('product_template_id', $templateId)
                        ->where('parameter_group', $parameterCombo)
                        ->first();
            
            if ($existingLink) {
                // 如果已经链接到不同的产品，先解除链接
                if ($existingLink->product_id != $productId) {
                    DB::table('product_template_product')
                        ->where('product_template_id', $templateId)
                        ->where('parameter_group', $parameterCombo)
                        ->delete();
                    
                    // 创建新链接
                    DB::table('product_template_product')->insert([
                        'product_template_id' => $templateId,
                        'product_id' => $productId,
                        'parameter_group' => $parameterCombo,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => '已成功更新产品链接到参数组合',
                        'product' => [
                            'id' => $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku
                        ],
                        'parameter_combo' => $parameterCombo
                    ]);
                }
                
                return response()->json([
                    'status' => 'info',
                    'message' => '此参数组合已链接到此产品',
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku
                    ],
                    'parameter_combo' => $parameterCombo
                ]);
            }
            
            // 创建链接
            DB::table('product_template_product')->insert([
                'product_template_id' => $templateId,
                'product_id' => $productId,
                'parameter_group' => $parameterCombo,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => '已成功链接产品到参数组合',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku
                ],
                'parameter_combo' => $parameterCombo
            ]);
        } catch (ValidationException $e) {
            Log::error('链接产品到参数组合验证失败: ' . json_encode($e->errors()));
            
            return response()->json([
                'status' => 'error',
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('链接产品到参数组合时出错: ' . $e->getMessage(), [
                'template_id' => $request->template_id ?? 'unknown',
                'product_id' => $request->product_id ?? 'unknown',
                'parameter_combo' => $request->parameter_combo ?? 'unknown',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => '处理请求时出错: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取模板的所有参数组合
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function allParameterCombos(Request $request, $templateId)
    {
        try {
            $template = ProductTemplate::with(['linkedProducts', 'media'])->findOrFail($templateId);
            
            // 格式化图片数据，确保在API响应中返回正确的图片URL
            if ($template->media->isNotEmpty()) {
                $template->images = $template->media->map(function($media) {
                    return [
                        'id' => $media->id,
                        'name' => $media->name,
                        'path' => $media->path,
                        'url' => $media->path
                    ];
                })->toArray();
            }
            
            $parameters = $template->parameters;
            
            if (!is_array($parameters) || empty($parameters)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template has no parameters defined'
                ], 404);
            }
            
            // 生成参数组合
            $combinations = $this->generateParameterCombinations($parameters);
            
            // 获取每个组合的状态（是否已链接）
            $linkedCombos = DB::table('product_template_product')
                ->where('product_template_id', $templateId)
                ->where('is_parameter_combo', true)
                ->get(['parameter_group', 'product_id'])
                ->keyBy('parameter_group')
                ->toArray();
            
            $result = [];
            
            foreach ($combinations as $combo) {
                $comboString = $this->formatParameterCombo($combo);
                
                // 确定链接状态
                $isLinked = isset($linkedCombos[$comboString]);
                $productId = $isLinked ? $linkedCombos[$comboString]->product_id : null;
                
                // 获取产品信息（如果已链接）
                $product = null;
                if ($isLinked && $productId) {
                    $linkedProduct = $template->linkedProducts->firstWhere('id', $productId);
                    if ($linkedProduct) {
                        // 处理链接产品的图片
                        $productImages = [];
                        if ($linkedProduct->relationLoaded('media') && $linkedProduct->media->count() > 0) {
                            $productImages = $linkedProduct->media->map(function($media) {
                                return [
                                    'id' => $media->id, 
                                    'url' => $media->path,
                                    'thumbnail' => $media->path
                                ];
                            })->toArray();
                        } elseif (!empty($linkedProduct->images) && is_array($linkedProduct->images)) {
                            $productImages = $linkedProduct->images;
                        }
                        
                        $product = [
                            'id' => $linkedProduct->id,
                            'name' => $linkedProduct->name,
                            'sku' => $linkedProduct->sku,
                            'price' => (float)$linkedProduct->selling_price,
                            'stock_quantity' => (int)$linkedProduct->stock,
                            'images' => $productImages
                        ];
                    }
                }
                
                $result[] = [
                    'parameter_values' => $combo,
                    'parameter_group_string' => $comboString,
                    'is_linked' => $isLinked,
                    'product' => $product
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'parameters' => $parameters,
                    'images' => $template->images
                ],
                'combinations' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting parameter combinations: ' . $e->getMessage(), [
                'exception' => $e,
                'template_id' => $templateId
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve parameter combinations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取下一个参数组合
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function nextParameterCombination(Request $request)
    {
        try {
            $templateId = $request->input('template_id');
            if (!$templateId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template ID is required'
                ], 400);
            }
            
            $template = ProductTemplate::with(['linkedProducts', 'media'])->findOrFail($templateId);
            
            // 格式化图片数据，确保在API响应中返回正确的图片URL
            if ($template->media->isNotEmpty()) {
                $template->images = $template->media->map(function($media) {
                    return [
                        'id' => $media->id,
                        'name' => $media->name,
                        'path' => $media->path,
                        'url' => $media->path
                    ];
                })->toArray();
            }
            
            $parameters = $template->parameters;
            
            if (!is_array($parameters) || empty($parameters)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template has no parameters defined'
                ], 404);
            }
            
            $currentCombo = $request->input('current_combo');
            
            // 生成参数组合
            $combinations = $this->generateParameterCombinations($parameters);
            
            if (empty($combinations)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No parameter combinations generated'
                ], 404);
            }
            
            // 获取链接的组合
            $linkedCombos = DB::table('product_template_product')
                ->where('product_template_id', $templateId)
                ->where('is_parameter_combo', true)
                ->pluck('parameter_group')
                ->toArray();
            
            // 确定当前组合的索引
            $currentIndex = -1;
            
            if ($currentCombo) {
                foreach ($combinations as $index => $combo) {
                    $comboString = $this->formatParameterCombo($combo);
                    if ($comboString === $currentCombo) {
                        $currentIndex = $index;
                        break;
                    }
                }
            }
            
            // 循环查找下一个未链接的组合
            $nextCombo = null;
            $nextIndex = null;
            
            if ($currentIndex === -1) {
                // 当前组合未找到，从第一个开始
                $startIndex = 0;
            } else {
                // 从当前组合的下一个开始
                $startIndex = $currentIndex + 1;
            }
            
            // 首先查找当前索引后的组合
            for ($i = $startIndex; $i < count($combinations); $i++) {
                $combo = $combinations[$i];
                $comboString = $this->formatParameterCombo($combo);
                
                if (!in_array($comboString, $linkedCombos)) {
                    $nextCombo = $combo;
                    $nextIndex = $i;
                    break;
                }
            }
            
            // 如果未找到，从头开始查找
            if ($nextCombo === null && $currentIndex !== -1) {
                for ($i = 0; $i < $currentIndex; $i++) {
                    $combo = $combinations[$i];
                    $comboString = $this->formatParameterCombo($combo);
                    
                    if (!in_array($comboString, $linkedCombos)) {
                        $nextCombo = $combo;
                        $nextIndex = $i;
                        break;
                    }
                }
            }
            
            if ($nextCombo) {
                $nextComboString = $this->formatParameterCombo($nextCombo);
                
                // 返回下一个组合信息
                return response()->json([
                    'status' => 'success',
                    'message' => 'Found next unlinked parameter combination',
                    'next_combo' => [
                        'parameter_values' => $nextCombo,
                        'parameter_group_string' => $nextComboString,
                        'parameter_group_index' => $nextIndex + 1, // 1-indexed for display
                        'total_combinations' => count($combinations)
                    ],
                    'template' => [
                        'id' => $template->id,
                        'name' => $template->name,
                        'parameters' => $parameters,
                        'images' => $template->images
                    ]
                ]);
            } else {
                // 所有组合都已链接
                return response()->json([
                    'status' => 'success',
                    'message' => 'All parameter combinations have been linked',
                    'next_combo' => null
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error finding next parameter combination: ' . $e->getMessage(), [
                'exception' => $e,
                'template_id' => $request->input('template_id'),
                'current_combo' => $request->input('current_combo')
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to find next parameter combination: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成所有可能的参数组合
     *
     * @param  array  $parameters
     * @return array
     */
    protected function generateParameterCombinations($parameters)
    {
        // 提取每个参数的名称和可能的值
        $paramValues = [];
        foreach ($parameters as $param) {
            if (!isset($param['name']) || !isset($param['values']) || !is_array($param['values'])) {
                continue;
            }
            $paramValues[$param['name']] = $param['values'];
        }
        
        // 递归生成所有组合
        return $this->generateCombinations($paramValues);
    }

    /**
     * 递归生成组合
     *
     * @param  array  $params
     * @param  array  $current
     * @param  array  $keys
     * @param  int    $index
     * @return array
     */
    protected function generateCombinations($params, $current = [], $keys = null, $index = 0)
    {
        if ($keys === null) {
            $keys = array_keys($params);
        }
        
        if ($index >= count($keys)) {
            return [$current];
        }
        
        $key = $keys[$index];
        $values = $params[$key];
        $combinations = [];
        
        foreach ($values as $value) {
            $newCurrent = $current;
            $newCurrent[$key] = $value;
            $newCombinations = $this->generateCombinations($params, $newCurrent, $keys, $index + 1);
            $combinations = array_merge($combinations, $newCombinations);
        }
        
        return $combinations;
    }

    /**
     * 格式化参数组合为字符串
     *
     * @param  array  $combo
     * @return string
     */
    protected function formatParameterCombo($combo)
    {
        $parts = [];
        foreach ($combo as $name => $value) {
            $parts[] = $name . '=' . $value;
        }
        return implode(';', $parts);
    }

    /**
     * Find the next unlinked parameter combination for a template
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function nextUnlinkedParameterCombo(Request $request, $templateId)
    {
        try {
            Log::info('Received request for next unlinked parameter combo', [
                'template_id' => $templateId,
                'current_combo' => $request->query('current_combo')
            ]);
            
            $currentCombo = $request->query('current_combo');
            if (!$currentCombo) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current parameter combination is required'
                ], 400);
            }
            
            $template = ProductTemplate::findOrFail($templateId);
            $parameters = $template->parameters;
            
            if (!is_array($parameters) || empty($parameters)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Template has no parameters'
                ], 404);
            }
            
            // Get linked parameter combinations
            $linkedCombos = DB::table('product_template_product')
                ->where('product_template_id', $templateId)
                ->where('is_parameter_combo', true)
                ->pluck('parameter_group')
                ->toArray();
            
            Log::info('Found linked combinations', [
                'template_id' => $templateId,
                'linked_combos_count' => count($linkedCombos)
            ]);
            
            // Parse current combination
            $currentParts = [];
            $currentItems = explode(';', $currentCombo);
            foreach ($currentItems as $item) {
                $parts = explode('=', $item);
                if (count($parts) === 2) {
                    $currentParts[$parts[0]] = $parts[1];
                }
            }
            
            // Generate all possible parameter combinations
            $allCombinations = $this->generateParameterCombinations($parameters);
            
            Log::info('Generated combinations', [
                'all_combinations_count' => count($allCombinations)
            ]);
            
            // Find index of current combination
            $currentIndex = -1;
            foreach ($allCombinations as $index => $combo) {
                $comboString = $this->formatParameterCombo($combo);
                if ($comboString === $currentCombo) {
                    $currentIndex = $index;
                    break;
                }
            }
            
            Log::info('Current combination index', [
                'current_index' => $currentIndex,
                'current_combo' => $currentCombo
            ]);
            
            // Find next unlinked combination
            $nextCombo = null;
            
            // First search after current index
            if ($currentIndex !== -1) {
                for ($i = $currentIndex + 1; $i < count($allCombinations); $i++) {
                    $combo = $allCombinations[$i];
                    $comboString = $this->formatParameterCombo($combo);
                    
                    if (!in_array($comboString, $linkedCombos)) {
                        $nextCombo = $comboString;
                        break;
                    }
                }
            }
            
            // If not found, search from beginning
            if ($nextCombo === null) {
                for ($i = 0; $i < ($currentIndex !== -1 ? $currentIndex : count($allCombinations)); $i++) {
                    $combo = $allCombinations[$i];
                    $comboString = $this->formatParameterCombo($combo);
                    
                    if (!in_array($comboString, $linkedCombos)) {
                        $nextCombo = $comboString;
                        break;
                    }
                }
            }
            
            Log::info('Next combination found', [
                'next_combo' => $nextCombo
            ]);
            
            if ($nextCombo) {
                return response()->json([
                    'status' => 'success',
                    'next_combo' => $nextCombo
                ]);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'All parameter combinations have been linked',
                    'next_combo' => null
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error finding next unlinked parameter combination: ' . $e->getMessage(), [
                'exception' => $e,
                'template_id' => $templateId,
                'current_combo' => $request->query('current_combo'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to find next parameter combination: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get parameter combination data with linked product info
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function parameterComboData(Request $request, $templateId)
    {
        try {
            $parameterCombo = $request->query('parameter_combo');
            if (!$parameterCombo) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter combination is required'
                ], 400);
            }
            
            $template = ProductTemplate::findOrFail($templateId);
            
            // Parse parameter combination
            $parameters = [];
            $items = explode(';', $parameterCombo);
            foreach ($items as $item) {
                $parts = explode('=', $item);
                if (count($parts) === 2) {
                    $parameters[$parts[0]] = $parts[1];
                }
            }
            
            // Get linked product for this parameter combination
            $linkedProduct = DB::table('product_template_product as ptp')
                ->select('p.*')
                ->join('products as p', 'ptp.product_id', '=', 'p.id')
                ->where('ptp.product_template_id', $templateId)
                ->where('ptp.parameter_group', $parameterCombo)
                ->where('ptp.is_parameter_combo', true)
                ->first();
            
            // Format the response
            $response = [
                'status' => 'success',
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name
                ],
                'parameter_combo' => $parameterCombo,
                'parameter_values' => $parameters,
                'linked_product' => $linkedProduct ? (array) $linkedProduct : null
            ];
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error getting parameter combo data: ' . $e->getMessage(), [
                'exception' => $e,
                'template_id' => $templateId,
                'parameter_combo' => $request->query('parameter_combo'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get parameter combo data: ' . $e->getMessage()
            ], 500);
        }
    }
}
