@extends('layouts.app')

@section('title', 'Create a product')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Top navigation bar -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Create a product</h1>
                        <p class="mt-1 text-sm text-gray-500">Fill in product information, bring * The number is required</p>
                        @if(isset($template))
                        <div class="mt-2">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Creating from template: {{ $template->name }}
                            </span>
                        </div>
                        @endif
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Return to the list
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Centralized error messages container -->
        <div id="form-error-container" class="mb-6 sticky top-20 z-10"></div>
        <form id="productForm" action="{{ route('products.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="temp_id" value="{{ $tempId }}">
            @if(isset($template))
            <input type="hidden" name="template_id" value="{{ $template->id }}">
            @endif

            <!-- Debugging information display -->
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                {{ session('error') }}
                            </p>
                            @if(session('debug_info'))
                                <pre class="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded">{{ print_r(session('debug_info'), true) }}</pre>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Form verification error:</h3>
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

            <div class="grid grid-cols-12 gap-6">
                <!-- Main information on the left -->
                <div class="col-span-12 lg:col-span-8 space-y-6">
                    <!-- Product basic information card -->
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-medium text-gray-900">Basic information</h2>
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    Main product information
                                </span>
                            </div>
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 required-field">
                                        Product Category
                                    </label>
                                    <select id="category_id" 
                                        name="category_id" 
                                        required
                                        class="mt-1 block w-full pl-3 pr-10 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                                        style="padding-top: 0.625rem; padding-bottom: 0.625rem; min-height: 42px; line-height: 1.25;">
                                        <option value="">Please select a category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                {{ old('category_id', isset($template) ? $template->category_id : '') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <p class="mt-2 text-sm text-red-600 bg-red-50 px-3 py-2 rounded-md">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="name" class="block text-sm font-medium text-gray-700 required-field">
                                        Product Name
                                    </label>
                                    <input type="text" 
                                        name="name" 
                                        id="name" 
                                        required
                                        value="{{ old('name', isset($template) ? $template->name : '') }}"
                                        placeholder="Enter a product name"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="brand" class="block text-sm font-medium text-gray-700">
                                        brand
                                    </label>
                                    <input type="text" 
                                        name="brand" 
                                        id="brand" 
                                        value="{{ old('brand', isset($template) ? $template->brand : '') }}"
                                        placeholder="Enter product brand"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('brand')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="sm:col-span-3">
                                    <label for="model" class="block text-sm font-medium text-gray-700">
                                        Model
                                    </label>
                                    <input type="text" 
                                        name="model" 
                                        id="model" 
                                        value="{{ old('model', isset($template) ? $template->model : '') }}"
                                        placeholder="Enter product model"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('model')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="sku" class="block text-sm font-medium text-gray-700 required-field">
                                        SKU coding
                                    </label>
                                    <input type="text" 
                                        name="sku" 
                                        id="sku" 
                                        required
                                        value="{{ old('sku') }}"
                                        placeholder="enter SKU coding"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('sku')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="barcode" class="block text-sm font-medium text-gray-700">
                                        Barcode
                                    </label>
                                    <input type="text" 
                                        name="barcode" 
                                        id="barcode" 
                                        value="{{ old('barcode') }}"
                                        placeholder="Enter the product barcode"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('barcode')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Product Description
                                    </label>
                                    <textarea id="description" 
                                        name="description" 
                                        rows="3"
                                        placeholder="Describe the characteristics and uses of the product in detail"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description', isset($template) ? $template->description : '') }}</textarea>
                                    @error('description')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Variant Options -->
                    @if(isset($variantOptions) && count($variantOptions) > 0)
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-medium text-gray-900">Variant Options</h2>
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                    From Template
                                </span>
                            </div>
                            <div class="space-y-4">
                                @foreach($variantOptions as $option)
                                <div class="border border-gray-200 rounded-md p-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900">{{ $option->name }}</h3>
                                            <p class="text-xs text-gray-500">{{ $option->type }}</p>
                                        </div>
                                        @if($option->pivot->is_required)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Required
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Optional
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <label for="variant_option_{{ $option->id }}" class="block text-sm font-medium text-gray-700">
                                            Value
                                        </label>
                                        @if(!empty($option->options))
                                            <select 
                                                id="variant_option_{{ $option->id }}" 
                                                name="variant_options[{{ $option->id }}]"
                                                @if($option->pivot->is_required) required @endif
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                <option value="">Select a value</option>
                                                @foreach($option->options as $optValue)
                                                    <option value="{{ $optValue }}">{{ $optValue }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" 
                                                id="variant_option_{{ $option->id }}" 
                                                name="variant_options[{{ $option->id }}]"
                                                @if($option->pivot->is_required) required @endif
                                                placeholder="Enter a value"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Product parameter card -->
                    <div id="parameters-card" class="bg-white shadow-sm rounded-lg overflow-hidden" style="display: none;">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-medium text-gray-900">Product parameters</h2>
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    Specifications
                                </span>
                            </div>
                            <div id="parameters-container" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Parameters will be dynamically inserted here -->
                            </div>
                        </div>
                    </div>

                    <!-- Price information card -->
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-medium text-gray-900">Price information</h2>
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    Sales Pricing
                                </span>
                            </div>
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="selling_price" class="block text-sm font-medium text-gray-700 required-field">
                                        Sales price
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm font-medium">RM</span>
                                        </div>
                                        <input type="number" 
                                            name="selling_price" 
                                            id="selling_price" 
                                            required
                                            value="{{ old('selling_price') }}"
                                            step="0.01"
                                            min="0"
                                            placeholder="0.00"
                                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-12 border-gray-300 rounded-md"
                                            style="padding-top: 0.625rem; padding-bottom: 0.625rem; min-height: 42px;">
                                    </div>
                                    @error('selling_price')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="min_stock" class="block text-sm font-medium text-gray-700">
                                        Minimum inventory
                                    </label>
                                    <input type="number" 
                                        name="min_stock" 
                                        id="min_stock" 
                                        value="{{ old('min_stock') }}"
                                        min="0"
                                        placeholder="Set inventory warning value"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('min_stock')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload the picture on the right -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden sticky top-6">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-medium text-gray-900">Product pictures</h2>
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-800">
                                    most5open
                                </span>
                            </div>
                            <div class="bg-gray-50 rounded-lg">
                                <div class="p-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-center">
                                            <div class="w-full">
                                                <x-image-uploader
                                                    :temp-id="$tempId"
                                                    model-type="products"
                                                    :max-files="5"
                                                />
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-center text-sm text-gray-600">
                                            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Suggested size800x800More than pixels</span>
                                        </div>
                                        <div class="flex flex-wrap gap-2 justify-center text-xs text-gray-500">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                JPG/PNG/GIF
                                            </span>
                                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                maximum2MB
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <span class="text-red-500">*</span> Indicates required
                        </div>
                        <div class="flex space-x-3">
                            <button type="button" onclick="window.history.back()" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                            <button type="submit" 
                                class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Save the product
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.required-field::after {
    content: " *";
    color: #EF4444;
}

/* Beautify the input box style */
input[type="text"],
input[type="number"],
input[type="email"],
input[type="password"],
input[type="search"],
input[type="tel"],
input[type="url"],
textarea,
select {
    @apply block w-full rounded-lg border-gray-300 shadow-sm transition duration-150 ease-in-out;
    padding-top: 0.625rem;
    padding-bottom: 0.625rem;
    min-height: 42px;
    line-height: 1.25;
}

input[type="text"]:hover,
input[type="number"]:hover,
input[type="email"]:hover,
input[type="password"]:hover,
input[type="search"]:hover,
input[type="tel"]:hover,
input[type="url"]:hover,
textarea:hover,
select:hover {
    @apply border-gray-400;
}

input[type="text"]:focus,
input[type="number"]:focus,
input[type="email"]:focus,
input[type="password"]:focus,
input[type="search"]:focus,
input[type="tel"]:focus,
input[type="url"]:focus,
textarea:focus,
select:focus {
    @apply border-indigo-500 ring-1 ring-indigo-500 ring-opacity-50;
}

/* 调整表单元素内容与标签的间距 */
.mt-1 {
    margin-top: 0.375rem !important;
}

/* 调整表单组之间的间距 */
.gap-y-6 {
    row-gap: 1.75rem !important;
}

/* Beautify placeholder text */
::placeholder {
    @apply text-gray-400;
}

/* Beautify required marks */
.required-field::after {
    @apply text-red-500 ml-0.5;
    content: "*";
}

/* Beautify form tags */
label {
    @apply block text-sm font-medium text-gray-700;
    margin-bottom: 0.375rem;
}

/* Beautify error messages */
.error-message {
    @apply mt-1 text-sm text-red-600;
}

/* 全局错误消息样式 */
.mt-2.text-sm.text-red-600,
p.mt-2.text-sm.text-red-600 {
    @apply bg-red-50 px-3 py-2 rounded-md border border-red-100;
    margin-top: 0.5rem;
    display: block;
}

/* Beautify the input box group */
.input-group {
    @apply relative rounded-md shadow-sm;
}

.input-group-text {
    @apply absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 sm:text-sm;
}

.input-group input {
    @apply pl-12;
}

/* 调整带前缀的输入框样式 */
.absolute.inset-y-0.left-0.pl-3.flex.items-center.pointer-events-none {
    height: 100%;
    display: flex;
    align-items: center;
}

.relative.rounded-md.shadow-sm input {
    padding-left: 2.5rem;
}

/* Beautify the drop-down selection box */
select {
    @apply pr-10 bg-white;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* Beautify the disabled state */
input:disabled,
select:disabled,
textarea:disabled {
    @apply bg-gray-100 cursor-not-allowed;
}

/* Beautify read-only state */
input:read-only,
textarea:read-only {
    @apply bg-gray-50;
}

/* Beautify form grouping */
.form-group {
    @apply mb-4;
}

/* Beautify help text */
.help-text {
    @apply mt-1 text-sm text-gray-500;
}

/* 添加全局默认边距调整 */
.p-6 {
    padding: 1.75rem !important;
}

/* 表单组容器内边距 */
.bg-white.shadow-sm.rounded-lg .p-6 {
    padding: 1.75rem !important;
}
</style>

@push('scripts')
<script src="{{ asset('js/product-category-params.js') }}"></script>
<script>
    // IIFE to avoid global namespace pollution
    (function() {
        // Track if initialization has occurred
        let initialized = false;
        
        function showError(message, field = null) {
            // 使用专门的错误容器显示错误
            const errorContainer = document.getElementById('form-error-container');
            if (!errorContainer) return;
            
            // 创建错误提示元素
        const errorDiv = document.createElement('div');
            errorDiv.className = 'p-4 text-sm text-red-700 bg-red-100 rounded-lg alert-error border border-red-300 shadow-sm';
            errorDiv.setAttribute('role', 'alert');
            
            // 创建内容区
            const content = document.createElement('div');
            content.className = 'flex items-center';
            
            // 添加图标
            const icon = document.createElement('div');
            icon.innerHTML = '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';
            content.appendChild(icon);
            
            // 添加错误消息
            const messageSpan = document.createElement('span');
            messageSpan.textContent = message;
            content.appendChild(messageSpan);
            
            errorDiv.appendChild(content);
            
            // 如果提供了字段名，添加额外提示
            if (field) {
                const fieldInfo = document.createElement('div');
                fieldInfo.className = 'mt-2 text-xs border-t border-red-200 pt-2';
                fieldInfo.innerHTML = `Please check the <strong>${field}</strong> field and try again.`;
                errorDiv.appendChild(fieldInfo);
                
                // 同时高亮显示对应的字段
                const fieldElement = document.getElementById(field.toLowerCase());
                if (fieldElement) {
                    fieldElement.classList.add('border-red-500');
                    fieldElement.focus();
                    
                    // 高亮标签
                    const label = document.querySelector(`label[for="${fieldElement.id}"]`);
                    if (label) {
                        label.classList.add('text-red-500');
                    }
                    
                    // 滚动到对应字段
                    setTimeout(() => {
                        fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                }
            }
        
        // 添加关闭按钮
        const closeButton = document.createElement('button');
            closeButton.className = 'absolute top-2 right-2 text-red-700 hover:text-red-900';
        closeButton.innerHTML = '&times;';
            closeButton.addEventListener('click', function() {
            errorDiv.remove();
            });
        errorDiv.appendChild(closeButton);
        
            // 设置相对定位以便绝对定位的关闭按钮
            errorDiv.style.position = 'relative';
            
            // 清空错误容器并添加新错误
            errorContainer.innerHTML = '';
            errorContainer.appendChild(errorDiv);
            
            // 滚动到错误容器
        setTimeout(() => {
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
            
            // 8秒后自动移除
            setTimeout(function() {
                if (errorContainer.contains(errorDiv)) {
                    errorDiv.remove();
                }
            }, 8000);
        }

        function validateField(field) {
            let isValid = true;
            const fieldId = field.id;
            const fieldName = field.getAttribute('data-name') || fieldId;
            
            // Remove existing error styling
            field.classList.remove('border-red-500');
            const label = document.querySelector(`label[for="${fieldId}"]`);
            if (label) {
                label.classList.remove('text-red-500');
            }
            
            // Required field validation
            if (field.hasAttribute('required') && !field.value.trim()) {
                isValid = false;
                field.classList.add('border-red-500');
                if (label) {
                    label.classList.add('text-red-500');
                }
                return { isValid, message: `${fieldName} is required.` };
            }
            
            // Specific field validations
            switch (fieldId) {
                case 'selling_price':
                    const sellingPrice = parseFloat(field.value);
                    if (field.value && (isNaN(sellingPrice) || sellingPrice <= 0)) {
                        isValid = false;
                        field.classList.add('border-red-500');
                        if (label) {
                            label.classList.add('text-red-500');
                        }
                        return { isValid, message: 'Selling price must be a positive number.' };
                    }
                    break;
                
                case 'min_stock':
                    const minStock = parseInt(field.value);
                    if (field.value && (isNaN(minStock) || minStock < 0)) {
                        isValid = false;
                        field.classList.add('border-red-500');
                        if (label) {
                            label.classList.add('text-red-500');
                        }
                        return { isValid, message: 'Minimum stock must be a non-negative number.' };
                    }
                    break;
                
                case 'sku':
                    if (field.value && !/^[A-Za-z0-9-]+$/.test(field.value)) {
                        isValid = false;
                        field.classList.add('border-red-500');
                        if (label) {
                            label.classList.add('text-red-500');
                        }
                        return { isValid, message: 'SKU must contain only letters, numbers, and hyphens.' };
                    }
                    break;
                
                case 'barcode':
                    if (field.value && !/^[0-9]+$/.test(field.value)) {
                        isValid = false;
                        field.classList.add('border-red-500');
                        if (label) {
                            label.classList.add('text-red-500');
                        }
                        return { isValid, message: 'Barcode must contain only numbers.' };
                    }
                    break;
            }
            
            return { isValid, message: '' };
        }

        function validateForm() {
            let isValid = true;
            let firstInvalidField = null;
            let firstErrorMessage = '';
            
            // Save parameter values immediately before validation
            saveParameterValues();
            
            // Check all fields with validation
            const fields = document.querySelectorAll('#productForm input, #productForm select, #productForm textarea');
            fields.forEach(field => {
                const result = validateField(field);
                if (!result.isValid) {
                    isValid = false;
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                        firstErrorMessage = result.message;
                    }
                }
            });
            
            // Show first error if any
            if (!isValid && firstErrorMessage) {
                showError(firstErrorMessage, firstInvalidField.getAttribute('data-name') || firstInvalidField.id);
                firstInvalidField.focus();
            }
            
            return isValid;
        }

    function handleFormSubmit(event) {
            if (!validateForm()) {
                event.preventDefault();
                return false;
            }
            
            // Form is valid, save parameters and allow submission
            saveParameterValues();
            return true;
        }

        // Highlight error fields based on session flash
        function highlightErrorFields() {
            // Check if we have an error_field in the session
            const errorFieldElement = document.querySelector('.alert-error .mt-2 strong');
            
            if (errorFieldElement) {
                const fieldName = errorFieldElement.textContent;
                const field = document.getElementById(fieldName.toLowerCase());
                if (field) {
                    field.classList.add('border-red-500');
                    field.focus();
                    const label = document.querySelector(`label[for="${field.id}"]`);
                    if (label) {
                        label.classList.add('text-red-500');
                    }
                    
                    // 获取错误消息
                    const errorMessageElement = document.querySelector('.alert-error');
                    if (errorMessageElement) {
                        const errorMessage = errorMessageElement.textContent.trim();
                        // 使用新的错误显示方式展示错误
                        showError(errorMessage, fieldName);
                    }
                }
            }
            
            // 处理Laravel验证错误
            const formErrorBag = document.querySelectorAll('.bg-red-50.border-l-4.border-red-400.p-4.mb-6 .text-red-700 ul li');
            if (formErrorBag && formErrorBag.length > 0) {
                // 提取所有错误消息
                const errorMessages = Array.from(formErrorBag).map(el => el.textContent.trim());
                
                // 显示第一个错误
                if (errorMessages.length > 0) {
                    showError(errorMessages[0], '');
                    
                    // 如果有多个错误，在控制台显示所有错误
                    if (errorMessages.length > 1) {
                        console.log('All form errors:', errorMessages);
                    }
                }
            }
        }

    function saveParameterValues() {
            const parameterInputs = document.querySelectorAll('[name^="parameters["]');
        const values = {};
        
            let hasValues = false;
            parameterInputs.forEach(input => {
                if (input.name) {
            values[input.name] = input.value;
                    hasValues = true;
                }
            });
            
            if (hasValues) {
                console.log('Saving parameters:', Object.keys(values).length);
                localStorage.setItem('product_parameters_values', JSON.stringify(values));
                
                // 记录保存时间
                localStorage.setItem('parameters_saved_time', new Date().getTime());
            }
        }

    function restoreParameterValues() {
            const savedValues = localStorage.getItem('product_parameters_values');
        if (!savedValues) return;
        
        try {
            const values = JSON.parse(savedValues);
                
                // 检查是否有存储的参数值
                if (Object.keys(values).length === 0) {
                    return;
                }
                
                console.log('Attempting to restore parameters');
                
                // 先尝试立即恢复参数值
                let restored = false;
                for (const name in values) {
                const input = document.querySelector(`[name="${name}"]`);
                if (input) {
                    input.value = values[name];
                        restored = true;
                        console.log('Immediately restored parameter:', name);
                    }
                }
                
                // 不管是否立即恢复成功，都设置监听器监控参数区域加载
                setupParameterContainerObserver(values);
                
        } catch (e) {
            console.error('Error restoring parameter values:', e);
        }
    }

        // 使用MutationObserver监控参数容器的变化
        function setupParameterContainerObserver(values) {
            const observer = new MutationObserver((mutations) => {
                const parametersCard = document.getElementById('parameters-card');
                const parametersContainer = document.getElementById('parameters-container');
                
                if (parametersCard && 
                    parametersContainer && 
                    parametersCard.style.display !== 'none' &&
                    parametersContainer.querySelectorAll('input, select').length > 0) {
                    
                    console.log('Parameters container loaded, restoring values');
                    
                    // 延迟执行以确保所有DOM元素都已完全加载
                    setTimeout(() => {
                        for (const name in values) {
                            const input = document.querySelector(`[name="${name}"]`);
                            if (input) {
                                input.value = values[name];
                                console.log('Restored parameter via observer:', name);
                            }
                        }
                    }, 300);
                    
                    // 停止观察
                    observer.disconnect();
                }
            });
            
            // 开始观察文档变化
            observer.observe(document.body, { 
                childList: true, 
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class']
            });
            
            // 同时使用定时器作为后备方案
            let checkCount = 0;
            const checkInterval = setInterval(() => {
                checkCount++;
                const parametersCard = document.getElementById('parameters-card');
                const parametersContainer = document.getElementById('parameters-container');
                
                if (parametersCard && 
                    parametersContainer && 
                    parametersCard.style.display !== 'none' &&
                    parametersContainer.querySelectorAll('input, select').length > 0) {
                    
                    for (const name in values) {
                        const input = document.querySelector(`[name="${name}"]`);
                        if (input) {
                            input.value = values[name];
                            console.log('Restored parameter via interval:', name);
                        }
                    }
                    
                    clearInterval(checkInterval);
                }
                
                // 最多检查30次（15秒）后停止
                if (checkCount > 30) {
                    clearInterval(checkInterval);
                }
            }, 500);
            
            // 设置最大检查时间
            setTimeout(() => {
                observer.disconnect();
                clearInterval(checkInterval);
                console.log('Parameter restoration monitoring timed out');
            }, 15000);
        }
        
        // 处理分类选择变化，保存当前表单值
        function setupCategoryChangeHandler() {
            const categorySelect = document.getElementById('category_id');
            if (!categorySelect) return;
            
            categorySelect.addEventListener('change', function() {
                // 先保存所有参数值
                saveParameterValues();
                
                // 记录当前选中的分类
                localStorage.setItem('last_selected_category_id', this.value);
                
                // 延迟一段时间后检查参数是否已加载并恢复
                setTimeout(() => {
                    restoreParameterValues();
                }, 1000);
            });
        }

        function initializeProductForm() {
            if (initialized) return;
            
            const form = document.getElementById('productForm');
            if (!form) return;
            
            // 添加数据属性以便更好的错误信息展示
            const fieldNames = {
                'sku': 'SKU',
                'barcode': 'Barcode',
                'name': 'Product name',
                'selling_price': 'Selling price',
                'min_stock': 'Minimum stock',
                'category_id': 'Category'
            };
            
            Object.entries(fieldNames).forEach(([id, name]) => {
                const field = document.getElementById(id);
                if (field) {
                    field.setAttribute('data-name', name);
                }
            });
            
            // 设置输入事件监听器
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                // 输入时移除错误样式
                input.addEventListener('input', function() {
                    this.classList.remove('border-red-500');
                    const label = document.querySelector(`label[for="${this.id}"]`);
                    if (label) {
                        label.classList.remove('text-red-500');
                    }
                });
                
                // 失去焦点时验证
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                // 参数字段值变化时保存
                if (input.name && input.name.startsWith('parameters[')) {
                    input.addEventListener('change', function() {
                        saveParameterValues();
                    });
                }
            });
            
            // 设置表单提交事件
            form.addEventListener('submit', handleFormSubmit);
            
            // 设置分类变化处理
            setupCategoryChangeHandler();
            
            // 恢复参数值
            restoreParameterValues();
            
            // 高亮显示错误字段
            highlightErrorFields();
            
            // 全局监听参数区域出现的事件
            document.addEventListener('DOMNodeInserted', function(e) {
                if (e.target.id === 'parameters-container' || 
                    (e.target.nodeType === 1 && e.target.querySelector('#parameters-container'))) {
                    console.log('Parameters container inserted, restoring values');
                    setTimeout(restoreParameterValues, 300);
                }
            });
            
            initialized = true;
        }

        // Multiple initialization points to handle different page loading scenarios
        function initializeElements() {
    initializeProductForm();
}

        // Standard DOM ready
        document.addEventListener('DOMContentLoaded', initializeElements);
        
        // For Livewire
        document.addEventListener('livewire:load', initializeElements);
        
        // For Turbolinks
        document.addEventListener('turbolinks:load', initializeElements);
        
        // For Alpine.js if used
        if (window.Alpine) {
            window.Alpine.start();
        }
        
        // Initialize immediately if document is already loaded
if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(initializeElements, 1);
}
    })();
</script>
@endpush

@endsection 