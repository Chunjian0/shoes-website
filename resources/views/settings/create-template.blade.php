@extends('layouts.settings')

@section('title', isset($template) ? 'Edit Message Template' : 'Create Message Template')

@section('breadcrumb')
<nav class="text-sm">
    <ol class="list-none p-0 inline-flex">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('settings.index') }}" class="text-gray-500 hover:text-gray-700">Settings</a>
            <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('settings.message-templates') }}" class="text-gray-500 hover:text-gray-700">Message Templates</a>
            <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
        </li>
        <li>
            <span class="text-gray-700">{{ isset($template) ? 'Edit' : 'Create' }}</span>
        </li>
    </ol>
</nav>
@endsection

@section('settings-content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900 mb-4">{{ isset($template) ? 'Edit Message Template' : 'Create Message Template' }}</h2>

                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-medium">
                                There were some problems with your input.
                            </p>
                            <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ isset($template) ? route('settings.message-templates.update', $template->id) : route('settings.message-templates.store') }}">
                    @csrf
                    @if(isset($template))
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- 模板名称 -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $template->name ?? '') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        </div>

                        <!-- 模板描述 -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input type="text" name="description" id="description" value="{{ old('description', $template->description ?? '') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <!-- 模板类型 -->
                        <div>
                            <label for="channel" class="block text-sm font-medium text-gray-700 mb-1">Template Type</label>
                            <select name="channel" id="channel" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select Template Type</option>
                                <option value="product_created" {{ old('channel', $template->channel ?? '') == 'product_created' ? 'selected' : '' }}>New product creation notification</option>
                                <option value="product_updated" {{ old('channel', $template->channel ?? '') == 'product_updated' ? 'selected' : '' }}>Product Update Notification</option>
                                <option value="quality_inspection_created" {{ old('channel', $template->channel ?? '') == 'quality_inspection_created' ? 'selected' : '' }}>Quality inspection record creation notice</option>
                                <option value="quality_inspection_updated" {{ old('channel', $template->channel ?? '') == 'quality_inspection_updated' ? 'selected' : '' }}>Quality Inspection Status Update Notice</option>
                                <option value="purchase_created" {{ old('channel', $template->channel ?? '') == 'purchase_created' ? 'selected' : '' }}>Purchase Order Creation Notice</option>
                                <option value="purchase_status_changed" {{ old('channel', $template->channel ?? '') == 'purchase_status_changed' ? 'selected' : '' }}>Purchase Order Status Update Notice</option>
                                <option value="send_purchase_order" {{ old('channel', $template->channel ?? '') == 'send_purchase_order' ? 'selected' : '' }}>Send Purchase Order</option>
                                <option value="inventory_alert" {{ old('channel', $template->channel ?? '') == 'inventory_alert' ? 'selected' : '' }}>Inventory warning notice</option>
                                <option value="low_stock_removal" {{ old('channel', $template->channel ?? '') == 'low_stock_removal' ? 'selected' : '' }}>Low Stock Product Removal Notice</option>
                                <option value="payment_status_changed" {{ old('channel', $template->channel ?? '') == 'payment_status_changed' ? 'selected' : '' }}>Payment status update notification</option>
                                <option value="system_alert" {{ old('channel', $template->channel ?? '') == 'system_alert' ? 'selected' : '' }}>System warning notification</option>
                            </select>
                        </div>
                        
                        <!-- 邮件主题 -->
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Email Subject</label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject', $template->subject ?? '') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                        
                        <!-- 供应商选择（多选）-->
                        <div id="suppliers-container" class="col-span-full md:col-span-2 {{ old('channel', $template->channel ?? '') == 'global' ? 'hidden' : '' }}">
                            <label for="suppliers" class="block text-sm font-medium text-gray-700 mb-1">Select Suppliers</label>
                            <p class="text-xs text-gray-500 mb-2">Leave empty to make this a global template for all suppliers</p>
                            <select name="suppliers[]" id="suppliers" class="select2-suppliers mt-1 block w-full" multiple>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ (isset($template) && $template->suppliers->contains($supplier->id)) ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->code }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- 是否默认 -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_default" name="is_default" type="checkbox" value="1" {{ old('is_default', isset($template) && $template->is_default ? 'checked' : '') ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_default" class="font-medium text-gray-700">Set as Default Template</label>
                                <p class="text-gray-500">This will be used as the default template for this type</p>
                            </div>
                        </div>
                        
                        <!-- 活动状态 -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="status" name="status" type="checkbox" value="active" {{ old('status', $template->status ?? 'active') == 'active' ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="status" class="font-medium text-gray-700">Active</label>
                                <p class="text-gray-500">Only active templates will be used for sending emails</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- 模板内容 -->
                        <div class="col-span-2">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Template Content</label>
                            <textarea name="content" id="content" rows="15" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md font-mono">{{ old('content', $template->content ?? '') }}</textarea>
                        </div>
                        
                        <!-- 可用变量 -->
                        <div>
                            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Available Variables</h4>
                                
                                <p class="text-xs text-gray-500 mb-2">Click a variable to insert it at cursor position:</p>
                                
                                <div class="space-y-3">
                                    <div>
                                        <h5 class="text-xs font-medium text-gray-600 mb-1">Common Variables:</h5>
                                        <div class="space-x-1 space-y-1">
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{app_name}">App Name</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{app_url}">App URL</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{current_date}">Current Date</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{current_time}">Current Time</button>
                                        </div>
                                    </div>
                                    
                                    <div id="order-variables" class="hidden">
                                        <h5 class="text-xs font-medium text-gray-600 mb-1">Order Variables:</h5>
                                        <div class="space-x-1 space-y-1">
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{order_number}">Order Number</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{order_date}">Order Date</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{order_total}">Order Total</button>
                                        </div>
                                    </div>
                                    
                                    <div id="payment-variables" class="hidden">
                                        <h5 class="text-xs font-medium text-gray-600 mb-1">Payment Variables:</h5>
                                        <div class="space-x-1 space-y-1">
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{customer_name}">Customer Name</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{invoice_number}">Invoice Number</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{invoice_date}">Invoice Date</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{due_date}">Due Date</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{amount_due}">Amount Due</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{days_overdue}">Days Overdue</button>
                                        </div>
                                    </div>
                                    
                                    <div id="purchase-variables" class="hidden">
                                        <h5 class="text-xs font-medium text-gray-600 mb-1">Purchase Variables:</h5>
                                        <div class="space-x-1 space-y-1">
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{purchase_number}">Purchase Number</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{purchase_date}">Purchase Date</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{purchase_total}">Purchase Total</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{supplier_name}">Supplier Name</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{supplier_email}">Supplier Email</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{supplier_contact}">Supplier Contact</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{supplier_names}">All Supplier Names</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{supplier_count}">Supplier Count</button>
                                        </div>
                                    </div>
                                    
                                    <div id="low-stock-variables" class="hidden">
                                        <h5 class="text-xs font-medium text-gray-600 mb-1">Low Stock Variables:</h5>
                                        <div class="space-x-1 space-y-1">
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{count}">Removed Products Count</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{threshold}">Stock Threshold</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{removed_products}">Removed Products List</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{product.id}">Product ID</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{product.name}">Product Name</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{product.sku}">Product SKU</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{product.stock}">Product Current Stock</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{time}">Removal Time</button>
                                            <button type="button" class="variable-btn inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50" data-variable="{url}">Management URL</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 预览部分 -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Preview</h3>
                            <button type="button" id="preview-button" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Preview Template
                            </button>
                        </div>
                        <div id="preview-container" class="bg-gray-50 p-6 border border-gray-200 rounded-md min-h-[300px] prose max-w-none">
                            <div class="text-center text-gray-500 italic py-12">
                                Click "Preview Template" to see how your email will look.
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between">
                        <a href="{{ route('settings.message-templates') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Message Templates
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            {{ isset($template) ? 'Update' : 'Save' }} Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
    
    /* 改进的Select2下拉菜单样式 */
    .select2-dropdown {
        border-color: #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem;
    }
    
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5;
    }
    
    .select2-container--default .select2-results__option {
        padding: 0.5rem 0.75rem;
    }
    
    /* 变量按钮样式改进 */
    .variable-btn {
        transition: all 0.2s;
    }
    
    .variable-btn:hover {
        background-color: #f3f4f6;
        transform: translateY(-1px);
    }
    
    .variable-btn:active {
        transform: translateY(0);
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translate3d(0, -10px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }
    
    .animate-fade-in-down {
        animation: fadeInDown 0.2s ease-out;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 初始化Select2
        $(document).ready(function() {
            $('.select2-suppliers').select2({
                placeholder: 'Search and select suppliers',
                allowClear: true,
                language: {
                    noResults: () => "No matching suppliers found",
                    searching: () => "Searching...",
                    loadingMore: () => "Loading more results...",
                    removeAllItems: () => "Remove all",
                },
                width: '100%',
                templateResult: formatSupplier,
                templateSelection: formatSupplierSelection,
                escapeMarkup: function(markup) {
                    return markup;
                }
            });
            
            // 自定义供应商选项格式
            function formatSupplier(supplier) {
                if (!supplier.id) {
                    return supplier.text;
                }
                
                // 提取供应商代码（假设格式为 "供应商名称 (代码)"）
                const match = supplier.text.match(/(.*) \((.*)\)$/);
                if (!match) return supplier.text;
                
                const name = match[1];
                const code = match[2];
                
                return $(`
                    <div class="flex items-center py-1">
                        <div class="flex-1">
                            <div class="font-medium">${name}</div>
                            <div class="text-xs text-gray-500">Code: ${code}</div>
                        </div>
                    </div>
                `);
            }
            
            // 自定义已选择的供应商格式
            function formatSupplierSelection(supplier) {
                if (!supplier.id) {
                    return supplier.text;
                }
                
                // 提取供应商名称（不显示代码）
                const match = supplier.text.match(/(.*) \((.*)\)$/);
                return match ? match[1] : supplier.text;
            }
        });
        
        // 变量按钮点击处理
        const variableBtns = document.querySelectorAll('.variable-btn');
        const contentTextarea = document.getElementById('content');
        
        variableBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const variable = this.getAttribute('data-variable');
                
                // 获取当前光标位置
                const cursorPos = contentTextarea.selectionStart;
                const textBefore = contentTextarea.value.substring(0, cursorPos);
                const textAfter = contentTextarea.value.substring(cursorPos);
                
                // 在光标位置插入变量
                contentTextarea.value = textBefore + variable + textAfter;
                
                // 将光标移动到插入的变量之后
                contentTextarea.focus();
                contentTextarea.selectionStart = cursorPos + variable.length;
                contentTextarea.selectionEnd = cursorPos + variable.length;
                
                // 添加动画效果
                this.classList.add('bg-indigo-100');
                setTimeout(() => {
                    this.classList.remove('bg-indigo-100');
                }, 300);
            });
        });
        
        // 根据所选模板类型显示对应变量
        const channelSelect = document.getElementById('channel');
        const orderVariables = document.getElementById('order-variables');
        const paymentVariables = document.getElementById('payment-variables');
        const purchaseVariables = document.getElementById('purchase-variables');
        const lowStockVariables = document.getElementById('low-stock-variables');
        const suppliersContainer = document.getElementById('suppliers-container');
        
        // 初始化时显示相应的变量区域
        updateVariableDisplay();
        
        // 模板类型变化时更新变量显示
        channelSelect.addEventListener('change', updateVariableDisplay);
        
        function updateVariableDisplay() {
            const selectedType = channelSelect.value;
            
            // 默认隐藏所有特定类型的变量
            orderVariables.classList.add('hidden');
            paymentVariables.classList.add('hidden');
            purchaseVariables.classList.add('hidden');
            lowStockVariables.classList.add('hidden');
            
            // 根据选择的模板类型显示相应的变量
            if (selectedType.includes('order')) {
                orderVariables.classList.remove('hidden');
            }
            
            if (selectedType.includes('payment')) {
                paymentVariables.classList.remove('hidden');
            }
            
            if (selectedType.includes('purchase') || selectedType === 'send_purchase_order') {
                purchaseVariables.classList.remove('hidden');
            }
            
            if (selectedType === 'low_stock_removal') {
                lowStockVariables.classList.remove('hidden');
            }
            
            // 显示/隐藏供应商选择
            if (selectedType === 'send_purchase_order') {
                suppliersContainer.classList.remove('hidden');
                // 如果选择了发送采购单，确保供应商选择框是可见的并且有焦点
                setTimeout(() => {
                    $('.select2-suppliers').select2('open');
                }, 100);
            } else {
                suppliersContainer.classList.add('hidden');
            }
        }
        
        // 预览功能
        const previewButton = document.getElementById('preview-button');
        const previewContainer = document.getElementById('preview-container');
        
        previewButton.addEventListener('click', function() {
            const content = contentTextarea.value;
            const channel = channelSelect.value;
            
            // 显示加载状态
            previewContainer.innerHTML = '<div class="text-center py-12"><div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-600"></div><p class="mt-2 text-gray-500">Loading preview...</p></div>';
            
            // 发送预览请求
            fetch('{{ route("settings.message-templates.preview") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    content: content,
                    type: channel
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    previewContainer.innerHTML = data.html;
                } else {
                    previewContainer.innerHTML = '<div class="bg-red-50 p-4 rounded-md"><p class="text-red-500">Error generating preview: ' + (data.message || 'Unknown error') + '</p></div>';
                }
            })
            .catch(error => {
                previewContainer.innerHTML = '<div class="bg-red-50 p-4 rounded-md"><p class="text-red-500">Error: ' + error.message + '</p></div>';
            });
        });
        
        // 增强供应商选择框的用户体验
        $('.select2-suppliers').on('select2:open', function() {
            // 添加自定义类到下拉容器
            $('.select2-dropdown').addClass('animate-fade-in-down');
            
            // 聚焦搜索框
            setTimeout(function() {
                $('.select2-search__field').focus();
            }, 0);
        });
        
        // 添加键盘快捷键支持
        $(document).on('keydown', '.select2-search__field', function(e) {
            // 按下Escape键关闭下拉框
            if (e.key === 'Escape') {
                $('.select2-suppliers').select2('close');
            }
        });
    });
</script>
@endpush 