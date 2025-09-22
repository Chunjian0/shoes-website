@extends('layouts.settings')

@section('title', 'Edit Message Template')

@section('breadcrumb')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settings.message-templates') }}">Message Templates</a></li>
    <li class="breadcrumb-item active">Edit Template</li>
</ol>
@endsection

@push('styles')
<style>
    .variable-container {
        max-height: 300px;
        overflow-y: auto;
    }
    .var-btn {
        margin-right: 5px;
        margin-bottom: 5px;
        transition: all 0.2s ease;
    }
    .var-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .variable-group {
        margin-bottom: 15px;
    }
    #preview-container {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        transition: all 0.3s ease;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .template-editor {
        min-height: 300px;
        font-family: "Courier New", monospace;
        line-height: 1.6;
    }
    .section-title {
        font-size: 1.15rem;
        font-weight: 600;
        color: #4F46E5;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #E5E7EB;
    }
    .card-header-custom {
        background-color: #f9fafb;
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        border-radius: 0.5rem 0.5rem 0 0;
    }
    .template-card {
        transition: all 0.3s ease;
    }
    .template-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .variable-category {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        background-color: #EEF2FF;
        color: #4F46E5;
        border: 1px solid #C7D2FE;
    }
    .variable-category.common {
        background-color: #ECFDF5;
        color: #047857;
        border-color: #A7F3D0;
    }
    .variable-category.supplier {
        background-color: #FEF3C7;
        color: #92400E;
        border-color: #FDE68A;
    }
    .variable-category.template {
        background-color: #EFF6FF;
        color: #1D4ED8;
        border-color: #BFDBFE;
    }
    .gradient-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
    }
    .gradient-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
        background: linear-gradient(90deg, #4F46E5, #60A5FA);
    }
    .template-type-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        background-color: #EEF2FF;
        color: #4F46E5;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .template-type-badge svg {
        width: 14px;
        height: 14px;
        margin-right: 4px;
    }
    .btn-gradient {
        background: linear-gradient(90deg, #4F46E5, #60A5FA);
        border: none;
    }
    .btn-gradient:hover {
        background: linear-gradient(90deg, #4338CA, #3B82F6);
    }
    
    /* Select2 Custom Styles */
    .select-wrapper {
        position: relative;
    }
    .select2-container {
        width: 100% !important;
        z-index: 100;
    }
    .select2-container--default .select2-selection--multiple {
        border-color: #e5e7eb;
        border-radius: 0.375rem;
        min-height: 42px;
        padding: 2px 6px;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #6366f1;
        outline: 0;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
        padding: 2px 8px;
        margin-top: 4px;
        font-size: 0.875rem;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #4b5563;
        margin-right: 5px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #111827;
    }
    .select2-dropdown {
        border-color: #e5e7eb;
        border-radius: 0.375rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .select2-search--dropdown {
        padding: 8px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-color: #d1d5db;
        border-radius: 0.375rem;
        padding: 6px 10px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5;
    }
</style>
@endpush

@section('settings-content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg gradient-card template-card">
    <div class="border-b border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Edit Message Template</h3>
                <p class="mt-1 text-sm text-gray-500">Modify template content and settings, supports various variables</p>
            </div>
            <div class="template-type-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ $templateTypeLabels[$template->channel] ?? ucfirst($template->channel) }}
            </div>
        </div>
    </div>
    <div class="p-6">
        @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Form Validation Error</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <form id="template-form" action="{{ route('settings.message-templates.update', $template->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-8">
                <!-- Basic Information -->
                <div>
                    <h4 class="section-title">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Template Name <span class="text-red-600">*</span></label>
                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="name" name="name" value="{{ old('name', $template->name) }}" required readonly>
                            <p class="mt-1 text-sm text-gray-500">Template name cannot be changed</p>
                </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="description" name="description" value="{{ old('description', $template->description) }}">
                            <p class="mt-1 text-sm text-gray-500">Brief description of template purpose</p>
                </div>
            </div>
                </div>

                <!-- Configuration Information -->
                <div>
                    <h4 class="section-title">Configuration</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="channel" class="block text-sm font-medium text-gray-700">Channel Type</label>
                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50" id="channel" value="{{ $templateTypeLabels[$template->channel] ?? ucfirst($template->channel) }}" readonly>
                            <input type="hidden" name="channel" value="{{ $template->channel }}">
                            <p class="mt-1 text-sm text-gray-500">Notification delivery channel</p>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Email Subject <span class="text-red-600">*</span></label>
                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="subject" name="subject" value="{{ old('subject', $template->subject) }}" required>
                            <p class="mt-1 text-sm text-gray-500">Supports variable insertion</p>
                </div>

                        <div>
                          
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Apply to Suppliers</label>
                                <div>
                                    <input type="radio" id="apply_to_all" name="supplier_type" value="all" {{ !$template->supplier_id ? 'checked' : '' }} class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                    <label for="apply_to_all" class="ml-2 text-sm text-gray-700">All Suppliers (Global Template)</label>
                                </div>
                                <div>
                                    <input type="radio" id="apply_to_specific" name="supplier_type" value="specific" {{ $template->supplier_id ? 'checked' : '' }} class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                    <label for="apply_to_specific" class="ml-2 text-sm text-gray-700">Specific Supplier</label>
                                        </div>
                                        </div>
                                    </div>
                                    
                        <div id="supplier-select-container" class="{{ $template->supplier_id ? '' : 'hidden' }}">
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Select Supplier</label>
                            <select name="supplier_id" id="supplier_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">Select a supplier</option>
                                @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $template->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
            </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="is_default" name="is_default" value="1" {{ old('is_default', $template->is_default) ? 'checked' : '' }}>
                                <label class="ml-2 block text-sm text-gray-900" for="is_default">Set as Default Template</label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 pl-6">Default templates are used when no specific template is assigned</p>
                </div>

                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="status" name="status" value="active" {{ old('status', $template->status) == 'active' ? 'checked' : '' }}>
                                <label class="ml-2 block text-sm text-gray-900" for="status">Active</label>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ old('status', $template->status) == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ old('status', $template->status) == 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 pl-6">Only active templates will be used</p>
                        </div>
                    </div>
                </div>

                <!-- Template Content -->
                <div>
                    <h4 class="section-title">Template Content</h4>
                    <div class="mb-6">
                        <label for="content" class="block text-sm font-medium text-gray-700">Content <span class="text-red-600">*</span></label>

                        <div class="mt-2">
                            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                                <div class="card-header-custom">
                                    <h5 class="text-sm font-medium text-gray-700 flex items-center">
                                        <svg class="h-5 w-5 text-indigo-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        Available Variables <small class="text-gray-500 ml-1">(Click to insert)</small>
                                    </h5>
                                </div>
                                <div class="p-4 variable-container">
                                    <div class="mb-3">
                                        <span class="variable-category common">Common</span>
                                        <span class="variable-category supplier">Supplier</span>
                                        @if($template->name)
                                            <span class="variable-category template">{{ ucfirst(str_replace('_', ' ', $template->name)) }}</span>
                                        @endif
            </div>

                                    <div class="common-variables">
                                        <h6 class="text-xs font-medium text-gray-700 mb-2">Common Variables:</h6>
                                        <div class="flex flex-wrap gap-1">
                                            <button type="button" class="var-btn px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded hover:bg-blue-200" data-var="app_name">App Name</button>
                                            <button type="button" class="var-btn px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded hover:bg-blue-200" data-var="app_url">App URL</button>
                                            <button type="button" class="var-btn px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded hover:bg-blue-200" data-var="current_date">Current Date</button>
                                            <button type="button" class="var-btn px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded hover:bg-blue-200" data-var="current_time">Current Time</button>
                                        </div>
                                    </div>

                                    <!-- Only show supplier variables when relevant for template type -->
                                    <div class="supplier-variables mt-4 {{ in_array($template->name, ['inventory_alert']) ? 'hidden' : '' }}">
                                        <h6 class="text-xs font-medium text-gray-700 mb-2">Supplier Variables:</h6>
                                        <div class="flex flex-wrap gap-1">
                                            <button type="button" class="var-btn px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded hover:bg-green-200" data-var="supplier_name">Supplier Name</button>
                                            <button type="button" class="var-btn px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded hover:bg-green-200" data-var="supplier_email">Supplier Email</button>
                                            <button type="button" class="var-btn px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded hover:bg-green-200" data-var="supplier_contact">Supplier Contact</button>
                                            <button type="button" class="var-btn px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded hover:bg-green-200" data-var="supplier_names">All Suppliers</button>
                                            <button type="button" class="var-btn px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded hover:bg-green-200" data-var="supplier_count">Supplier Count</button>
                        </div>
                                    </div>
                                    
                                    <!-- Template specific variables -->
                                    <div class="template-specific-variables mt-4">
                                        <h6 class="text-xs font-medium text-gray-700 mb-2">Template Specific Variables:</h6>
                                        
                                        <!-- Purchase Order Generated Variables -->
                                        <div class="template-vars purchase_order_generated {{ in_array($template->name, ['purchase_order_generated', 'default_purchase_order_generated']) ? '' : 'hidden' }}">
                                            <div class="flex flex-wrap gap-1">
                                                <button type="button" class="var-btn px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded hover:bg-indigo-200" data-var="purchase_count">Purchase Count</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded hover:bg-indigo-200" data-var="purchase_numbers">Purchase Numbers</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded hover:bg-indigo-200" data-var="user_name">User Name</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded hover:bg-indigo-200" data-var="user_email">User Email</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded hover:bg-indigo-200" data-var="purchase_number">Purchase Number</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded hover:bg-indigo-200" data-var="purchase_date">Purchase Date</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded hover:bg-indigo-200" data-var="purchase_total">Purchase Total</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded hover:bg-indigo-200" data-var="items_list">Items List</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Inventory Alert Variables -->
                                        <div class="template-vars inventory_alert {{ in_array($template->name, ['inventory_alert', 'default_inventory_alert']) ? '' : 'hidden' }}">
                                            <div class="flex flex-wrap gap-1">
                                                <button type="button" class="var-btn px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded hover:bg-red-200" data-var="product_name">Product Name</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded hover:bg-red-200" data-var="product_sku">Product SKU</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded hover:bg-red-200" data-var="current_stock">Current Stock</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded hover:bg-red-200" data-var="min_stock">Min Stock</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded hover:bg-red-200" data-var="warehouse_name">Warehouse Name</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Payment Overdue Variables -->
                                        <div class="template-vars payment_overdue {{ in_array($template->name, ['payment_overdue', 'default_payment_overdue']) ? '' : 'hidden' }}">
                                            <div class="flex flex-wrap gap-1">
                                                <button type="button" class="var-btn px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded hover:bg-yellow-200" data-var="customer_name">Customer Name</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded hover:bg-yellow-200" data-var="invoice_number">Invoice Number</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded hover:bg-yellow-200" data-var="invoice_date">Invoice Date</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded hover:bg-yellow-200" data-var="due_date">Due Date</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded hover:bg-yellow-200" data-var="amount_due">Amount Due</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded hover:bg-yellow-200" data-var="days_overdue">Days Overdue</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Quality Inspection Variables -->
                                        <div class="template-vars quality_inspection_created {{ in_array($template->name, ['quality_inspection_created', 'default_quality_inspection_created']) ? '' : 'hidden' }}">
                                            <div class="flex flex-wrap gap-1">
                                                <button type="button" class="var-btn px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded hover:bg-purple-200" data-var="inspection_number">Inspection Number</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded hover:bg-purple-200" data-var="product_name">Product Name</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded hover:bg-purple-200" data-var="created_by">Created By</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded hover:bg-purple-200" data-var="inspection_date">Inspection Date</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Supplier Order Notification Variables -->
                                        <div class="template-vars supplier_order_notification {{ in_array($template->name, ['supplier_order_notification', 'default_supplier_order_notification']) ? '' : 'hidden' }}">
                                            <div class="flex flex-wrap gap-1">
                                                <button type="button" class="var-btn px-2 py-1 bg-teal-100 text-teal-800 text-xs font-medium rounded hover:bg-teal-200" data-var="purchase_number">Purchase Number</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-teal-100 text-teal-800 text-xs font-medium rounded hover:bg-teal-200" data-var="purchase_date">Purchase Date</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-teal-100 text-teal-800 text-xs font-medium rounded hover:bg-teal-200" data-var="delivery_date">Delivery Date</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-teal-100 text-teal-800 text-xs font-medium rounded hover:bg-teal-200" data-var="purchase_total">Purchase Total</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-teal-100 text-teal-800 text-xs font-medium rounded hover:bg-teal-200" data-var="contact_person">Contact Person</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-teal-100 text-teal-800 text-xs font-medium rounded hover:bg-teal-200" data-var="contact_email">Contact Email</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-teal-100 text-teal-800 text-xs font-medium rounded hover:bg-teal-200" data-var="items_list">Items List</button>
                                    </div>
                                </div>
                                
                                        <!-- System Notification Variables -->
                                        <div class="template-vars system_notification {{ in_array($template->name, ['system_notification', 'default_system_notification']) ? '' : 'hidden' }}">
                                            <div class="flex flex-wrap gap-1">
                                                <button type="button" class="var-btn px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded hover:bg-blue-200" data-var="subject">Subject</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded hover:bg-blue-200" data-var="message">Message</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded hover:bg-blue-200" data-var="action_url">Action URL</button>
                                                <button type="button" class="var-btn px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded hover:bg-blue-200" data-var="action_text">Action Text</button>
                                            </div>
                                        </div>
                                        
                                        <!-- Default message when no specific variables -->
                                        <div class="template-vars default {{ in_array($template->name, [
                                            'purchase_order_generated', 'default_purchase_order_generated', 
                                            'inventory_alert', 'default_inventory_alert', 
                                            'payment_overdue', 'default_payment_overdue', 
                                            'quality_inspection_created', 'default_quality_inspection_created', 
                                            'supplier_order_notification', 'default_supplier_order_notification',
                                            'system_notification', 'default_system_notification'
                                        ]) ? 'hidden' : '' }}">
                                            <p class="text-sm text-gray-500 italic">No specific variables available for this template type.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <textarea class="mt-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 template-editor" id="content" name="content" rows="14" required>{{ old('content', $template->content) }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">HTML format, supports all HTML tags</p>
                    </div>
                </div>

                <!-- Preview section -->
                <div class="mt-6 mb-6">
                    <div class="flex items-center space-x-4">
                        <button type="button" id="preview-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 btn-gradient">
                            <svg class="mr-2 -ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview Template
                        </button>
                    </div>
                    
                    <div id="preview-container" class="mt-4 hidden">
                        <div class="flex justify-between items-center mb-4">
                            <h5 class="text-lg font-medium text-gray-900">Template Preview</h5>
                            <button type="button" class="inline-flex items-center p-1.5 border border-gray-300 rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="close-preview">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <hr class="mb-4">
                        <div id="preview-content" class="prose max-w-none"></div>
                    </div>
                </div>
            </div>

            <div class="pt-5 border-t border-gray-200">
                <div class="flex justify-start">
                    <a href="{{ route('settings.message-templates') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="mr-2 -ml-1 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Templates
                    </a>
                    <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 btn-gradient">
                        <svg class="mr-2 -ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Save Template
                </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// 立即执行函数确保全局变量封装，避免重复声明
(function() {
    // 先检查是否已存在模板编辑器实例，如果存在则不重新创建
    if (window.templateEditorInitialized) {
        console.log('Template editor already initialized, skipping...');
        return;
    }
    
    // 标记为已初始化
    window.templateEditorInitialized = true;
    
    // 定义全局模板编辑器对象
    window.templateEditor = {
        elements: {},
        data: {
            suppliers: [],
            content: '',
            templateName: "{{ $template->name }}",
            templateType: "{{ $template->type }}",
        },
        
        // 初始化编辑器
        init: function() {
            console.log('初始化模板编辑器');
            // 缓存DOM元素
            this.cacheElements();
            // 绑定事件处理
            this.bindEvents();
            // 初始化选择器
            this.initSelects();
            // 根据模板类型显示/隐藏供应商选择器
            this.toggleSupplierSelectorVisibility();
        },
        
        // 缓存DOM元素引用
        cacheElements: function() {
            // 使用安全的DOM选择器方法，确保元素存在
            this.elements = {
                form: document.getElementById('template-form'),
                previewBtn: document.getElementById('preview-btn'),
                previewContainer: document.getElementById('preview-container'),
                previewContent: document.getElementById('preview-content'),
                closePreviewBtn: document.getElementById('close-preview'),
                contentField: document.getElementById('content'),
                varButtons: document.querySelectorAll('.var-btn'),
                statusCheckbox: document.getElementById('status'),
                statusLabel: document.querySelector('label[for="status"]') ? document.querySelector('label[for="status"]').nextElementSibling : null,
                supplierToggle: document.getElementById('toggle-supplier-selector'),
                supplierSelector: document.getElementById('supplier-selector'),
                supplierSearch: document.querySelector('.supplier-search'),
                supplierItems: document.querySelectorAll('.supplier-item'),
                selectAllBtn: document.getElementById('select-all-suppliers'),
                deselectAllBtn: document.getElementById('deselect-all-suppliers'),
                supplierSelectorContainer: document.querySelector('.supplier-selector-container'),
                supplierCheckboxes: document.querySelectorAll('input[name="suppliers[]"]'),
                selectedSuppliersContainer: document.querySelector('.selected-suppliers'),
            };
        },
        
        // 绑定事件处理
        bindEvents: function() {
            // 变量按钮点击
            if (this.elements.varButtons && this.elements.varButtons.length > 0) {
                this.elements.varButtons.forEach(btn => {
                    btn.addEventListener('click', () => this.insertVariable(btn));
                });
            }
            
            // 预览按钮点击
            if (this.elements.previewBtn) {
                this.elements.previewBtn.addEventListener('click', () => this.previewTemplate());
            }
            
            // 关闭预览按钮
            if (this.elements.closePreviewBtn) {
                this.elements.closePreviewBtn.addEventListener('click', () => this.closePreview());
            }
            
            // 状态复选框变更
            if (this.elements.statusCheckbox && this.elements.statusLabel) {
                this.elements.statusCheckbox.addEventListener('change', () => this.updateStatusBadge());
            }
            
            // 供应商选择器相关事件
            if (this.elements.supplierToggle) {
                this.elements.supplierToggle.addEventListener('click', () => this.toggleSupplierSelector());
            }
            
            if (this.elements.supplierSearch) {
                this.elements.supplierSearch.addEventListener('input', () => this.searchSuppliers());
            }
            
            if (this.elements.selectAllBtn) {
                this.elements.selectAllBtn.addEventListener('click', () => this.selectAllSuppliers());
            }
            
            if (this.elements.deselectAllBtn) {
                this.elements.deselectAllBtn.addEventListener('click', () => this.deselectAllSuppliers());
            }
            
            // 供应商复选框变更
            if (this.elements.supplierCheckboxes && this.elements.supplierCheckboxes.length > 0) {
                this.elements.supplierCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', () => this.updateSelectedSuppliers());
                });
            }
        },
        
        // 根据模板类型显示/隐藏供应商选择器
        toggleSupplierSelectorVisibility: function() {
            if (!this.elements.supplierSelectorContainer) return;
            
            // 隐藏不需要供应商的模板类型的选择器
            const supplierSpecificTemplates = ['supplier_order_notification', 'purchase_order_generated', 'quality_inspection_created'];
            if (!supplierSpecificTemplates.includes(this.data.templateName)) {
                this.elements.supplierSelectorContainer.classList.add('hidden');
            } else {
                this.elements.supplierSelectorContainer.classList.remove('hidden');
            }
        },
        
        // 切换供应商选择器显示/隐藏
        toggleSupplierSelector: function() {
            if (!this.elements.supplierSelector || !this.elements.supplierToggle) return;
            
            this.elements.supplierSelector.classList.toggle('hidden');
            const icon = this.elements.supplierToggle.querySelector('.toggle-icon');
            if (icon) {
                icon.classList.toggle('rotate-180');
            }
        },
        
        // 搜索供应商
        searchSuppliers: function() {
            if (!this.elements.supplierSearch || !this.elements.supplierItems) return;
            
            const query = this.elements.supplierSearch.value.toLowerCase();
            this.elements.supplierItems.forEach(item => {
                const nameEl = item.querySelector('.text-gray-700');
                const emailEl = item.querySelector('.text-gray-500');
                
                if (!nameEl && !emailEl) return;
                
                const name = nameEl ? nameEl.textContent.toLowerCase() : '';
                const email = emailEl ? emailEl.textContent.toLowerCase() : '';
                
                if (name.includes(query) || email.includes(query)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        },
        
        // 全选供应商
        selectAllSuppliers: function() {
            if (!this.elements.supplierCheckboxes) return;
            
            this.elements.supplierCheckboxes.forEach(checkbox => {
                const item = checkbox.closest('.supplier-item');
                if (item && item.style.display !== 'none') {
                    checkbox.checked = true;
                }
            });
            
            this.updateSelectedSuppliers();
        },
        
        // 清空全部供应商
        deselectAllSuppliers: function() {
            if (!this.elements.supplierCheckboxes) return;
            
            this.elements.supplierCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            this.updateSelectedSuppliers();
        },
        
        // 更新选中的供应商
        updateSelectedSuppliers: function() {
            if (!this.elements.selectedSuppliersContainer || !this.elements.supplierCheckboxes) return;
            
            const selectedCheckboxes = Array.from(this.elements.supplierCheckboxes).filter(cb => cb.checked);
            
            if (selectedCheckboxes.length > 0) {
                const suppliers = selectedCheckboxes.map(checkbox => {
                    const item = checkbox.closest('.supplier-item');
                    if (!item) return '';
                    
                    const nameEl = item.querySelector('.text-gray-700');
                    if (!nameEl) return '';
                    
                    const name = nameEl.textContent;
                    return `<span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded">${name}</span>`;
                }).filter(name => name !== '');
                
                this.elements.selectedSuppliersContainer.innerHTML = `
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">Selected ${selectedCheckboxes.length} supplier(s):</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                            ${suppliers.join('')}
                        </div>
                    </div>
                `;
            } else {
                this.elements.selectedSuppliersContainer.innerHTML = '<div class="text-sm text-gray-500 italic">No suppliers selected</div>';
            }
        },
        
        // 初始化Select2
        initSelects: function() {
            // 兼容旧版Select2实现（保留以备不时之需）
            if (typeof $ !== 'undefined' && $.fn.select2 && document.querySelector('.select2-suppliers')) {
                $(document).ready(function() {
                    $('.select2-suppliers').select2({
                        placeholder: "Select suppliers",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('.select-wrapper')
        });
    });
            }
        },
        
        // 插入变量到编辑器
        insertVariable: function(button) {
            const variable = button.getAttribute('data-var');
            const field = this.elements.contentField;
            this.insertAtCursor(field, `{${variable}}`);
        },
    
    // 在光标位置插入文本
        insertAtCursor: function(field, text) {
        if (field.selectionStart || field.selectionStart === 0) {
            const startPos = field.selectionStart;
            const endPos = field.selectionEnd;
            field.value = field.value.substring(0, startPos) + text + field.value.substring(endPos, field.value.length);
            field.selectionStart = startPos + text.length;
            field.selectionEnd = startPos + text.length;
            field.focus();
        } else {
            field.value += text;
        }
        },
        
        // 预览模板
        previewTemplate: function() {
            const content = this.elements.contentField.value.trim();
        if (!content) {
                // 使用SweetAlert2（如果可用）
                if (window.Swal) {
                    Swal.fire({
                        title: '错误',
                        text: '请先输入模板内容再预览',
                        icon: 'error',
                        confirmButtonText: '确定'
                    });
                } else {
                    // 回退到标准alert
                    alert('请先输入模板内容再预览');
                }
            return;
        }
        
            // 获取选中的供应商ID
        let selectedSuppliers = [];
            
            // 使用新选择器获取选中的供应商
            document.querySelectorAll('input[name="suppliers[]"]:checked').forEach(checkbox => {
                selectedSuppliers.push(checkbox.value);
            });
        
        // 显示加载状态
            this.elements.previewContent.innerHTML = '<div class="flex justify-center items-center py-4"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> 加载预览中...</div>';
            this.elements.previewContainer.classList.remove('hidden');
        
        // 发送预览请求
        fetch('{{ route("settings.message-templates.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                content: content,
                    type: this.data.templateName,
                suppliers: selectedSuppliers
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                    this.elements.previewContent.innerHTML = data.html;
            } else {
                    this.elements.previewContent.innerHTML = `<div class="bg-red-50 border-l-4 border-red-400 p-4"><div class="text-red-700">${data.message}</div></div>`;
            }
        })
        .catch(error => {
                this.elements.previewContent.innerHTML = `<div class="bg-red-50 border-l-4 border-red-400 p-4"><div class="text-red-700">预览加载错误: ${error.message}</div></div>`;
        });
        },
    
    // 关闭预览
        closePreview: function() {
            this.elements.previewContainer.classList.add('hidden');
        },
        
        // 更新状态标签
        updateStatusBadge: function() {
            if (!this.elements.statusCheckbox || !this.elements.statusLabel) return;
            
            const isActive = this.elements.statusCheckbox.checked;
            this.elements.statusLabel.textContent = isActive ? 'Active' : 'Inactive';
            
            // 更新状态标签样式
            this.elements.statusLabel.className = `ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
        }
    };

    // 确保DOM加载后初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (window.templateEditor) {
                window.templateEditor.init();
            }
        });
    } else {
        // 如果DOM已加载完成，直接初始化
        if (window.templateEditor) {
            window.templateEditor.init();
        }
    }

    // 确保在所有资源加载完成后也进行初始化检查，以防页面迟加载的情况
    window.addEventListener('load', function() {
        if (window.templateEditor && !window.templateEditor.initialized) {
            console.log('Initializing template editor on window load');
            window.templateEditor.init();
            window.templateEditor.initialized = true;
        }
    });
})();
</script>
@endpush

@endsection 