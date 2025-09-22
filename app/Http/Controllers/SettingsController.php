<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Services\SettingsService;

class SettingsController extends Controller
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->middleware('auth');
        $this->middleware('permission:view company settings')->only(['company']);
        $this->middleware('permission:manage company settings')->only(['updateCompany']);
        $this->middleware('permission:view notification settings')->only(['notifications']);
        $this->middleware('permission:manage notification settings')->only(['updateNotifications', 'toggleEmail']);
        $this->middleware('permission:view notification history')->only(['notificationHistory']);
        $this->middleware('permission:manage notification templates')->only(['messageTemplates', 'updateMessageTemplates']);
        $this->middleware('permission:view system settings')->only(['system', 'autoPurchase']);
        $this->middleware('permission:manage system settings')->only(['updateSystem', 'updateAutoPurchase']);
        $this->settingsService = $settingsService;
    }

    public function company(): View
    {
        try {
            $settings = Setting::where('group', 'company')
                ->get()
                ->pluck('value', 'key')
                ->toArray();

            return view('settings.company', compact('settings'));
        } catch (\Exception $e) {
            Log::error('Failed to get company settings: ' . $e->getMessage());
            return view('settings.company', [
                'settings' => [],
                'error' => 'An error occurred while obtaining settings information'
            ]);
        }
    }

    public function updateCompany(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $validated = $request->validate([
                'company_name' => 'required|string|max:255',
                'company_address' => 'required|string|max:1000',
                'company_phone' => 'required|string|max:20',
                'company_email' => 'required|email|max:255',
                'company_tax_number' => 'required|string|max:50',
                'invoice_prefix' => 'nullable|string|max:10',
                'invoice_footer' => 'nullable|string|max:1000',
                'company_logo' => 'nullable|exists:media,id',
            ]);

            foreach ($validated as $key => $value) {
                if ($key === 'company_logo') {
                    continue;
                }
                Setting::updateOrCreate(
                    ['key' => $key, 'group' => 'company'],
                    ['value' => $value]
                );
            }

            // deal withLogo
            if ($request->has('company_logo')) {
                Setting::updateOrCreate(
                    ['key' => 'company_logo', 'group' => 'company'],
                    ['value' => $validated['company_logo']]
                );
            }

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Company settings have been updated.']);
            }

            return redirect()
                ->route('settings.company')
                ->with('success', 'Company settings have been updated.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            return redirect()
                ->route('settings.company')
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to update the company settings: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while updating settings'], 500);
            }
            return redirect()
                ->route('settings.company')
                ->with('error', 'An error occurred while updating settings')
                ->withInput();
        }
    }

    public function notifications(): View
    {
        try {
            // 获取通知设置
            $settings = Setting::where('group', 'notifications')
                ->get()
                ->pluck('value', 'key')
                ->toArray();
            
            // 获取通知类型
            $notificationTypes = [
                'product_created' => '新商品创建通知',
                'quality_inspection_created' => '质检记录创建通知',
                'inventory_alert' => '库存警告通知',
                'purchase_created' => '采购单创建通知',
                'payment_status_changed' => '付款状态更新通知',
                'system_alert' => '系统警告通知',
                'auto_purchase' => '自动采购通知',
            ];
            
            // 获取通知接收人
            $receivers = [];
            foreach ($notificationTypes as $type => $label) {
                $settingKey = 'notification_receivers_' . $type;
                $receivers[$type] = isset($settings[$settingKey]) ? json_decode($settings[$settingKey], true) : [];
            }
            
            // 如果auto_purchase类型没有接收人，尝试从系统设置中获取
            if (empty($receivers['auto_purchase'])) {
                $autoPurchaseSettings = $this->settingsService->getAutoPurchaseSettings();
                if (isset($autoPurchaseSettings['auto_purchase_notify_users'])) {
                    $userIds = json_decode($autoPurchaseSettings['auto_purchase_notify_users'], true);
                    $users = User::whereIn('id', $userIds)->get();
                    $receivers['auto_purchase'] = $users->pluck('email')->toArray();
                }
            }
            
            // 检查电子邮件通知是否启用
            $emailEnabled = isset($settings['email_enabled']) && $settings['email_enabled'] === 'true';
            
            // 获取所有活跃用户
            $users = \App\Models\User::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
                
            // 获取历史消息记录
            $notificationHistory = \App\Models\NotificationLog::orderBy('created_at', 'desc')
                ->paginate(10);

            return view('settings.notifications', compact('notificationTypes', 'receivers', 'emailEnabled', 'users', 'notificationHistory'));
        } catch (\Exception $e) {
            Log::error('获取通知设置失败: ' . $e->getMessage());
            return view('settings.notifications', [
                'notificationTypes' => [
                    'product_created' => '新商品创建通知',
                    'quality_inspection_created' => '质检记录创建通知',
                    'inventory_alert' => '库存警告通知',
                    'purchase_created' => '采购单创建通知',
                    'payment_status_changed' => '付款状态更新通知',
                    'system_alert' => '系统警告通知',
                    'auto_purchase' => '自动采购通知',
                ],
                'receivers' => [],
                'emailEnabled' => false,
                'users' => [],
                'error' => '获取通知设置失败'
            ]);
        }
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        try {
            // 验证接收到的数据
            $validated = $request->validate([
                'receivers' => 'nullable|array',
                'receivers.*' => 'nullable|array',
                'receivers.*.*' => 'nullable|email',
            ]);
            
            // 更新每种通知类型的接收人
            if (isset($validated['receivers'])) {
                foreach ($validated['receivers'] as $type => $emails) {
                    $settingKey = 'notification_receivers_' . $type;
                    Setting::updateOrCreate(
                        ['key' => $settingKey, 'group' => 'notifications'],
                        ['value' => json_encode($emails ?? [])]
                    );
                    
                    // 如果是自动采购通知，同步到auto_purchase_notify_users设置
                    if ($type === 'auto_purchase' && is_array($emails)) {
                        // 获取用户ID
                        $users = User::whereIn('email', $emails)->get();
                        $userIds = $users->pluck('id')->toArray();
                        
                        // 更新auto_purchase_notify_users设置
                        Setting::updateOrCreate(
                            ['key' => 'auto_purchase_notify_users'],
                            ['value' => json_encode($userIds), 'group' => 'system']
                        );
                    }
                }
            }

            return redirect()
                ->route('settings.notifications')
                ->with('success', '通知设置已更新');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('settings.notifications')
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('更新通知设置失败: ' . $e->getMessage());
            return redirect()
                ->route('settings.notifications')
                ->with('error', '更新通知设置失败')
                ->withInput();
        }
    }
    
    /**
     * 切换电子邮件通知状态
     */
    public function toggleEmail(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'enabled' => 'required|boolean',
            ]);
            
            // 更新电子邮件通知状态
            Setting::updateOrCreate(
                ['key' => 'email_enabled', 'group' => 'notifications'],
                ['value' => $validated['enabled'] ? 'true' : 'false']
            );
            
            return response()->json([
                'success' => true,
                'message' => $validated['enabled'] ? '电子邮件通知已启用' : '电子邮件通知已禁用'
            ]);
        } catch (\Exception $e) {
            Log::error('切换电子邮件通知状态失败: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => '切换电子邮件通知状态失败'
            ], 500);
        }
    }

    /**
     * 显示系统设置页面
     */
    public function system(): View
    {
        try {
            // 获取系统设置
            $settings = Setting::whereIn('group', ['system', 'homepage'])
                ->get()
                ->pluck('value', 'key')
                ->toArray();
        
        // 获取自动采购设置
            $autoPurchaseSettings = $this->settingsService->getAutoPurchaseSettings();
            
            // 获取仓库列表（用于自动采购设置）
            $warehouses = \App\Models\Warehouse::orderBy('name')->get();
            
            // 获取商品列表（用于自动采购黑名单）
            $products = \App\Models\Product::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'sku']);
                
            // 获取用户列表（用于通知接收人设置）
            $users = \App\Models\User::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            return view('settings.system', compact('settings', 'autoPurchaseSettings', 'warehouses', 'products', 'users'));
        } catch (\Exception $e) {
            Log::error('获取系统设置失败: ' . $e->getMessage());
            return view('settings.system', [
                'settings' => [],
                'autoPurchaseSettings' => [],
                'warehouses' => [],
                'products' => [],
                'users' => [],
                'error' => '获取系统设置失败'
            ]);
        }
    }

    /**
     * 更新系统设置
     */
    public function updateSystem(Request $request): RedirectResponse
    {
        try {
        $validated = $request->validate([
                'timezone' => 'required|string|in:' . implode(',', timezone_identifiers_list()),
                'date_format' => 'required|string|in:Y-m-d,d/m/Y,m/d/Y',
                'admin_notification_email' => 'required|email',
                'auto_create_inspection' => 'nullable|in:true,false',
                'auto_approve_inspection' => 'nullable|in:true,false',
                'backup_enabled' => 'nullable|in:0,1',
                'backup_frequency' => 'nullable|string|in:daily,weekly,monthly',
                'auto_add_new_products' => 'nullable|in:true,false', // 新增：新品自动添加设置
                'new_products_display_days' => 'nullable|integer|min:1|max:365', // 新增：新品展示天数
            ]);

            // 处理复选框值，如果未选中则设置为false
            $checkboxSettings = [
                'auto_create_inspection',
                'auto_approve_inspection',
                'auto_add_new_products', // 新增：新品自动添加设置
            ];

            foreach ($checkboxSettings as $setting) {
                if (!isset($validated[$setting])) {
                    $validated[$setting] = 'false';
                }
            }

            // 保存设置
            foreach ($validated as $key => $value) {
                // 对于自动添加新品的设置，设置正确的分组为homepage
                $group = 'system';
                if ($key === 'auto_add_new_products' || $key === 'new_products_display_days') {
                    $group = 'homepage';
                }

                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => $group]
                );
            }

            return redirect()
                ->route('settings.system')
                ->with('success', '系统设置已更新');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('settings.system')
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('更新系统设置失败: ' . $e->getMessage());
            return redirect()
                ->route('settings.system')
                ->with('error', '更新系统设置失败')
                ->withInput();
        }
    }

    public function autoPurchase(): View
    {
        try {
            // 获取设置
            $settingsData = Setting::whereIn('key', [
                'auto_purchase_enabled',
                'auto_purchase_frequency',
                'auto_purchase_quantity_method',
                'default_warehouse_id',
                'auto_purchase_notify_users',
                'auto_purchase_blacklist',
            ])
            ->get()
            ->pluck('value', 'key')
            ->toArray();
            
            // 确保所有设置键都有默认值
            $settings = [
                'auto_purchase_enabled' => $settingsData['auto_purchase_enabled'] ?? 'false',
                'auto_purchase_frequency' => $settingsData['auto_purchase_frequency'] ?? 'daily',
                'auto_purchase_quantity_method' => $settingsData['auto_purchase_quantity_method'] ?? 'min_stock',
                'default_warehouse_id' => $settingsData['default_warehouse_id'] ?? '',
                'auto_purchase_notify_users' => $settingsData['auto_purchase_notify_users'] ?? '[]',
                'auto_purchase_blacklist' => $settingsData['auto_purchase_blacklist'] ?? '[]',
            ];

            // 获取所有仓库
            $warehouses = Warehouse::where('is_active', true)
                ->orderBy('name')
                ->get();

            // 获取所有产品
            $products = Product::orderBy('name')
                ->get(['id', 'name', 'sku']);

            // 获取所有活跃用户
            $users = User::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            return view('settings.auto-purchase', compact('settings', 'warehouses', 'products', 'users'));
        } catch (\Exception $e) {
            Log::error('获取自动采购设置失败: ' . $e->getMessage());
            return view('settings.auto-purchase', [
                'settings' => [
                    'auto_purchase_enabled' => 'false',
                    'auto_purchase_frequency' => 'daily',
                    'auto_purchase_quantity_method' => 'min_stock',
                    'default_warehouse_id' => '',
                    'auto_purchase_notify_users' => '[]',
                    'auto_purchase_blacklist' => '[]',
                ],
                'warehouses' => [],
                'products' => [],
                'users' => [],
                'error' => '获取设置信息时发生错误'
            ]);
        }
    }

    public function updateAutoPurchase(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'auto_purchase_enabled' => 'nullable|in:true,false',
                'auto_purchase_frequency' => 'required|in:daily,weekly,twice_weekly',
                'auto_purchase_quantity_method' => 'required|in:min_stock,double_min_stock,replenish_only',
                'default_warehouse_id' => 'nullable|exists:warehouses,id',
                'auto_purchase_blacklist' => 'nullable|array',
                'auto_purchase_blacklist.*' => 'exists:products,id',
                'auto_purchase_notify_users' => 'nullable|array',
                'auto_purchase_notify_users.*' => 'exists:users,id',
            ]);

            // 更新自动采购启用状态
            Setting::updateOrCreate(
                ['key' => 'auto_purchase_enabled', 'group' => 'purchase'],
                ['value' => isset($validated['auto_purchase_enabled']) ? 'true' : 'false']
            );

            // 更新自动采购频率
            Setting::updateOrCreate(
                ['key' => 'auto_purchase_frequency', 'group' => 'purchase'],
                ['value' => $validated['auto_purchase_frequency']]
            );

            // 更新采购数量计算方法
            Setting::updateOrCreate(
                ['key' => 'auto_purchase_quantity_method', 'group' => 'purchase'],
                ['value' => $validated['auto_purchase_quantity_method']]
            );

            // 更新默认仓库ID
            Setting::updateOrCreate(
                ['key' => 'default_warehouse_id', 'group' => 'inventory'],
                ['value' => $validated['default_warehouse_id'] ?? null]
            );

            // 更新自动采购黑名单
            $blacklist = isset($validated['auto_purchase_blacklist']) ? json_encode($validated['auto_purchase_blacklist']) : '[]';
            Setting::updateOrCreate(
                ['key' => 'auto_purchase_blacklist', 'group' => 'purchase'],
                ['value' => $blacklist]
            );

            // 更新通知用户
            $notifyUsers = isset($validated['auto_purchase_notify_users']) ? json_encode($validated['auto_purchase_notify_users']) : '[]';
            Setting::updateOrCreate(
                ['key' => 'auto_purchase_notify_users', 'group' => 'purchase'],
                ['value' => $notifyUsers]
            );

            return redirect()
                ->route('settings.auto-purchase')
                ->with('success', '自动采购设置已更新');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('settings.auto-purchase')
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('更新自动采购设置失败: ' . $e->getMessage());
            return redirect()
                ->route('settings.auto-purchase')
                ->with('error', '更新设置时发生错误')
                ->withInput();
        }
    }

    /**
     * 查看通知历史记录
     */
    public function notificationHistory(Request $request): View
    {
        try {
            $notificationTypes = [
                'product_created' => 'Product Created',
                'product_updated' => 'Product Updated',
                'product_deleted' => 'Product Deleted',
                'quality_inspection_created' => 'Quality Inspection Created',
                'quality_inspection_updated' => 'Quality Inspection Status Updated',
                'purchase_created' => 'Purchase Order Created',
                'purchase_status_changed' => 'Purchase Order Status Updated',
                'inventory_alert' => 'Inventory Alert',
                'payment_status_changed' => 'Payment Status Updated',
                'system_alert' => 'System Alert',
                'purchase_order_generated' => 'Auto Purchase Order Generated',
                'payment_overdue' => 'Payment Overdue Reminder',
                'supplier_order_notification' => 'Supplier Order Notification',
                'test_mail' => 'Test Mail',
            ];
            
            // Get all notification logs without filtering (filtering will be done on the frontend)
            $notificationHistory = \App\Models\NotificationLog::orderBy('created_at', 'desc')->paginate(15);
            
            // Get unique recipients for the filter
            $recipients = \App\Models\NotificationLog::distinct()->pluck('recipient')->toArray();
            
            // Get all users for the recipient selector with improved error handling
            try {
                $users = \App\Models\User::select('id', 'name', 'email')->get();
                Log::info('Retrieved users for notification history: ' . $users->count());
            } catch (\Exception $e) {
                Log::error('Failed to get users for notification history: ' . $e->getMessage());
                $users = collect();
            }
            
            return view('settings.notification-history', compact('notificationHistory', 'notificationTypes', 'recipients', 'users'));
        } catch (\Exception $e) {
            Log::error('Failed to get notification history: ' . $e->getMessage());
            return view('settings.notification-history', [
                'notificationHistory' => collect(),
                'notificationTypes' => [
                    'product_created' => 'Product Created',
                    'product_updated' => 'Product Updated',
                    'product_deleted' => 'Product Deleted',
                    'quality_inspection_created' => 'Quality Inspection Created',
                    'quality_inspection_updated' => 'Quality Inspection Status Updated',
                    'purchase_created' => 'Purchase Order Created',
                    'purchase_status_changed' => 'Purchase Order Status Updated',
                    'inventory_alert' => 'Inventory Alert',
                    'payment_status_changed' => 'Payment Status Updated',
                    'system_alert' => 'System Alert',
                    'purchase_order_generated' => 'Auto Purchase Order Generated',
                    'payment_overdue' => 'Payment Overdue Reminder',
                    'supplier_order_notification' => 'Supplier Order Notification',
                    'test_mail' => 'Test Mail',
                ],
                'recipients' => [],
                'users' => collect(),
            ]);
        }
    }
}