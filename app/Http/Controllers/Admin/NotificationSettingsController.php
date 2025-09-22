<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\HomepageStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\HomepageUpdatedEvent;

class NotificationSettingsController extends Controller
{
    /**
     * 显示通知设置页面
     *
     * @param HomepageStockService $stockService
     * @return \Illuminate\View\View
     */
    public function index(HomepageStockService $stockService)
    {
        // 获取所有用户
        $users = User::all();
        
        // 获取低库存通知接收人
        $lowStockReceivers = [];
        $receiversSetting = Setting::where('key', 'low_stock_notification_receivers')->first();
        if ($receiversSetting && !empty($receiversSetting->value)) {
            $lowStockReceivers = json_decode($receiversSetting->value, true);
        }
        
        // 获取库存阈值
        $minStockThreshold = $stockService->getMinStockThreshold();
        
        return view('admin.notification_settings', compact('users', 'lowStockReceivers', 'minStockThreshold'));
    }
    
    /**
     * 更新通知设置
     *
     * @param Request $request
     * @param HomepageStockService $stockService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, HomepageStockService $stockService)
    {
        try {
            // 更新低库存通知接收人
            $receivers = $request->input('receivers.low_stock_notification_receivers', []);
            Setting::setValue('low_stock_notification_receivers', json_encode($receivers));
            
            // 更新库存阈值
            $threshold = (int)$request->input('min_stock_threshold', 5);
            $stockService->updateMinStockThreshold($threshold);
            
            // 触发首页更新事件
            event(new HomepageUpdatedEvent(
                'notification_settings_updated',
                'Notification settings have been updated'
            ));
            
            return redirect()->route('admin.settings.notifications')
                ->with('success', 'Notification settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update notification settings: ' . $e->getMessage());
            return redirect()->route('admin.settings.notifications')
                ->with('error', 'Failed to update notification settings. Please try again.');
        }
    }
} 