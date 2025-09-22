@extends('layouts.settings')

@section('settings-content')
<div class="space-y-6">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">系统设置</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">配置系统的基本参数和功能</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('settings.system.update') }}" method="POST" class="divide-y divide-gray-200">
            @csrf
            @method('PUT')

            <!-- 时区设置 -->
            <div class="px-4 py-5 sm:p-6">
                <div class="mb-6">
                    <label for="timezone" class="block text-sm font-medium text-gray-700">系统时区</label>
                    <select name="timezone" id="timezone" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        @foreach(timezone_identifiers_list() as $timezone)
                            <option value="{{ $timezone }}" 
                                {{ old('timezone', $settings['timezone'] ?? config('app.timezone')) == $timezone ? 'selected' : '' }}>
                                {{ $timezone }}
                            </option>
                        @endforeach
                    </select>
                    @error('timezone')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 日期格式 -->
                <div class="mb-6">
                    <label for="date_format" class="block text-sm font-medium text-gray-700">日期格式</label>
                    <select name="date_format" id="date_format" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="Y-m-d" {{ old('date_format', $settings['date_format'] ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : '' }}>
                            YYYY-MM-DD (例如: 2024-01-31)
                        </option>
                        <option value="d/m/Y" {{ old('date_format', $settings['date_format'] ?? 'Y-m-d') == 'd/m/Y' ? 'selected' : '' }}>
                            DD/MM/YYYY (例如: 31/01/2024)
                        </option>
                        <option value="m/d/Y" {{ old('date_format', $settings['date_format'] ?? 'Y-m-d') == 'm/d/Y' ? 'selected' : '' }}>
                            MM/DD/YYYY (例如: 01/31/2024)
                        </option>
                    </select>
                    @error('date_format')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 管理员通知设置 -->
            <div class="px-4 py-5 sm:p-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">管理员通知</h3>
                    <p class="mt-1 text-sm text-gray-500">设置管理员邮箱地址，用于接收系统中的重要通知</p>
                </div>

                <div class="mt-6">
                    <label for="admin_notification_email" class="block text-sm font-medium text-gray-700">管理员邮箱</label>
                    <input type="email" name="admin_notification_email" id="admin_notification_email" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           value="{{ old('admin_notification_email', $settings['admin_notification_email'] ?? '') }}"
                           required>
                    <p class="mt-2 text-sm text-gray-500">用于接收重要系统通知，例如安全警告、错误报告等</p>
                    @error('admin_notification_email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 自动采购设置 -->
            <div class="px-4 py-5 sm:p-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">自动采购设置</h3>
                    <p class="mt-1 text-sm text-gray-500">配置系统自动检查库存并生成采购单的功能</p>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="auto_purchase_enabled" value="true"
                                {{ old('auto_purchase_enabled', $autoPurchaseSettings['auto_purchase_enabled'] ?? 'false') === 'true' ? 'checked' : '' }}
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="auto_purchase_enabled" class="font-medium text-gray-700">启用自动采购</label>
                            <p class="text-gray-500">启用后，系统将按设定频率自动检查库存并生成采购单</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="ml-7">
                        <label for="auto_purchase_frequency" class="block text-sm font-medium text-gray-700">自动采购频率</label>
                        <select name="auto_purchase_frequency" id="auto_purchase_frequency" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="daily" {{ old('auto_purchase_frequency', $autoPurchaseSettings['auto_purchase_frequency'] ?? 'daily') === 'daily' ? 'selected' : '' }}>
                                每天
                            </option>
                            <option value="weekly" {{ old('auto_purchase_frequency', $autoPurchaseSettings['auto_purchase_frequency'] ?? 'daily') === 'weekly' ? 'selected' : '' }}>
                                每周（周一）
                            </option>
                            <option value="twice_weekly" {{ old('auto_purchase_frequency', $autoPurchaseSettings['auto_purchase_frequency'] ?? 'daily') === 'twice_weekly' ? 'selected' : '' }}>
                                每周两次（周一和周四）
                            </option>
                        </select>
                        <p class="mt-2 text-sm text-gray-500">设置系统自动检查库存并生成采购单的频率</p>
                        @error('auto_purchase_frequency')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="ml-7">
                        <label for="auto_purchase_quantity_method" class="block text-sm font-medium text-gray-700">采购数量计算方法</label>
                        <select name="auto_purchase_quantity_method" id="auto_purchase_quantity_method" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="min_stock" {{ old('auto_purchase_quantity_method', $autoPurchaseSettings['auto_purchase_quantity_method'] ?? 'min_stock') === 'min_stock' ? 'selected' : '' }}>
                                按最小库存水平采购
                            </option>
                            <option value="double_min_stock" {{ old('auto_purchase_quantity_method', $autoPurchaseSettings['auto_purchase_quantity_method'] ?? 'min_stock') === 'double_min_stock' ? 'selected' : '' }}>
                                按两倍最小库存水平采购
                            </option>
                            <option value="replenish_only" {{ old('auto_purchase_quantity_method', $autoPurchaseSettings['auto_purchase_quantity_method'] ?? 'min_stock') === 'replenish_only' ? 'selected' : '' }}>
                                仅补充至最小库存
                            </option>
                        </select>
                        <p class="mt-2 text-sm text-gray-500">决定系统如何计算每个商品的采购数量</p>
                        @error('auto_purchase_quantity_method')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="ml-7">
                        <label for="default_warehouse_id" class="block text-sm font-medium text-gray-700">默认仓库</label>
                        <select name="default_warehouse_id" id="default_warehouse_id" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">-- 请选择默认仓库 --</option>
                            @foreach($warehouses ?? [] as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('default_warehouse_id', $autoPurchaseSettings['default_warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-gray-500">自动采购时将使用此仓库的库存数据</p>
                        @error('default_warehouse_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="ml-7">
                        <label for="auto_purchase_notify_users" class="block text-sm font-medium text-gray-700">通知用户</label>
                        <select name="auto_purchase_notify_users[]" id="auto_purchase_notify_users" multiple
                                class="select2-users mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" 
                                    {{ in_array($user->id, json_decode($autoPurchaseSettings['auto_purchase_notify_users'] ?? '[]', true)) ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-gray-500">系统生成采购单后将通知这些用户</p>
                        @error('auto_purchase_notify_users')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="ml-7">
                        <label for="auto_purchase_blacklist" class="block text-sm font-medium text-gray-700">排除商品（黑名单）</label>
                        <select name="auto_purchase_blacklist[]" id="auto_purchase_blacklist" multiple
                                class="select2-products mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}" 
                                    {{ in_array($product->id, json_decode($autoPurchaseSettings['auto_purchase_blacklist'] ?? '[]', true)) ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-gray-500">这些商品将被排除在自动采购之外</p>
                        @error('auto_purchase_blacklist')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 新品自动添加设置 -->
            <div class="px-4 py-5 sm:p-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">新品自动添加设置</h3>
                    <p class="mt-1 text-sm text-gray-500">配置新创建的商品自动添加到新品区域的功能</p>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="auto_add_new_products" value="true"
                                {{ old('auto_add_new_products', $settings['auto_add_new_products'] ?? 'false') === 'true' ? 'checked' : '' }}
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="auto_add_new_products" class="font-medium text-gray-700">自动添加新品</label>
                            <p class="text-gray-500">启用后，系统将自动将新创建的商品添加到首页新品区域</p>
                        </div>
                    </div>
                    
                    <div class="ml-7">
                        <label for="new_products_display_days" class="block text-sm font-medium text-gray-700">新品展示天数</label>
                        <input type="number" name="new_products_display_days" id="new_products_display_days" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               min="1" max="365" step="1"
                               value="{{ old('new_products_display_days', $settings['new_products_display_days'] ?? '30') }}">
                        <p class="mt-2 text-sm text-gray-500">设置新品在首页展示的默认天数，过期后将自动从新品区域移除</p>
                        @error('new_products_display_days')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 质量检验设置 -->
            <div class="px-4 py-5 sm:p-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">质量检验设置</h3>
                    <p class="mt-1 text-sm text-gray-500">配置商品接收时的质量检验选项</p>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="auto_create_inspection" value="true"
                                {{ old('auto_create_inspection', $settings['auto_create_inspection'] ?? 'true') === 'true' ? 'checked' : '' }}
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="auto_create_inspection" class="font-medium text-gray-700">默认自动创建质量检验记录</label>
                            <p class="text-gray-500">启用后，系统在确认收货时会默认选中自动创建质量检验记录选项</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="auto_approve_inspection" value="true"
                                {{ old('auto_approve_inspection', $settings['auto_approve_inspection'] ?? 'true') === 'true' ? 'checked' : '' }}
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="auto_approve_inspection" class="font-medium text-gray-700">自动审批质量检验记录</label>
                            <p class="text-gray-500">启用后，系统会自动审批收货时创建的质量检验记录，并将合格商品加入库存</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 数据备份设置 -->
            <div class="px-4 py-5 sm:p-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">数据备份</h3>
                    <p class="mt-1 text-sm text-gray-500">配置自动备份设置</p>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="backup_enabled" value="1"
                                {{ old('backup_enabled', $settings['backup_enabled'] ?? false) ? 'checked' : '' }}
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="backup_enabled" class="font-medium text-gray-700">启用自动备份</label>
                            <p class="text-gray-500">定期自动备份系统数据</p>
                        </div>
                    </div>

                    <div class="ml-7">
                        <label for="backup_frequency" class="block text-sm font-medium text-gray-700">备份频率</label>
                        <select name="backup_frequency" id="backup_frequency" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="daily" {{ old('backup_frequency', $settings['backup_frequency'] ?? '') == 'daily' ? 'selected' : '' }}>
                                每天
                            </option>
                            <option value="weekly" {{ old('backup_frequency', $settings['backup_frequency'] ?? '') == 'weekly' ? 'selected' : '' }}>
                                每周
                            </option>
                            <option value="monthly" {{ old('backup_frequency', $settings['backup_frequency'] ?? '') == 'monthly' ? 'selected' : '' }}>
                                每月
                            </option>
                        </select>
                        @error('backup_frequency')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    保存设置
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 样式 */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container--default .select2-selection--multiple {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        min-height: 3rem;
        padding: 0.25rem 0.5rem;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        outline: none;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #eef2ff;
        border: none;
        border-radius: 0.375rem;
        color: #4f46e5;
        font-size: 0.875rem;
        margin: 0.25rem;
        padding: 0.25rem 0.75rem;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #6366f1;
        margin-right: 0.375rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSettings();
});

// 初始化设置页面
function initializeSettings() {
    setupDependentFields();
    initializeSelect2();
}

// 初始化Select2下拉框
function initializeSelect2() {
    try {
        // 用户选择器
        if ($('#auto_purchase_notify_users').length) {
            $('#auto_purchase_notify_users').select2({
                placeholder: '选择需要通知的用户',
                allowClear: true,
                language: {
                    noResults: () => "未找到匹配的用户",
                    searching: () => "搜索中...",
                    loadingMore: () => "加载更多...",
                    removeAllItems: () => "清除所有",
                },
                width: '100%'
            });
        }
        
        // 产品选择器
        if ($('#auto_purchase_blacklist').length) {
            $('#auto_purchase_blacklist').select2({
                placeholder: '选择需要排除的商品',
                allowClear: true,
                language: {
                    noResults: () => "未找到匹配的商品",
                    searching: () => "搜索中...",
                    loadingMore: () => "加载更多...",
                    removeAllItems: () => "清除所有",
                },
                width: '100%'
            });
        }
    } catch (e) {
        console.error('初始化Select2失败:', e);
    }
}

// 设置字段的依赖关系
function setupDependentFields() {
    try {
        // 备份启用字段的依赖
        const backupEnabledCheckbox = document.querySelector('input[name="backup_enabled"]');
        const backupFrequencySelect = document.getElementById('backup_frequency');
        
        if (backupEnabledCheckbox && backupFrequencySelect) {
            updateFieldVisibility(backupEnabledCheckbox.checked, backupFrequencySelect.closest('.ml-7'));
            
            backupEnabledCheckbox.addEventListener('change', function() {
                updateFieldVisibility(this.checked, backupFrequencySelect.closest('.ml-7'));
            });
        }
        
        // 自动采购启用字段的依赖
        const autoPurchaseEnabledCheckbox = document.querySelector('input[name="auto_purchase_enabled"]');
        if (autoPurchaseEnabledCheckbox) {
            const autoPurchaseFields = [];
            
            // 获取所有需要启用/禁用的字段
            ['auto_purchase_frequency', 'auto_purchase_quantity_method', 
             'default_warehouse_id', 'auto_purchase_notify_users', 
             'auto_purchase_blacklist'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    autoPurchaseFields.push(element.closest('.ml-7'));
                }
            });
            
            if (autoPurchaseFields.length > 0) {
                updateFieldsVisibility(autoPurchaseEnabledCheckbox.checked, autoPurchaseFields);
                
                autoPurchaseEnabledCheckbox.addEventListener('change', function() {
                    updateFieldsVisibility(this.checked, autoPurchaseFields);
                });
            }
        }
    } catch (e) {
        console.error('设置字段依赖关系失败:', e);
    }
}

// 更新单个字段的可见性
function updateFieldVisibility(isVisible, element) {
    if (element) {
        element.style.opacity = isVisible ? '1' : '0.5';
        element.style.pointerEvents = isVisible ? 'auto' : 'none';
        
        const inputs = element.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.disabled = !isVisible;
        });
    }
}

// 更新多个字段的可见性
function updateFieldsVisibility(isVisible, elements) {
    elements.forEach(element => updateFieldVisibility(isVisible, element));
}

// 如果使用 Livewire，设置导航钩子
if (typeof window.Livewire !== 'undefined') {
    window.Livewire.on('navigated', () => {
        console.log('Livewire导航完成，重新初始化...');
        setTimeout(initializeSettings, 100);
    });
}
</script>
@endpush 