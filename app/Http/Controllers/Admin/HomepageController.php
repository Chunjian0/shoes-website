<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductTemplate;
use App\Services\HomepageStockService;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Carbon\Carbon;
use App\Models\Size;
use App\Events\HomepageUpdatedEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class HomepageController extends Controller
{
    /**
     * 显示首页管理页面
     *
     * @param HomepageStockService $stockService
     * @return \Illuminate\View\View
     */
    public function index(HomepageStockService $stockService)
    {
        // 获取所有首页设置
        $settings = $this->getHomepageSettingsForView();

        // 获取特色产品模板
        $featuredProducts = \App\Models\ProductTemplate::where('is_active', true)
            ->where('is_featured', true)
            ->with('category')
            ->orderBy('name')
            ->paginate(10);
        
        // 获取新品模板
        $newProducts = \App\Models\ProductTemplate::where('is_active', true)
            ->where('is_new_arrival', true)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // 获取促销产品模板
        $saleProducts = \App\Models\ProductTemplate::where('is_active', true)
            ->where('is_sale', true)
            ->with('category')
            ->orderBy('name')
            ->paginate(10);
        
        // 获取库存阈值
        $stockThreshold = $stockService->getMinStockThreshold();
        
        return view('admin.homepage.index', compact(
            'featuredProducts',
            'newProducts',
            'saleProducts',
            'stockThreshold',
            'settings'
        ));
    }

    /**
     * 更新库存阈值
     *
     * @param Request $request
     * @param HomepageStockService $stockService
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStockThreshold(Request $request, HomepageStockService $stockService)
    {
        $request->validate([
            'threshold' => 'required|integer|min:1'
        ]);
        
        $threshold = (int)$request->input('threshold');
        $success = $stockService->updateMinStockThreshold($threshold);
        
        if ($success) {
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'settings_updated',
                auth()->user()->email,
                ['threshold' => $threshold]
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Stock threshold updated successfully',
                'threshold' => $threshold
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update stock threshold'
        ], 500);
    }

    /**
     * 执行库存过滤
     *
     * @param HomepageStockService $stockService
     * @return \Illuminate\Http\JsonResponse
     */
    public function runStockFilter(HomepageStockService $stockService)
    {
        try {
            $removedProducts = $stockService->filterLowStockProducts();
            $removedCount = count($removedProducts);
            
            return response()->json([
                'success' => true,
                'message' => 'Filter completed successfully',
                'removed_count' => $removedCount,
                'removed_products' => $removedProducts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to run filter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取可添加到指定板块的产品模板列表
     *
     * @param string $type ('featured', 'new-arrival', 'sale')
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableProducts(string $type)
    {
        $validTypes = ['featured', 'new-arrival', 'sale'];
        if (!in_array($type, $validTypes)) {
            return response()->json(['success' => false, 'message' => 'Invalid section type provided.'], 400);
        }

        $column = 'is_' . str_replace('-', '_', $type);

        try {
            $templates = ProductTemplate::where('is_active', true)
                ->where(function (Builder $query) use ($column) {
                    $query->where($column, false)->orWhereNull($column);
                })
                ->with(['category:id,name', 'media'])
                ->orderBy('name')
                ->select(['id', 'name', 'category_id', 'images'])
                ->get()
                ->map(function ($template) {
                    $finalImageUrl = null;
                    // 1. Try getAllImages() method (if exists)
                    if (method_exists($template, 'getAllImages')) {
                        $imageUrls = $template->getAllImages();
                        if (!empty($imageUrls) && is_array($imageUrls[0]) && isset($imageUrls[0]['url'])) {
                            $imagePath = $imageUrls[0]['url'];
                            $finalImageUrl = Storage::url($imagePath);
                        } elseif (!empty($imageUrls) && is_string($imageUrls[0])) {
                            $imagePath = $imageUrls[0];
                            $finalImageUrl = Storage::url($imagePath);
                        }
                    }

                    // 2. Fallback to Spatie Media Library
                    if (!$finalImageUrl && method_exists($template, 'hasMedia') && $template->relationLoaded('media') && $template->hasMedia()) {
                        $finalImageUrl = $template->getFirstMediaUrl('default', 'thumbnail') ?: $template->getFirstMediaUrl();
                    }

                    // 3. Fallback to 'images' JSON field
                    if (!$finalImageUrl && !empty($template->images) && is_array($template->images)) {
                        if (isset($template->images[0])) {
                            $imagePath = null;
                            if (is_array($template->images[0]) && isset($template->images[0]['url'])) {
                                $imagePath = $template->images[0]['url'];
                            } elseif (is_string($template->images[0])) {
                                $imagePath = $template->images[0];
                            }
                            if ($imagePath) {
                                $finalImageUrl = Storage::url($imagePath);
                            }
                        }
                    }

                    // 4. Fallback placeholder
                    if (!$finalImageUrl) {
                        $finalImageUrl = asset('images/placeholder.png');
                    }

                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'category' => $template->category,
                        'images' => $finalImageUrl ? [[ 'thumbnail' => $finalImageUrl, 'url' => $finalImageUrl ]] : [],
                    ];
                });

            return response()->json(['success' => true, 'templates' => $templates]);

        } catch (\Exception $e) {
            Log::error("Error fetching available products for type {$type}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve available products.'], 500);
        }
    }

    /**
     * 将选定的产品模板添加到指定的首页板块
     *
     * @param string $type ('featured', 'new-arrival', 'sale')
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addProducts(string $type, Request $request)
    {
        $validTypes = ['featured', 'new-arrival', 'sale'];
        if (!in_array($type, $validTypes)) {
            return response()->json(['success' => false, 'message' => 'Invalid section type provided.'], 400);
        }

        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:product_templates,id' // 确保 ID 存在于 product_templates 表
        ]);

        $productIds = $request->input('product_ids');
        $column = 'is_' . str_replace('-', '_', $type); // 'new-arrival' -> 'is_new_arrival'

        try {
            $updatedCount = ProductTemplate::whereIn('id', $productIds)
                                ->where('is_active', true) // 确保只更新活跃的产品
                                ->update([$column => true]);

            if ($updatedCount > 0) {
                 // 触发首页更新事件
                 event(new HomepageUpdatedEvent(
                    "{$type}_products_added",
                    auth()->user()?->email ?? 'system', // Handle potential null user
                    ['product_ids' => $productIds, 'count' => $updatedCount]
                 ));

                 // 清除相关缓存，如果需要的话
                 // Cache::forget('homepage_featured_templates');
                 // Cache::forget('homepage_new_arrival_templates');
                 // Cache::forget('homepage_sale_templates');

                return response()->json([
                    'success' => true,
                    'message' => "Successfully added {$updatedCount} products to {$type} section."
                ]);
            } else {
                // 可能所有选中的产品都已经是该类型或不活跃
                 return response()->json([
                    'success' => true, // Still considered success as the request was processed
                    'message' => "No new products were added. They might already be in the section or inactive."
                ], 200); // Return 200 OK even if no rows affected
            }

        } catch (\Exception $e) {
            Log::error("Error adding products to type {$type}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to add products.'], 500);
        }
    }

    /**
     * 更新产品的精选状态
     *
     * @param Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFeatured(Request $request, \App\Models\Product $product)
    {
        try {
            $request->validate([
                'is_featured' => 'required|boolean'
            ]);
            
            $isFeatured = (bool)$request->input('is_featured');
            $product->update(['is_featured' => $isFeatured]);
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'product_featured_updated',
                auth()->user()->email,
                ['product_id' => $product->id, 'is_featured' => $isFeatured]
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Product featured status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product featured status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新产品的新品状态
     *
     * @param Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNewArrival(Request $request, \App\Models\Product $product)
    {
        try {
            $request->validate([
                'is_new_arrival' => 'required|boolean'
            ]);
            
            $isNewArrival = (bool)$request->input('is_new_arrival');
            $product->update(['is_new_arrival' => $isNewArrival]);
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'product_new_arrival_updated',
                auth()->user()->email,
                ['product_id' => $product->id, 'is_new_arrival' => $isNewArrival]
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Product new arrival status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product new arrival status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新产品的促销状态
     *
     * @param Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSale(Request $request, \App\Models\Product $product)
    {
        try {
            $request->validate([
                'is_sale' => 'required|boolean'
            ]);
            
            $isSale = (bool)$request->input('is_sale');
            $product->update(['is_sale' => $isSale]);
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'product_sale_updated',
                auth()->user()->email,
                ['product_id' => $product->id, 'is_sale' => $isSale]
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Product sale status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product sale status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取轮播图列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBannersList()
    {
        try {
            $banners = \App\Models\Banner::with('media')
                ->orderBy('order')
                ->get()
                ->map(function($banner) {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'subtitle' => $banner->subtitle,
                        'button_text' => $banner->button_text,
                        'button_link' => $banner->button_link,
                        'order' => $banner->order,
                        'is_active' => $banner->is_active,
                        'image_url' => $banner->getImageUrl(),
                    ];
                });
                
            return response()->json([
                'success' => true,
                'banners' => $banners
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get banners list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get banners list: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 更新轮播图排序
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBannerOrder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:banners,id',
        ]);
        
        try {
            $ids = $request->input('ids');
            
            foreach ($ids as $index => $id) {
                \App\Models\Banner::where('id', $id)->update(['order' => $index + 1]);
            }
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'banners_reordered',
                auth()->user()->email,
                []
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Banner order updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update banner order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 切换轮播图状态
     *
     * @param \App\Models\Banner $banner
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleBannerActive(\App\Models\Banner $banner)
    {
        try {
            $banner->update(['is_active' => !$banner->is_active]);
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'banner_status_updated',
                auth()->user()->email,
                ['banner_id' => $banner->id, 'is_active' => $banner->is_active]
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Banner status updated successfully',
                'is_active' => $banner->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle banner status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle banner status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 快速创建轮播图
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickCreateBanner(Request $request)
    {
        Log::info('快速创建轮播图', ['request' => $request->all()]);
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'file' => 'required|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);
        
        try {
            DB::beginTransaction();
            
            // 上传图片
            $file = $request->file('file');
            $media = \App\Models\Media::create([
                'name' => $file->getClientOriginalName(),
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'path' => $file->store('banners', 'public'),
                'disk' => 'public',
                'collection_name' => 'banner_images',
                'model_type' => \App\Models\Banner::class,
            ]);
            
            // 设置新轮播图的排序位置
            $maxOrder = \App\Models\Banner::max('order') ?? 0;
            
            // 创建轮播图记录
            $banner = \App\Models\Banner::create([
                'title' => $validated['title'],
                'subtitle' => $validated['subtitle'] ?? null,
                'button_text' => $validated['button_text'] ?? null,
                'button_link' => $validated['button_link'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'media_id' => $media->id,
                'order' => $maxOrder + 1,
            ]);
            
            // 更新媒体关联
            $media->update([
                'model_id' => $banner->id
            ]);
            
            DB::commit();
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'banner_created',
                auth()->user()->email,
                ['banner_id' => $banner->id]
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Banner created successfully',
                'banner' => [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'button_text' => $banner->button_text,
                    'button_link' => $banner->button_link,
                    'order' => $banner->order,
                    'is_active' => $banner->is_active,
                    'image_url' => $banner->getImageUrl(),
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 删除轮播图
     *
     * @param \App\Models\Banner $banner
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyBanner(\App\Models\Banner $banner)
    {
        try {
            $banner->delete();
            
            // 重新排序剩余的轮播图
            \App\Models\Banner::reorder();
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'banner_deleted',
                auth()->user()->email,
                ['banner_id' => $banner->id]
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to delete banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the featured products order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFeaturedOrder(Request $request)
    {
        $productIds = $request->input('productIds', []);
        
        try {
            foreach ($productIds as $index => $productId) {
                Product::where('id', $productId)->update(['featured_order' => $index + 1]);
            }
            
            // 触发首页更新事件
            $user = auth()->user() ? auth()->user()->email : 'system';
            event(new HomepageUpdatedEvent('featured_order_updated', $user, ['products' => $productIds]));
            
            return response()->json([
                'success' => true,
                'message' => 'Featured products order updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update featured products order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the new arrival products order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNewArrivalOrder(Request $request)
    {
        $productIds = $request->input('productIds', []);
        
        try {
            foreach ($productIds as $index => $productId) {
                Product::where('id', $productId)->update(['new_arrival_order' => $index + 1]);
            }
            
            // 触发首页更新事件
            $user = auth()->user() ? auth()->user()->email : 'system';
            event(new HomepageUpdatedEvent('new_arrival_order_updated', $user, ['products' => $productIds]));
            
            return response()->json([
                'success' => true,
                'message' => 'New arrival products order updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update new arrival products order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the sale products order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSaleOrder(Request $request)
    {
        $productIds = $request->input('productIds', []);
        
        try {
            foreach ($productIds as $index => $productId) {
                Product::where('id', $productId)->update(['sale_order' => $index + 1]);
            }
            
            // 触发首页更新事件
            $user = auth()->user() ? auth()->user()->email : 'system';
            event(new HomepageUpdatedEvent('sale_order_updated', $user, ['products' => $productIds]));
            
            return response()->json([
                'success' => true,
                'message' => 'Sale products order updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sale products order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取产品列表，用于添加到首页各个区域
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsList(Request $request)
    {
        $perPage = 10;
        $search = $request->input('search', '');
        $type = $request->input('type', ''); // 'featured', 'new-arrival', 'sale'
        $page = $request->input('page', 1);
        $all = $request->boolean('all', false); // 是否获取所有产品
        
        try {
            // 查询所有活动的 ProductTemplate
            $query = \App\Models\ProductTemplate::query()
                ->with(['category', 'media'])
                ->where('is_active', true);
                
            // 搜索条件
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            // 获取总数
            $total = $query->count();
            
            // 获取所有产品或者分页
            if ($all) {
                $templates = $query->orderBy('name')->get();
            } else {
                $templates = $query->orderBy('name')
                    ->skip(($page - 1) * $perPage)
                    ->take($perPage)
                    ->get();
            }
            
            // 格式化输出
            $formattedTemplates = $templates->map(function($template) {
                $image = null;
                if ($template->relationLoaded('media') && $template->media->isNotEmpty()) {
                    $image = asset('storage/' . $template->media->first()->path);
                } else if (!empty($template->images) && is_array($template->images) && count($template->images) > 0) {
                    $firstImage = $template->images[0];
                    if (isset($firstImage['url'])) {
                        $image = asset('storage/' . $firstImage['url']);
                    }
                }
                
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'category' => $template->category ? $template->category->name : 'No Category',
                    'category_id' => $template->category_id,
                    'image' => $image ?? asset('images/no-image.png'),
                    'parameters' => count($template->parameters ?? []),
                    'active' => $template->is_active,
                    'is_featured' => $template->is_featured,
                    'is_new_arrival' => $template->is_new_arrival,
                    'is_sale' => $template->is_sale,
                ];
            });
            
            // 生成分页数据
            $pagination = null;
            if (!$all) {
                $pagination = [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                    'from' => ($page - 1) * $perPage + 1,
                    'to' => min($page * $perPage, $total),
                    'prev_page_url' => $page > 1 ? null : null,
                    'next_page_url' => $page < ceil($total / $perPage) ? null : null
                ];
            }
            
            $response = [
                'success' => true,
                'products' => $formattedTemplates
            ];
            
            if ($pagination) {
                $response['pagination'] = $pagination;
            }
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Failed to get products list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'type' => $type,
                'search' => $search
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products list: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新轮播设置
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCarouselSettings(Request $request)
    {
        $validated = $request->validate([
            'autoplay' => 'nullable|boolean',
            'delay' => 'nullable|integer|min:1000|max:10000',
            'transition' => 'nullable|string|in:slide,fade,zoom',
            'show_navigation' => 'nullable|boolean',
            'show_indicators' => 'nullable|boolean',
        ]);
        
        try {
            DB::beginTransaction();
            
            // 获取所有设置
            $settings = [
                'autoplay' => $request->has('autoplay') ? (bool)$request->input('autoplay') : true,
                'delay' => $request->input('delay', 5000),
                'transition' => $request->input('transition', 'slide'),
                'show_navigation' => $request->has('show_navigation') ? (bool)$request->input('show_navigation') : true,
                'show_indicators' => $request->has('show_indicators') ? (bool)$request->input('show_indicators') : true,
            ];
            
            // 轮播设置的描述映射
            $settingsInfo = [
                'autoplay' => [
                    'type' => 'boolean',
                    'label' => 'Autoplay Carousel',
                    'description' => 'Whether the carousel should automatically rotate',
                ],
                'delay' => [
                    'type' => 'integer',
                    'label' => 'Transition Delay',
                    'description' => 'Time in milliseconds between slides',
                ],
                'transition' => [
                    'type' => 'string',
                    'label' => 'Transition Effect',
                    'description' => 'Visual effect when changing slides',
                    'options' => ['slide', 'fade', 'zoom'],
                ],
                'show_navigation' => [
                    'type' => 'boolean',
                    'label' => 'Show Navigation Arrows',
                    'description' => 'Display the next/previous buttons',
                ],
                'show_indicators' => [
                    'type' => 'boolean',
                    'label' => 'Show Indicators',
                    'description' => 'Display the slide position indicators',
                ],
            ];
            
            // 更新轮播设置
            foreach ($settings as $key => $value) {
                $settingKey = 'carousel_' . $key;
                $info = $settingsInfo[$key];
                
                $this->upsertSetting(
                    'homepage',
                    $settingKey,
                    $value,
                    $info['type'],
                    $info['label'],
                    $info['description'],
                    $info['options'] ?? null,
                    true
                );
            }
            
            DB::commit();
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'carousel_settings_updated',
                auth()->user()->email,
                $settings
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Carousel settings updated successfully',
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update carousel settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update carousel settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取轮播设置
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCarouselSettings()
    {
        try {
            $settings = DB::table('settings')
                ->where('group', 'homepage')
                ->whereIn('key', [
                    'carousel_autoplay',
                    'carousel_delay',
                    'carousel_transition',
                    'carousel_show_navigation',
                    'carousel_show_indicators'
                ])
                ->pluck('value', 'key')
                ->toArray();
            
            // 重新格式化键名
            $formattedSettings = [];
            foreach ($settings as $key => $value) {
                $formattedKey = str_replace('carousel_', '', $key);
                $formattedSettings[$formattedKey] = $value;
            }
            
            // 设置默认值
            $defaultSettings = [
                'autoplay' => true,
                'delay' => 5000,
                'transition' => 'slide',
                'show_navigation' => true,
                'show_indicators' => true
            ];
            
            $finalSettings = array_merge($defaultSettings, $formattedSettings);
            
            return response()->json([
                'success' => true,
                'settings' => $finalSettings
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get carousel settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get carousel settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get homepage general settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHomepageSettings()
    {
        try {
            $settings = DB::table('settings')
                ->where('group', 'homepage')
                ->get()
                ->keyBy('key')
                ->map(function ($item) {
                    // Convert boolean strings to actual booleans
                    if ($item->value === '0' || $item->value === '1') {
                        return (bool) (int) $item->value;
                    }
                    
                    // Convert numeric strings to integers
                    if (is_numeric($item->value)) {
                        return (int) $item->value;
                    }
                    
                    return $item->value;
                })
                ->toArray();
            
            // Set default values if not found
            $defaults = [
                'site_title' => 'Optic System',
                'site_description' => 'Your one-stop shop for quality optical products',
                'products_per_page' => 12,
                'new_product_days' => 30,
                'layout' => 'standard',
                'show_promotion' => true,
                'show_brands' => true,
                'auto_add_new_products' => true,
                'auto_add_sale_products' => true,
                'featured_products_title' => 'Featured Products',
                'featured_products_subtitle' => 'Our carefully selected premium products known for exceptional quality',
                'featured_products_button_text' => 'View All Featured Products',
                'featured_products_button_link' => '/products?featured=true',
                'featured_products_banner_title' => 'Featured Products Collection',
                'featured_products_banner_subtitle' => 'Discover our selection of premium quality shoes',
                'new_products_title' => 'New Arrivals',
                'new_products_subtitle' => 'Check out our latest products',
                'new_products_button_text' => 'View All New Arrivals',
                'new_products_button_link' => '/products?new=true',
                'sale_products_title' => 'On Sale',
                'sale_products_subtitle' => 'Great deals on quality products',
                'sale_products_button_text' => 'View All Sale Items',
                'sale_products_button_link' => '/products?sale=true'
            ];
            
            foreach ($defaults as $key => $value) {
                if (!isset($settings[$key])) {
                    $settings[$key] = $value;
                }
            }
            
            return response()->json([
                'success' => true, 
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve homepage settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update homepage general settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateHomepageSettings(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // 获取 'settings' 数组中的数据
            $submittedSettings = $request->input('settings', []);
            if (!is_array($submittedSettings)) {
                // 如果输入不是数组，返回错误
                return response()->json(['success' => false, 'message' => 'Invalid settings format.'], 400);
            }
            
            // 设置默认值
            $defaults = [
                'site_title' => 'Optic System',
                'site_description' => 'Your one-stop shop for quality optical products',
                'products_per_page' => 12,
                'new_product_days' => 30,
                'layout' => 'standard',
                'show_promotion' => true,
                'show_brands' => true,
                'auto_add_new_products' => true,
                'auto_add_sale_products' => true,
                'featured_products_title' => 'Featured Products',
                'featured_products_subtitle' => 'Our carefully selected premium products known for exceptional quality',
                'featured_products_button_text' => 'View All Featured Products',
                'featured_products_button_link' => '/products?featured=true',
                'featured_products_banner_title' => 'Featured Products Collection',
                'featured_products_banner_subtitle' => 'Discover our selection of premium quality shoes',
                'new_products_title' => 'New Arrivals',
                'new_products_subtitle' => 'Check out our latest products',
                'new_products_button_text' => 'View All New Arrivals',
                'new_products_button_link' => '/products?new=true',
                'sale_products_title' => 'On Sale',
                'sale_products_subtitle' => 'Great deals on quality products',
                'sale_products_button_text' => 'View All Sale Items',
                'sale_products_button_link' => '/products?sale=true'
            ];
            
            // 应用默认值 (这部分可能不再需要，因为我们只处理提交的字段)
            /*
            foreach ($defaults as $key => $defaultValue) {
                if (!isset($settings[$key])) {
                    $settings[$key] = $defaultValue;
                }
            }
            */
            
            // 验证数据 (只验证提交的数据)
            // $this->validateSettings($submittedSettings); // 验证逻辑可能需要调整
            
            // 主页设置的描述映射
            $settingsInfo = [
                'site_title' => [
                    'type' => 'string',
                    'label' => 'Site Title',
                    'description' => 'The title displayed in the browser tab and SEO',
                ],
                'site_description' => [
                    'type' => 'text',
                    'label' => 'Site Description',
                    'description' => 'Brief description of the website for SEO purposes',
                ],
                'products_per_page' => [
                    'type' => 'integer',
                    'label' => 'Products Per Page',
                    'description' => 'Number of products to display per page',
                ],
                'new_product_days' => [
                    'type' => 'integer',
                    'label' => 'New Product Days',
                    'description' => 'Number of days a product is considered new',
                ],
                'layout' => [
                    'type' => 'string',
                    'label' => 'Layout Style',
                    'description' => 'Homepage layout presentation style',
                    'options' => ['standard', 'modern', 'minimal', 'grid'],
                ],
                'show_promotion' => [
                    'type' => 'boolean',
                    'label' => 'Show Promotions',
                    'description' => 'Display promotional sections on homepage',
                ],
                'show_brands' => [
                    'type' => 'boolean',
                    'label' => 'Show Brands',
                    'description' => 'Display brand logos on homepage',
                ],
                'auto_add_new_products' => [
                    'type' => 'boolean',
                    'label' => 'Auto-add New Products',
                    'description' => 'Automatically add new products to homepage',
                ],
                'auto_add_sale_products' => [
                    'type' => 'boolean',
                    'label' => 'Auto-add Sale Products',
                    'description' => 'Automatically add sale products to homepage',
                ],
                'featured_products_banner_title' => [
                    'type' => 'string',
                    'label' => 'Featured Products Banner Title',
                    'description' => 'The title displayed in the featured products banner',
                ],
                'featured_products_banner_subtitle' => [
                    'type' => 'text',
                    'label' => 'Featured Products Banner Subtitle',
                    'description' => 'The subtitle displayed in the featured products banner',
                ],
                'featured_products_title' => [
                    'type' => 'string',
                    'label' => 'Featured Products Title',
                    'description' => 'The title displayed in the featured products section',
                ],
                'featured_products_subtitle' => [
                    'type' => 'text',
                    'label' => 'Featured Products Subtitle',
                    'description' => 'The subtitle displayed in the featured products section',
                ],
                'featured_products_button_text' => [
                    'type' => 'string',
                    'label' => 'Featured Products Button Text',
                    'description' => 'The text displayed on the featured products button',
                ],
                'featured_products_button_link' => [
                    'type' => 'string',
                    'label' => 'Featured Products Button Link',
                    'description' => 'The link for the featured products button',
                ],
                'new_products_title' => [
                    'type' => 'string',
                    'label' => 'New Products Title',
                    'description' => 'The title displayed in the new products section',
                ],
                'new_products_subtitle' => [
                    'type' => 'text',
                    'label' => 'New Products Subtitle',
                    'description' => 'The subtitle displayed in the new products section',
                ],
                'new_products_button_text' => [
                    'type' => 'string',
                    'label' => 'New Products Button Text',
                    'description' => 'The text displayed on the new products button',
                ],
                'new_products_button_link' => [
                    'type' => 'string',
                    'label' => 'New Products Button Link',
                    'description' => 'The link for the new products button',
                ],
                'sale_products_title' => [
                    'type' => 'string',
                    'label' => 'Sale Products Title',
                    'description' => 'The title displayed in the sale products section',
                ],
                'sale_products_subtitle' => [
                    'type' => 'text',
                    'label' => 'Sale Products Subtitle',
                    'description' => 'The subtitle displayed in the sale products section',
                ],
                'sale_products_button_text' => [
                    'type' => 'string',
                    'label' => 'Sale Products Button Text',
                    'description' => 'The text displayed on the sale products button',
                ],
                'sale_products_button_link' => [
                    'type' => 'string',
                    'label' => 'Sale Products Button Link',
                    'description' => 'The link for the sale products button',
                ],
            ];
            
            // Update settings in database
            foreach ($submittedSettings as $key => $value) {
                // 现在 $key 是 'featured_title' 等, $value 是对应的值
                
                // Check if setting info exists
                if (!isset($settingsInfo[$key])) {
                    // If not defined in the map, create default info
                    $settingsInfo[$key] = [
                        'type' => 'string',
                        'label' => ucwords(str_replace('_', ' ', $key)),
                        'description' => 'Homepage setting for ' . str_replace('_', ' ', $key),
                    ];
                }
                
                $info = $settingsInfo[$key];
                
                $this->upsertSetting(
                    'homepage',
                    $key,
                    $value,
                    $info['type'],
                    $info['label'],
                    $info['description'],
                    $info['options'] ?? null,
                    true
                );
            }
            
            DB::commit();

            // 清除相关的 API 缓存
            Cache::forget('homepage_settings_api');
            Log::info('Homepage settings cache cleared (key: homepage_settings_api)');
            
            // Trigger homepage updated event
            event(new \App\Events\HomepageUpdatedEvent(
                'homepage_settings_updated',
                auth()->user()->email,
                $submittedSettings // 记录实际更新的设置
            ));
            
            return response()->json([
                'success' => true,
                'message' => 'Homepage settings updated successfully',
                'settings' => $submittedSettings // 返回实际更新的设置
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update homepage settings: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update homepage settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 验证提交的设置数据
     */
    private function validateSettings($settings)
    {
        $validators = [
            'site_title' => ['nullable', 'string', 'max:100'],
            'site_description' => ['nullable', 'string', 'max:255'],
            'products_per_page' => ['nullable', 'integer', 'min:4', 'max:48'],
            'new_product_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'layout' => ['nullable', 'string', 'in:standard,modern,minimal,grid'],
        ];
        
        $data = [];
        $rules = [];
        
        foreach ($validators as $key => $validations) {
            if (isset($settings[$key])) {
                $data[$key] = $settings[$key];
                $rules[$key] = $validations;
            }
        }
        
        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);
        
        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    /**
     * 添加或更新设置
     * 
     * @param string $group 设置组
     * @param string $key 设置键
     * @param mixed $value 设置值
     * @param string $type 值类型
     * @param string $label 显示名称
     * @param string $description 描述
     * @param array|null $options 可选值
     * @param bool $isPublic 是否公开
     * @return bool 操作是否成功
     */
    protected function upsertSetting($group, $key, $value, $type, $label, $description, $options = null, $isPublic = true)
    {
        try {
            // 首先检查设置是否已存在
            $existingSetting = DB::table('settings')
                ->where('group', $group)
                ->where('key', $key)
                ->first();
            
            // 转换布尔值为数据库存储格式
            if ($type === 'boolean') {
                // 处理各种可能的布尔值格式
                if (is_string($value)) {
                    $value = in_array(strtolower($value), ['true', '1', 'yes', 'y', 'on']) ? '1' : '0';
                } else {
                    $value = $value ? '1' : '0';
                }
                
                // 添加调试日志
                Log::info("Setting boolean value for {$key}: " . var_export($value, true));
            }
            
            // 转换选项为JSON字符串
            if ($options !== null && is_array($options)) {
                $options = json_encode($options);
            }
            
            // 记录每次设置更新
            Log::info("Updating setting [{$group}.{$key}] with value: " . var_export($value, true));
            
            if ($existingSetting) {
                // 如果存在，只更新值
                return DB::table('settings')
                    ->where('group', $group)
                    ->where('key', $key)
                    ->update([
                        'value' => $value,
                        'updated_at' => now()
                    ]) > 0;
            } else {
                // 如果不存在，插入全新记录
                return DB::table('settings')->insert([
                    'group' => $group,
                    'key' => $key,
                    'value' => $value,
                    'type' => $type,
                    'label' => $label,
                    'description' => $description,
                    'options' => $options,
                    'is_public' => $isPublic,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to upsert setting: ' . $e->getMessage(), [
                'group' => $group,
                'key' => $key,
                'value' => $value,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Helper method to get formatted homepage settings for the view.
     *
     * @return array
     */
    private function getHomepageSettingsForView(): array
    {
        try {
            $dbSettings = DB::table('settings')
                ->where('group', 'homepage')
                ->get()
                ->keyBy('key')
                ->map(function ($item) {
                    // Convert boolean strings to actual booleans if applicable
                    if ($item->value === '0' || $item->value === '1') {
                        // Consider type if available
                        // if (isset($item->type) && $item->type === 'boolean') {
                        //     return (bool)(int)$item->value;
                        // }
                        // Heuristic: Assume '0'/'1' are boolean unless proven otherwise
                        return (bool)(int)$item->value;
                    }
                    
                    // Convert numeric strings to integers if applicable
                    // if (isset($item->type) && $item->type === 'integer' && is_numeric($item->value)) {
                    //     return (int)$item->value;
                    // }
                    if (is_numeric($item->value)) {
                        return (int)$item->value;
                    }
                    
                    return $item->value; // Return as string otherwise
                })
                ->toArray();
            
            // Define default values
            $defaults = [
                'featured_title' => 'Featured Products', // Renamed from featured_products_title
                'featured_subtitle' => 'Our carefully selected premium products...', // Renamed
                'featured_button_text' => 'View All Featured', // Renamed
                'featured_button_link' => '/products?featured=true', // Renamed
                'new_products_title' => 'New Arrivals',
                'new_products_subtitle' => 'Check out our latest products',
                'new_products_button_text' => 'View All New',
                'new_products_button_link' => '/products?new=true',
                'sale_products_title' => 'On Sale',
                'sale_products_subtitle' => 'Great deals on quality products',
                'sale_products_button_text' => 'View All Sale',
                'sale_products_button_link' => '/products?sale=true'
                // Add other necessary defaults here...
            ];
            
            // Merge database settings with defaults, giving priority to database values
            $settings = array_merge($defaults, $dbSettings);
            
            // Ensure specific keys needed by the view exist, even if not in DB or defaults
            $requiredKeys = [
                'featured_title', 'featured_subtitle', 'featured_button_text', 'featured_button_link',
                'new_products_title', 'new_products_subtitle', 'new_products_button_text', 'new_products_button_link',
                'sale_products_title', 'sale_products_subtitle', 'sale_products_button_text', 'sale_products_button_link'
            ];
            foreach ($requiredKeys as $key) {
                if (!isset($settings[$key])) {
                    $settings[$key] = ''; // Set empty string if completely missing
                }
            }

            return $settings;
        } catch (\Exception $e) {
            Log::error('Failed to get homepage settings for view: ' . $e->getMessage());
            // Return only defaults in case of error
            return $this->getDefaultHomepageSettings(); // Assuming you have a method for defaults
        }
    }
    
    // Optional: Helper to get just the defaults if needed
    private function getDefaultHomepageSettings(): array
    {
         return [
                'featured_title' => 'Featured Products', 
                'featured_subtitle' => 'Our carefully selected premium products...', 
                'featured_button_text' => 'View All Featured', 
                'featured_button_link' => '/products?featured=true', 
                'new_products_title' => 'New Arrivals',
                'new_products_subtitle' => 'Check out our latest products',
                'new_products_button_text' => 'View All New',
                'new_products_button_link' => '/products?new=true',
                'sale_products_title' => 'On Sale',
                'sale_products_subtitle' => 'Great deals on quality products',
                'sale_products_button_text' => 'View All Sale',
                'sale_products_button_link' => '/products?sale=true'
            ];
    }

    /**
     * 获取指定板块的产品模板列表 (用于 AJAX 刷新)
     *
     * @param string $type ('featured', 'new-arrival', 'sale')
     * @param Request $request (用于分页等参数)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSectionProducts(string $type, Request $request)
    {
        $validTypes = ['featured', 'new-arrival', 'sale'];
        if (!in_array($type, $validTypes)) {
            return response()->json(['success' => false, 'message' => 'Invalid section type provided.'], 400);
        }

        $column = 'is_' . str_replace('-', '_', $type);
        $perPage = $request->input('per_page', 10);

        try {
            $query = ProductTemplate::where('is_active', true)
                ->where($column, true)
                ->with(['category:id,name', 'media']);

            // 根据类型设置排序
            if ($type === 'new-arrival') {
                $query->orderBy('created_at', 'desc');
            } else {
                $orderColumn = $type . '_order';
                if (Schema::hasColumn('product_templates', $orderColumn)) {
                    $query->orderBy($orderColumn, 'asc');
                } else {
                    $query->orderBy('name', 'asc');
                }
            }

            $products = $query->paginate($perPage);

            $formattedProducts = $products->getCollection()->map(function ($template) {
                 $finalImageUrl = null;
                 // Try Spatie Media Library
                 if (method_exists($template, 'hasMedia') && $template->relationLoaded('media') && $template->hasMedia()) {
                     $finalImageUrl = $template->getFirstMediaUrl('default', 'thumbnail') ?: $template->getFirstMediaUrl();
                 }
                 // Fallback to 'images' JSON field
                 elseif (!empty($template->images) && is_array($template->images)) {
                     if (isset($template->images[0])) {
                        $imagePath = null;
                        if (is_array($template->images[0]) && isset($template->images[0]['url'])) {
                            $imagePath = $template->images[0]['url'];
                        } elseif (is_string($template->images[0])) {
                            $imagePath = $template->images[0];
                        }
                        if ($imagePath) {
                           $finalImageUrl = Storage::url($imagePath);
                        }
                     }
                 }
                 // Fallback placeholder
                 $finalImageUrl = $finalImageUrl ?: asset('images/placeholder.png');

                 return [
                     'id' => $template->id,
                     'name' => $template->name,
                     'category' => $template->category,
                     'parameters' => $template->parameters,
                     'is_active' => $template->is_active,
                     'created_at_formatted' => $template->created_at ? $template->created_at->format('M d, Y') : 'N/A',
                     'image_url' => $finalImageUrl,
                 ];
            });

            return response()->json([
                'success' => true,
                'products' => $formattedProducts,
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching section products for type {$type}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to retrieve section products.'], 500);
        }
    }
}
