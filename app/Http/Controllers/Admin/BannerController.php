<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    /**
     * Display a listing of the banners.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $banners = Banner::with('media')->orderBy('order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new banner.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Store a newly created banner in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'media_id' => 'nullable|exists:media,id',
        ]);

        try {
            DB::beginTransaction();
            
            // 设置新轮播图的排序位置
            $maxOrder = Banner::max('order') ?? 0;
            $validated['order'] = $maxOrder + 1;
            
            // 创建轮播图记录
            $banner = Banner::create($validated);
            
            DB::commit();
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'banner_created',
                auth()->user()->email,
                ['banner_id' => $banner->id]
            ));
            
            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create banner: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create banner. ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified banner.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\View\View
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified banner in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'media_id' => 'nullable|exists:media,id',
        ]);

        try {
            $banner->update($validated);
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'banner_updated',
                auth()->user()->email,
                ['banner_id' => $banner->id]
            ));
            
            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update banner: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update banner. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified banner from storage.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Banner $banner)
    {
        try {
            $banner->delete();
            
            // 重新排序剩余的轮播图
            $remainingBanners = Banner::orderBy('order')->get();
            foreach ($remainingBanners as $index => $remainingBanner) {
                $remainingBanner->update(['order' => $index + 1]);
            }
            
            // 触发首页更新事件
            event(new \App\Events\HomepageUpdatedEvent(
                'banner_deleted',
                auth()->user()->email,
                ['banner_id' => $banner->id]
            ));
            
            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete banner: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete banner. ' . $e->getMessage());
        }
    }
    
    /**
     * Update the order of banners.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:banners,id',
        ]);
        
        try {
            $ids = $request->input('ids');
            
            foreach ($ids as $index => $id) {
                Banner::where('id', $id)->update(['order' => $index + 1]);
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
     * Toggle the active status of a banner.
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(Banner $banner)
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
     * Quickly create a banner from modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickCreate(Request $request)
    {
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
            $media = Media::create([
                'name' => $file->getClientOriginalName(),
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'path' => $file->store('banners', 'public'),
                'disk' => 'public',
                'collection_name' => 'banner_images',
                'model_type' => Banner::class,
            ]);
            
            // 设置新轮播图的排序位置
            $maxOrder = Banner::max('order') ?? 0;
            
            // 创建轮播图记录
            $banner = Banner::create([
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
     * Get a list of banners for display.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBannersList()
    {
        try {
            $banners = Banner::with('media')
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
} 