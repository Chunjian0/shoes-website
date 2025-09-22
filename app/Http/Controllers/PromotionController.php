<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PromotionController extends Controller
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage promotions')->except(['index']);
        $this->middleware('permission:view promotions')->only(['index']);
    }

    /**
     * 显示活动列表
     */
    public function index(): View
    {
        $promotions = Promotion::orderBy('priority', 'desc')->get();
        return view('promotions.index', compact('promotions'));
    }

    /**
     * 显示创建活动表单
     */
    public function create(): View
    {
        return view('promotions.create');
    }

    /**
     * 存储新活动
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // 验证请求数据
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'subtitle' => 'nullable|string',
                'description' => 'nullable|string',
                'button_text' => 'nullable|string|max:50',
                'button_link' => 'nullable|string|max:255',
                'background_color' => 'nullable|string|max:20',
                'text_color' => 'nullable|string|max:20',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'boolean',
                'priority' => 'integer|min:0',
                'image' => 'nullable|image|max:2048',
            ]);

            // 处理图片上传
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('promotions', 'public');
                $validated['image'] = $path;
            }

            // 创建活动
            Promotion::create($validated);

            return redirect()->route('promotions.index')
                ->with('success', '活动创建成功');
        } catch (\Exception $e) {
            Log::error('创建活动失败', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', '创建活动失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示编辑活动表单
     */
    public function edit(Promotion $promotion): View
    {
        return view('promotions.edit', compact('promotion'));
    }

    /**
     * 更新活动
     */
    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        try {
            // 验证请求数据
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'subtitle' => 'nullable|string',
                'description' => 'nullable|string',
                'button_text' => 'nullable|string|max:50',
                'button_link' => 'nullable|string|max:255',
                'background_color' => 'nullable|string|max:20',
                'text_color' => 'nullable|string|max:20',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'boolean',
                'priority' => 'integer|min:0',
                'image' => 'nullable|image|max:2048',
            ]);

            // 处理图片上传
            if ($request->hasFile('image')) {
                // 删除旧图片
                if ($promotion->image) {
                    Storage::disk('public')->delete($promotion->image);
                }
                
                // 保存新图片
                $path = $request->file('image')->store('promotions', 'public');
                $validated['image'] = $path;
            }

            // 更新活动
            $promotion->update($validated);

            return redirect()->route('promotions.index')
                ->with('success', '活动更新成功');
        } catch (\Exception $e) {
            Log::error('更新活动失败', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', '更新活动失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除活动
     */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        try {
            // 删除图片
            if ($promotion->image) {
                Storage::disk('public')->delete($promotion->image);
            }

            // 删除活动
            $promotion->delete();

            return redirect()->route('promotions.index')
                ->with('success', '活动删除成功');
        } catch (\Exception $e) {
            Log::error('删除活动失败', ['error' => $e->getMessage()]);
            return back()->with('error', '删除活动失败: ' . $e->getMessage());
        }
    }

    /**
     * 切换活动状态
     */
    public function toggleStatus(Promotion $promotion): JsonResponse
    {
        try {
            $promotion->is_active = !$promotion->is_active;
            $promotion->save();

            return response()->json([
                'success' => true,
                'is_active' => $promotion->is_active,
                'message' => $promotion->is_active ? '活动已启用' : '活动已禁用'
            ]);
        } catch (\Exception $e) {
            Log::error('切换活动状态失败', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => '操作失败: ' . $e->getMessage()
            ], 500);
        }
    }
}
