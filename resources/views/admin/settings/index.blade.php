@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-bold mb-6">系统设置</h1>
                
                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-8">
                    @csrf
                    @method('PUT')
                    
                    <!-- 通用设置 -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">通用设置</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">应用的基本配置选项</p>
                        </div>
                        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                            <!-- 公司名称 -->
                            <div class="mb-4">
                                <label for="company_name" class="block text-sm font-medium text-gray-700">公司名称</label>
                                <input type="text" name="settings[company_name]" id="company_name" 
                                       value="{{ $settings['company_name'] ?? '' }}" 
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            
                            <!-- 公司地址 -->
                            <div class="mb-4">
                                <label for="company_address" class="block text-sm font-medium text-gray-700">公司地址</label>
                                <textarea name="settings[company_address]" id="company_address" rows="3" 
                                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $settings['company_address'] ?? '' }}</textarea>
                            </div>
                            
                            <!-- 联系电话 -->
                            <div class="mb-4">
                                <label for="company_phone" class="block text-sm font-medium text-gray-700">联系电话</label>
                                <input type="text" name="settings[company_phone]" id="company_phone" 
                                       value="{{ $settings['company_phone'] ?? '' }}" 
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            
                            <!-- 电子邮件 -->
                            <div>
                                <label for="company_email" class="block text-sm font-medium text-gray-700">电子邮件</label>
                                <input type="email" name="settings[company_email]" id="company_email" 
                                       value="{{ $settings['company_email'] ?? '' }}" 
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>
                    
                    <!-- 首页设置 -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">首页设置</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">管理首页展示相关的配置选项</p>
                        </div>
                        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                            <!-- 库存阈值设置 -->
                            <div class="mb-4">
                                <label for="homepage_min_stock_threshold" class="block text-sm font-medium text-gray-700">最低库存阈值</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="number" name="settings[homepage_min_stock_threshold]" id="homepage_min_stock_threshold" 
                                           value="{{ $settings['homepage_min_stock_threshold'] ?? 5 }}" min="1"
                                           class="focus:ring-blue-500 focus:border-blue-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300">
                                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                        件
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">库存低于此值的产品将自动从首页移除</p>
                            </div>
                            
                            <!-- 自动添加新品 -->
                            <div class="mb-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="hidden" name="settings[auto_add_new_products]" value="false">
                                        <input type="checkbox" name="settings[auto_add_new_products]" id="auto_add_new_products" 
                                               value="true" {{ ($settings['auto_add_new_products'] ?? 'false') === 'true' ? 'checked' : '' }}
                                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="auto_add_new_products" class="font-medium text-gray-700">自动添加新品</label>
                                        <p class="text-gray-500">启用后，新创建的商品将自动添加到首页新品区域</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 新品展示天数 -->
                            <div class="mb-4">
                                <label for="new_products_display_days" class="block text-sm font-medium text-gray-700">新品展示天数</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="number" name="settings[new_products_display_days]" id="new_products_display_days" 
                                           value="{{ $settings['new_products_display_days'] ?? 30 }}" min="1"
                                           class="focus:ring-blue-500 focus:border-blue-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300">
                                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                        天
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">新品在首页展示的时间，过期后将自动移除</p>
                            </div>
                            
                            <!-- 自动添加折扣商品 -->
                            <div class="mb-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="hidden" name="settings[auto_add_sale_products]" value="false">
                                        <input type="checkbox" name="settings[auto_add_sale_products]" id="auto_add_sale_products" 
                                               value="true" {{ ($settings['auto_add_sale_products'] ?? 'false') === 'true' ? 'checked' : '' }}
                                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="auto_add_sale_products" class="font-medium text-gray-700">自动添加折扣商品</label>
                                        <p class="text-gray-500">启用后，设置了折扣的商品将自动添加到首页促销区域</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 促销商品展示天数 -->
                            <div class="mb-4">
                                <label for="sale_products_display_days" class="block text-sm font-medium text-gray-700">促销商品展示天数</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="number" name="settings[sale_products_display_days]" id="sale_products_display_days" 
                                           value="{{ $settings['sale_products_display_days'] ?? 30 }}" min="1"
                                           class="focus:ring-blue-500 focus:border-blue-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300">
                                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                        天
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">折扣商品在首页促销区域的展示时间，过期后将自动移除</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 保存按钮 -->
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            保存设置
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 