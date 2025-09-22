@extends('layouts.app')

@section('title', 'Product management')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900 inline-flex items-center">
                <svg class="w-5 h-5 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>
                front page
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-400 ml-1 md:ml-2 text-sm font-medium">Product management</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Product list
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage all product information
                </p>
            </div>
            <div class="mt-4 md:mt-0 md:ml-4">
                <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add product
                </a>
                <a href="{{ route('products.discounts.bulk') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l1 1a1 1 0 010 1.414L10.414 7H13a1 1 0 110 2h-3v2h3a1 1 0 110 2h-3v2a1 1 0 01-2 0v-2H5a1 1 0 110-2h3V9H5a1 1 0 010-2h2.586L6.293 5.707a1 1 0 011.414-1.414l1 1A1 1 0 0110 5v-.001z" clip-rule="evenodd" />
                    </svg>
                    Bulk Discount
                </a>
            </div>
        </div>

        <!-- SPA容器：参数组合选择和链接功能 -->
        <div id="spa-container" class="mb-6">
            @if(isset($linkTemplate))
                <div class="p-4 bg-white rounded-lg shadow mb-4">
                    <h2 class="text-lg font-medium mb-2">{{ __('Link Products to Template') }}: {{ $linkTemplate->name }}</h2>
                    <p class="text-sm text-gray-600 mb-2">{{ __('Select products to link to this template.') }}</p>
                    
                    <!-- 参数链接区域 - 将动态更新 -->
                    <div id="parameter-combo-area">
                        @if(isset($parameterCombo))
                            <div class="my-4 p-3 bg-blue-100 border border-blue-300 rounded-md">
                                <h3 class="font-medium text-blue-800 mb-1">{{ __('Currently Linking Parameter Combination') }}:</h3>
                                <div class="flex flex-wrap gap-2 mt-2" id="current-parameter-combo">
                                    @foreach(explode(';', $parameterCombo) as $param)
                                        @php
                                            $parts = explode('=', $param);
                                            $paramName = $parts[0] ?? '';
                                            $paramValue = $parts[1] ?? '';
                                            @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500 text-white">
                                            {{ $paramName }}: {{ $paramValue }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @elseif(isset($parameterGroup) && isset($parameterValue))
                            <div class="my-4 p-3 bg-green-100 border border-green-300 rounded-md">
                                <h3 class="font-medium text-green-800 mb-1">{{ __('Currently Linking Parameter') }}:</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500 text-white">
                                    {{ $parameterGroup }}: {{ $parameterValue }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- 链接状态区域 - 将动态更新 -->
                    <div id="link-status-area">
                        @if(isset($linkedProduct))
                            <div class="mt-4 p-3 bg-green-100 border border-green-300 rounded-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-green-800">{{ __('Currently Linked Product') }}:</h4>
                                        <p class="text-sm text-green-700">
                                            {{ $linkedProduct->name }} ({{ $linkedProduct->sku }})
                                        </p>
                                    </div>
                                    
                                    <button type="button" class="unlink-btn px-3 py-1.5 bg-white border border-red-300 rounded-md text-sm text-red-600 hover:bg-red-50"
                                            data-template-id="{{ $linkTemplate->id }}"
                                            @if(isset($parameterCombo))
                                                data-parameter-group="{{ $parameterCombo }}"
                                                data-is-combo="true"
                                            @else
                                                data-parameter-group="{{ $parameterGroup }}={{ $parameterValue }}"
                                                data-is-combo="false"
                                            @endif
                                            >
                                        {{ __('Unlink Product') }}
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 p-3 bg-yellow-100 border border-yellow-300 rounded-md">
                                <p class="text-sm text-yellow-700">
                                    {{ __('No product linked yet. Select a product from the list below.') }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- 导航控制区域 -->
                    <div id="parameter-navigation" class="mt-4 flex justify-between border-t pt-4">
                        <button type="button" id="prev-combo-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Previous Combination
                        </button>
                        
                        <div class="text-sm text-gray-500" id="combo-navigation-status">
                            <span id="current-combo-index">-</span> / <span id="total-combo-count">-</span>
                        </div>
                        
                        <button type="button" id="next-combo-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Next Combination
                            <svg class="-mr-0.5 ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- 完成按钮区域 -->
                    <div class="mt-4 text-right">
                        <a href="{{ route('product-templates.show', $linkTemplate->id ?? 0) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Complete & Return to Template
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Search and screen -->
        <div class="mb-6">
            <form method="GET" action="{{ route('products.index') }}" id="filter-form">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="frontend-search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" id="frontend-search" name="search" 
                               value="{{ request('search') }}"
                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                               placeholder="Product name/SKU/Barcode">
                    </div>
                    <div>
                        <label for="frontend-category" class="block text-sm font-medium text-gray-700">Category</label>
                        <select id="frontend-category" name="category_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="frontend-status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="frontend-status" name="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">All Statuses</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label for="frontend-stock-status" class="block text-sm font-medium text-gray-700">Stock Status</label>
                        <select id="frontend-stock-status" name="stock_status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">All</option>
                            <option value="sufficient" {{ request('stock_status') === 'sufficient' ? 'selected' : '' }}>Sufficient Stock</option>
                            <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Clear Filters
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Product list -->
        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="products-table-body">
                                @forelse ($products as $product)
                                    <tr class="product-row" 
                                        data-id="{{ $product->id }}"
                                        data-product-id="{{ $product->id }}"
                                        data-name="{{ strtolower($product->name) }}" 
                                        data-sku="{{ strtolower($product->sku) }}" 
                                        data-barcode="{{ strtolower($product->barcode ?? '') }}" 
                                        data-category="{{ $product->category_id }}"
                                        data-status="{{ $product->is_active ? '1' : '0' }}"
                                        data-stock-status="{{ ($product->inventory_count ?? 0) > $product->min_stock ? 'sufficient' : (($product->inventory_count ?? 0) > 0 ? 'low' : 'out') }}">
                                        <td class="px-6 py-4 whitespace-nowrap max-w-xs truncate">
                                            <div class="flex items-center">
                                                @if($product->images && count($product->images) > 0)
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full object-cover" 
                                                             src="{{ $product->images[0] }}" 
                                                             alt="{{ $product->name }}">
                                                    </div>
                                                @endif
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $product->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        SKU: {{ $product->sku }}
                                                    </div>
                                                    @if($product->barcode)
                                                        <div class="text-sm text-gray-500">
                                                            Barcode: {{ $product->barcode }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $product->category->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $product->brand }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Price: RM {{ number_format($product->selling_price, 2) }}</div>
                                            <div class="text-sm text-gray-500">Cost: RM {{ number_format($product->cost_price, 2) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm {{ ($product->inventory_count ?? 0) <= $product->min_stock ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                                Stock: {{ $product->inventory_count ?? 0 }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Min: {{ $product->min_stock }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if(isset($linkTemplate))
                                                <form action="{{ route('product-templates.link-product') }}" method="POST" class="inline-block link-product-form">
                                                    @csrf
                                                    <input type="hidden" name="template_id" value="{{ $linkTemplate->id }}">
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    @if(isset($parameterGroup))
                                                        <input type="hidden" name="parameter_group" value="{{ $parameterGroup }}">
                                                    @endif
                                                    <button type="button" class="text-green-600 hover:text-green-900 mr-3 link-product-btn">
                                                        Link to Template
                                                    </button>
                                                </form>
                                            @else
                                            <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                View
                                            </a>
                                            <a href="{{ route('products.edit', $product) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                                Edit
                                            </a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline-block delete-product-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="text-red-600 hover:text-red-900 delete-product-btn" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                                                    Delete
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no-products-row">
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            No products found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $products->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection 

@push('styles')
<style>
    /* Basic Pagination Styling */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 1rem 0;
    }
    .pagination span,
    .pagination a {
        padding: 0.5rem 0.75rem;
        margin: 0 0.25rem;
        border-radius: 0.375rem; /* rounded-md */
        font-size: 0.875rem; /* text-sm */
        line-height: 1.25rem;
        text-decoration: none;
    }
    .pagination span.disabled {
        color: #9ca3af; /* text-gray-400 */
        background-color: #f3f4f6; /* bg-gray-100 */
        cursor: not-allowed;
    }
    .pagination a {
        color: #4f46e5; /* text-indigo-600 */
        background-color: #ffffff; /* bg-white */
        border: 1px solid #e5e7eb; /* border-gray-200 */
        transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
    }
    .pagination a:hover {
        background-color: #eef2ff; /* hover:bg-indigo-50 */
        color: #4338ca; /* hover:text-indigo-700 */
    }
    .pagination .active span {
        background-color: #4f46e5; /* bg-indigo-600 */
        color: #ffffff; /* text-white */
        border-color: #4f46e5; /* border-indigo-600 */
    }
</style>
@endpush

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- 存储模板数据 -->
<script>
// 全局变量 - 这些变量可以在整个页面范围内访问
window.linkTemplate = @json(isset($linkTemplate) ? [
    'id' => $linkTemplate->id, 
    'name' => $linkTemplate->name, 
    'parameters' => is_array($linkTemplate->parameters) ? $linkTemplate->parameters : []
] : null);

// 当前参数组
window.currentParameterGroup = @json($parameterGroup ?? null);
window.currentParameterCombo = @json($parameterCombo ?? null);

// 存储所有参数组合
window.allParameterCombinations = [];
window.linkedParameterCombinations = [];

// 禁用产品分析追踪
if (window.ProductAnalytics) {
    window.ProductAnalytics.disabled = true;
}
</script>

<script>
(function() {
    // 使用全局变量，而不是重新声明
    // 避免变量覆盖冲突
    let isLoadingCombinations = false;
    let isInitialized = false;
    
    // 初始化产品页面
    function initializeProductPage() {
        if (isInitialized) return;
        isInitialized = true;
        
        console.log('Initializing products page (Backend Filtering Mode)');
        
        // 设置删除按钮事件
        setupDeleteButtons();
        
        // 产品链接相关功能
        const linkTemplate = window.linkTemplate;
        if (linkTemplate) {
            // 使用全局变量
            if (window.currentParameterCombo) {
                setupParameterComboNavigation();
                initParameterComboHandlers();
                preloadParameterCombinations();
            }
            
            // 设置解除链接按钮事件
            setupUnlinkButtons();
        }
    }
    
    // 预加载所有参数组合
    function preloadParameterCombinations() {
        if (isLoadingCombinations) return;
        isLoadingCombinations = true;
        
        const templateId = window.linkTemplate?.id || 0;
        if (!templateId) return;
        
        // 显示加载状态
        const loadingToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            didOpen: (toast) => {
                Swal.showLoading();
            }
        });
        
        loadingToast.fire({
            title: 'Loading parameter combinations...'
        });
        
        updateNavigationStatus('Loading combinations...', '');
        
        // 从备用文件获取所有参数组合
        console.log('Loading parameter combinations from param_debug.php for template:', templateId);
        fetch(`/param_debug.php?template_id=${templateId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Failed to load parameter combinations: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Loaded parameter combinations:', data);
            
            if (data.status === 'error') {
                throw new Error(data.message || 'Unknown error loading parameter combinations');
            }
            
            // 使用全局变量
            window.allParameterCombinations = data.combinations || [];
            window.linkedParameterCombinations = window.allParameterCombinations
                .filter(combo => combo.is_linked)
                .map(combo => combo.parameter_group_string);
            
            console.log(`Loaded ${window.allParameterCombinations.length} parameter combinations, ${window.linkedParameterCombinations.length} linked`);
            
            // 更新当前参数组合显示
            const combo = window.allParameterCombinations.find(c => c.parameter_group_string === window.currentParameterCombo);
            if (combo) {
                updateParameterComboDisplay(window.currentParameterCombo, combo.product);
            }
            
            // 更新导航状态 - 确保清除加载信息
            updateComboNavigationStatus();
            
            // 检查组合数量和当前状态，更新状态信息
            if (window.allParameterCombinations.length > 0) {
                const currentCombo = window.currentParameterCombo;
                const currentIndex = window.allParameterCombinations.findIndex(c => c.parameter_group_string === currentCombo);
                
                if (currentIndex !== -1) {
                    const params = currentCombo.split(';').map(p => {
                        const parts = p.split('=');
                        return `${parts[0]}: ${parts[1]}`;
                    }).join(', ');
                    
                    updateNavigationStatus(
                        `Combination ${currentIndex + 1} of ${window.allParameterCombinations.length}`, 
                        `Parameters: ${params}`
                    );
                } else {
                    updateNavigationStatus(
                        `${window.allParameterCombinations.length} combinations available`,
                        'Select a parameter combination to continue'
                    );
                }
            } else {
                updateNavigationStatus('No parameter combinations available', 'Template has no parameters defined');
            }
            
            isLoadingCombinations = false;
            
            // 明确关闭SweetAlert加载通知
            Swal.close();
            
            // 显示成功通知
            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            
            toast.fire({
                icon: 'success',
                title: `Loaded ${window.allParameterCombinations.length} combinations`
            });
            
            // 确保链接按钮正常工作
            setupLinkButtons();
        })
        .catch(error => {
            console.error('Failed to load parameter combinations:', error);
            isLoadingCombinations = false;
            
            // 关闭任何打开的加载提示
            Swal.close();
            
            // 更新导航状态显示错误
            updateNavigationStatus('Error loading combinations', error.message || 'Unknown error');
            
            // 显示更详细的错误信息
            const errorMessage = error.message || 'Unknown error';
            
            Swal.fire({
                icon: 'error',
                title: 'Failed to load parameter combinations',
                html: `
                    <div class="text-left">
                        <p class="mb-2">Error: ${errorMessage}</p>
                        <div class="text-xs bg-gray-100 p-2 rounded overflow-auto max-h-40">
                            <pre>${error.stack || ''}</pre>
                        </div>
                        <p class="mt-2 text-sm">Please try refreshing the page or contact support if the problem persists.</p>
                    </div>
                `,
                confirmButtonText: 'Refresh Page',
                showCancelButton: true,
                cancelButtonText: 'Continue Anyway'
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        });
    }
    
    // 设置参数组合导航
    function setupParameterComboNavigation() {
        console.log('Setting up parameter combo navigation');
        const prevBtn = document.getElementById('prev-combo-btn');
        const nextBtn = document.getElementById('next-combo-btn');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                navigateToParameterCombo('prev');
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                navigateToParameterCombo('next');
            });
        }
    }
    
    // 设置链接产品按钮
    function setupLinkButtons() {
        console.log('Setting up link product buttons');
        const linkButtons = document.querySelectorAll('.link-product-btn');
        linkButtons.forEach(button => {
            // 移除现有事件处理以避免重复绑定
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                const form = this.closest('.link-product-form');
                if (!form) {
                    console.error("未找到链接表单");
                    return;
                }
                
                const templateId = form.querySelector('input[name="template_id"]').value;
                const productId = form.querySelector('input[name="product_id"]').value;
                
                // 获取产品名称
                const productRow = this.closest('tr');
                const productName = productRow.querySelector('.text-sm.font-medium.text-gray-900')?.textContent.trim();
                
                // 获取参数组和参数组合
                const urlParams = new URLSearchParams(window.location.search);
                const parameterCombo = urlParams.get('parameter_combo');
                const parameterGroup = urlParams.get('parameter_group');
                const parameterValue = urlParams.get('parameter_value');
                
                let requestData = {
                    template_id: templateId,
                    product_id: productId
                };
                let endpoint = '';
                
                if (parameterCombo) {
                    // 参数组合模式
                    requestData.parameter_combo = parameterCombo;
                    endpoint = '{{ route("product-templates.link-parameter-combo") }}';
                } else if (parameterGroup && parameterValue) {
                    // 单参数模式
                    requestData.parameter_group = parameterGroup;
                    requestData.parameter_value = parameterValue;
                    endpoint = '{{ route("products.store-parameter-value-link") }}';
                } else {
                    // 显示简短的通知
                    const toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    
                    toast.fire({
                        icon: 'error',
                        title: 'Missing parameter information'
                    });
                    return;
                }
                
                console.log('Linking product with data:', requestData, 'Product name:', productName);
                
                // 显示加载中
                const loadingToast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    didOpen: (toast) => {
                        Swal.showLoading();
                    }
                });
                
                loadingToast.fire({
                    title: `Linking product "${productName}"...`
                });
                
                // 发送请求
                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Link response:', data);
                    
                    // 关闭加载提示
                    Swal.close();
                    
                    if (data.status === 'success' || data.status === 'info' || !data.status) {
                        let successTitle = 'Link Successful';
                        let successMessage = data.message || `Product "${productName}" has been linked successfully`;
                        
                        // 显示成功通知
                        const toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        
                        toast.fire({
                            icon: 'success',
                            title: successMessage
                        });
                        
                        // 刷新当前页面或切换到下一个参数组合
                        if (parameterCombo) {
                            // 更新参数组合状态
                            if (window.allParameterCombinations.length > 0) {
                                const comboIndex = window.allParameterCombinations.findIndex(
                                    c => c.parameter_group_string === parameterCombo
                                );
                                if (comboIndex !== -1) {
                                    window.allParameterCombinations[comboIndex].is_linked = true;
                                    window.allParameterCombinations[comboIndex].product = data.product || null;
                                    if (!window.linkedParameterCombinations.includes(parameterCombo)) {
                                        window.linkedParameterCombinations.push(parameterCombo);
                                    }
                                }
                            }
                            
                            // 刷新当前组合数据
                            loadParameterComboData(parameterCombo);
                            
                            // 延迟后查找下一个未链接的组合
                            setTimeout(() => {
                                findNextUnlinkedParameterCombo(templateId, parameterCombo, nextCombo => {
                                    // 显示切换到下一个组合的通知
                                    const switchToast = Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true
                                    });
                                    
                                    switchToast.fire({
                                        icon: 'info',
                                        title: 'Moving to next unlinked combination...'
                                    });
                                    
                                    loadParameterComboData(nextCombo);
                                });
                            }, 1500);
                        } else {
                            // 其他情况简单刷新页面
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    } else {
                        // 显示错误通知
                        Swal.fire({
                            icon: 'error',
                            title: 'Link Failed',
                            text: data.message || 'Failed to link product, please try again'
                        });
                    }
                })
                .catch(error => {
                    console.error('Link request error:', error);
                    
                    // 关闭加载提示
                    Swal.close();
                    
                    // 显示错误通知
                    Swal.fire({
                        icon: 'error',
                        title: 'Link Failed',
                        text: error.message || 'Network error, please try again'
                    });
                });
            });
        });
    }
    
    // 更新参数组合导航状态
    function updateComboNavigationStatus() {
        if (!window.allParameterCombinations.length) return;
        
        const currentIndex = window.allParameterCombinations.findIndex(
            combo => combo.parameter_group_string === window.currentParameterCombo
        );
        
        const currentIndexElement = document.getElementById('current-combo-index');
        const totalCountElement = document.getElementById('total-combo-count');
        
        if (currentIndexElement && totalCountElement) {
            currentIndexElement.textContent = (currentIndex !== -1) ? (currentIndex + 1) : '-';
            totalCountElement.textContent = window.allParameterCombinations.length;
        }
        
        // 更新按钮状态
        const prevBtn = document.getElementById('prev-combo-btn');
        const nextBtn = document.getElementById('next-combo-btn');
        
        if (prevBtn) {
            prevBtn.disabled = currentIndex <= 0;
            if (prevBtn.disabled) {
                prevBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                prevBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        
        if (nextBtn) {
            const hasNext = currentIndex < window.allParameterCombinations.length - 1;
            nextBtn.disabled = !hasNext;
            if (nextBtn.disabled) {
                nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }
    
    // 更新导航状态显示
    function updateNavigationStatus(message, details) {
        const statusElement = document.getElementById('combo-navigation-status');
        if (statusElement) {
            statusElement.innerHTML = `
                <span class="text-gray-500">${message}</span>
                ${details ? `<span class="text-xs text-gray-400 block mt-1">${details}</span>` : ''}
            `;
        }
    }
    
    // 导航到指定的参数组合（上一个/下一个）
    function navigateToParameterCombo(direction) {
        if (!window.allParameterCombinations.length) {
            console.error('Cannot navigate: parameter combinations not loaded');
            return;
        }
        
        const currentIndex = window.allParameterCombinations.findIndex(
            combo => combo.parameter_group_string === window.currentParameterCombo
        );
        
        if (currentIndex === -1) {
            console.error('Cannot navigate: current combination not found');
            return;
        }
        
        let targetIndex;
        if (direction === 'next') {
            targetIndex = Math.min(currentIndex + 1, window.allParameterCombinations.length - 1);
        } else {
            targetIndex = Math.max(currentIndex - 1, 0);
        }
        
        if (targetIndex !== currentIndex) {
            // 显示导航过渡通知
            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1000,
                timerProgressBar: true
            });
            
            toast.fire({
                icon: 'info',
                title: direction === 'next' ? 'Moving to next combination...' : 'Moving to previous combination...'
            });
            
            loadParameterComboData(window.allParameterCombinations[targetIndex].parameter_group_string);
        }
    }
    
    // 加载参数组合数据（通过AJAX，无需刷新页面）
    function loadParameterComboData(paramCombo) {
        const templateId = window.linkTemplate?.id || 0;
        if (!templateId || !paramCombo) return;
        
        // 显示加载状态
        const loadingToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            didOpen: (toast) => {
                Swal.showLoading();
            }
        });
        
        loadingToast.fire({
            title: 'Loading parameter combination...'
        });
        
        // 如果已经加载了所有组合数据，从本地缓存获取
        if (window.allParameterCombinations.length > 0) {
            const combo = window.allParameterCombinations.find(c => c.parameter_group_string === paramCombo);
            if (combo) {
                console.log('Found parameter combo in local cache:', combo);
                
                // 更新URL，但不刷新页面
                const url = new URL(window.location.href);
                url.searchParams.set('link_template', templateId);
                url.searchParams.set('parameter_combo', paramCombo);
                window.history.pushState({ path: url.toString() }, '', url.toString());
                
                // 更新参数组合显示
                updateParameterComboDisplay(paramCombo, combo.product);
                
                // 更新当前参数组合
                window.currentParameterCombo = paramCombo;
                
                // 更新导航状态
                updateComboNavigationStatus();
                
                // 确保链接按钮可用
                setupLinkButtons();
                
                // 关闭加载提示
                Swal.close();
                
                // 显示切换成功通知
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                
                toast.fire({
                    icon: 'success',
                    title: `Switched to ${paramCombo.split(';').join(', ')}`
                });
                
                return;
            }
        }
        
        // 如果本地缓存中没有，从数据文件获取
        // 先获取所有参数组合，再筛选出当前组合
        fetch(`/param_debug.php?template_id=${templateId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Failed to load parameter combinations: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Loaded parameter combinations for data lookup:', data);
            
            // 更新本地缓存
            window.allParameterCombinations = data.combinations || [];
            window.linkedParameterCombinations = window.allParameterCombinations
                .filter(combo => combo.is_linked)
                .map(combo => combo.parameter_group_string);
                
            // 找到当前参数组合
            const combo = window.allParameterCombinations.find(c => c.parameter_group_string === paramCombo);
            if (!combo) {
                throw new Error(`Parameter combination "${paramCombo}" not found`);
            }
            
            // 更新URL，但不刷新页面
            const url = new URL(window.location.href);
            url.searchParams.set('link_template', templateId);
            url.searchParams.set('parameter_combo', paramCombo);
            window.history.pushState({ path: url.toString() }, '', url.toString());
            
            // 更新参数组合显示
            updateParameterComboDisplay(paramCombo, combo.product);
            
            // 更新当前参数组合
            window.currentParameterCombo = paramCombo;
            
            // 更新导航状态
            updateComboNavigationStatus();
            
            // 确保链接按钮可用
            setupLinkButtons();
            
            // 关闭加载提示
            Swal.close();
            
            // 显示切换成功通知
            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            
            const params = paramCombo.split(';').map(p => {
                const parts = p.split('=');
                return `${parts[0]}: ${parts[1]}`;
            }).join(', ');
            
            toast.fire({
                icon: 'success',
                title: `Switched to ${params}`
            });
        })
        .catch(error => {
            console.error('Failed to load parameter combo data:', error);
            
            // 确保关闭加载通知
            Swal.close();
            
            // 显示错误通知
            Swal.fire({
                icon: 'error',
                title: 'Failed to load parameter combination',
                text: error.message,
                confirmButtonText: 'Try Again',
                showCancelButton: true,
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    loadParameterComboData(paramCombo);
                } else {
                    Swal.close();
                }
            });
        });
    }
    
    // 更新参数组合显示
    function updateParameterComboDisplay(paramCombo, linkedProduct) {
        console.log('Updating parameter combo display:', paramCombo, linkedProduct);
        
        // 更新参数组合显示
        const parameterComboArea = document.getElementById('parameter-combo-area');
        if (parameterComboArea) {
            let paramHtml = '';
            if (paramCombo) {
                paramHtml = `
                    <div class="my-4 p-3 bg-blue-100 border border-blue-300 rounded-md">
                        <h3 class="font-medium text-blue-800 mb-1">Currently Linking Parameter Combination:</h3>
                        <div class="flex flex-wrap gap-2 mt-2" id="current-parameter-combo">
                `;
                
                const params = paramCombo.split(';');
                for (const param of params) {
                    const parts = param.split('=');
                    const paramName = parts[0] || '';
                    const paramValue = parts[1] || '';
                    
                    paramHtml += `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500 text-white">
                            ${paramName}: ${paramValue}
                        </span>
                    `;
                }
                
                paramHtml += `
                        </div>
                    </div>
                `;
            }
            
            parameterComboArea.innerHTML = paramHtml;
        } else {
            console.warn('Parameter combo area not found in DOM');
        }
        
        // 更新链接状态
        const linkStatusArea = document.getElementById('link-status-area');
        if (linkStatusArea) {
            const templateId = window.linkTemplate?.id || 0;
            
            if (linkedProduct) {
                linkStatusArea.innerHTML = `
                    <div class="mt-4 p-3 bg-green-100 border border-green-300 rounded-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-green-800">Currently Linked Product:</h4>
                                <p class="text-sm text-green-700">
                                    ${linkedProduct.name} (${linkedProduct.sku})
                                </p>
                            </div>
                            
                            <button type="button" class="unlink-btn px-3 py-1.5 bg-white border border-red-300 rounded-md text-sm text-red-600 hover:bg-red-50"
                                    data-template-id="${templateId}"
                                    data-parameter-group="${paramCombo}"
                                    data-is-combo="true">
                                Unlink Product
                            </button>
                        </div>
                    </div>
                `;
            } else {
                linkStatusArea.innerHTML = `
                    <div class="mt-4 p-3 bg-yellow-100 border border-yellow-300 rounded-md">
                        <p class="text-sm text-yellow-700">
                            No product linked yet. Select a product from the list below.
                        </p>
                    </div>
                `;
            }
            
            // 重新绑定解除链接按钮
            setupUnlinkButtons();
        } else {
            console.warn('Link status area not found in DOM');
        }
        
        // 更新导航状态，确保不再显示"Loading combinations..."
        if (paramCombo) {
            const currentIndex = window.allParameterCombinations.findIndex(
                combo => combo.parameter_group_string === paramCombo
            );
            
            if (currentIndex !== -1) {
                const params = paramCombo.split(';').map(p => {
                    const parts = p.split('=');
                    return `${parts[0]}: ${parts[1]}`;
                }).join(', ');
                
                updateNavigationStatus(
                    `Combination ${currentIndex + 1} of ${window.allParameterCombinations.length}`, 
                    `Parameters: ${params}`
                );
            } else {
                updateNavigationStatus(
                    `Viewing parameter combination`,
                    paramCombo.replace(/;/g, ', ').replace(/=/g, ': ')
                );
            }
        }
    }
    
    // 设置解除链接按钮事件
    function setupUnlinkButtons() {
        const unlinkButtons = document.querySelectorAll('.unlink-btn');
        unlinkButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const templateId = this.getAttribute('data-template-id');
                const parameterGroup = this.getAttribute('data-parameter-group');
                const isCombo = this.getAttribute('data-is-combo') === 'true';
                
                // 解除链接确认
                Swal.fire({
                    title: 'Unlink Confirmation',
                    text: 'Are you sure you want to unlink this product?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, unlink it',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    customClass: {
                        confirmButton: 'text-white',
                        cancelButton: 'text-white'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (isCombo) {
                            unlinkParameterCombo(templateId, parameterGroup);
                        } else {
                            unlinkProduct(templateId, parameterGroup, false, '{{ route("products.unlink-parameter-value") }}');
                        }
                    }
                });
            });
        });
    }

    // 找到下一个未链接的参数组合（仅使用本地缓存，不再调用API）
    function findNextUnlinkedParameterCombo(templateId, currentCombo, onNextComboFound) {
        if (!templateId || !currentCombo) {
            console.error('Missing required parameters for finding next combination');
            return;
        }
        
        // 显示正在搜索的通知
        const loadingToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            didOpen: (toast) => {
                Swal.showLoading();
            }
        });
        
        loadingToast.fire({
            title: 'Finding next unlinked combination...'
        });
        
        // 如果参数组合已经加载，使用本地缓存查找
        if (window.allParameterCombinations.length > 0) {
            console.log('Using local cache to find next combination');
            
            // 找到当前组合的索引
            const currentIndex = window.allParameterCombinations.findIndex(
                combo => combo.parameter_group_string === currentCombo
            );
            
            // 查找下一个未链接的组合
            let nextCombo = null;
            for (let i = currentIndex + 1; i < window.allParameterCombinations.length; i++) {
                if (!window.allParameterCombinations[i].is_linked) {
                    nextCombo = window.allParameterCombinations[i].parameter_group_string;
                    break;
                }
            }
            
            // 如果没找到，从头开始找
            if (!nextCombo) {
                for (let i = 0; i < currentIndex; i++) {
                    if (!window.allParameterCombinations[i].is_linked) {
                        nextCombo = window.allParameterCombinations[i].parameter_group_string;
                        break;
                    }
                }
            }
            
            // 关闭加载提示
            Swal.close();
            
            if (nextCombo) {
                onNextComboFound(nextCombo);
            } else {
                Swal.fire({
                    title: 'Completed!',
                    text: 'All parameter combinations have been linked.',
                    icon: 'success',
                    confirmButtonText: 'View Template Details'
                }).then(() => {
                    window.location.href = `/product-templates/${templateId}`;
                });
            }
            
            return;
        }
        
        // 如果本地缓存不可用，重新加载所有参数组合
        console.log('Local cache not available, loading all combinations');
        fetch(`/param_debug.php?template_id=${templateId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Failed to load parameter combinations: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Loaded parameter combinations for next combo lookup:', data);
            
            // 更新本地缓存
            window.allParameterCombinations = data.combinations || [];
            window.linkedParameterCombinations = window.allParameterCombinations
                .filter(combo => combo.is_linked)
                .map(combo => combo.parameter_group_string);
            
            // 找到当前组合的索引
            const currentIndex = window.allParameterCombinations.findIndex(
                combo => combo.parameter_group_string === currentCombo
            );
            
            // 查找下一个未链接的组合
            let nextCombo = null;
            for (let i = currentIndex + 1; i < window.allParameterCombinations.length; i++) {
                if (!window.allParameterCombinations[i].is_linked) {
                    nextCombo = window.allParameterCombinations[i].parameter_group_string;
                    break;
                }
            }
            
            // 如果没找到，从头开始找
            if (!nextCombo) {
                for (let i = 0; i < currentIndex; i++) {
                    if (!window.allParameterCombinations[i].is_linked) {
                        nextCombo = window.allParameterCombinations[i].parameter_group_string;
                        break;
                    }
                }
            }
            
            // 关闭加载提示
            Swal.close();
            
            if (nextCombo) {
                onNextComboFound(nextCombo);
            } else {
                Swal.fire({
                    title: 'Completed!',
                    text: 'All parameter combinations have been linked.',
                    icon: 'success',
                    confirmButtonText: 'View Template Details'
                }).then(() => {
                    window.location.href = `/product-templates/${templateId}`;
                });
            }
        })
        .catch(error => {
            console.error('Failed to load parameter combinations:', error);
            
            // 关闭加载提示
            Swal.close();
            
            // 显示错误通知
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to find next parameter combination: ' + error.message,
                confirmButtonText: 'OK'
            });
        });
    }

    // 解除产品链接
    function unlinkProduct(templateId, parameterGroup, isCombo, endpoint) {
        // 显示加载中
        const loadingToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            didOpen: (toast) => {
                Swal.showLoading();
            }
        });
        
        loadingToast.fire({
            title: 'Unlinking product...'
        });
        
        // 发送请求
        fetch(endpoint, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(isCombo ? {
                template_id: templateId,
                parameter_group: parameterGroup
            } : (() => {
                // 对于非组合参数，parameterGroup格式为 "group=value"
                const parts = parameterGroup.split('=');
                if (parts.length === 2) {
                    return {
                        template_id: templateId,
                        parameter_group: parts[0],
                        parameter_value: parts[1]
                    };
                } else {
                    return {
                        template_id: templateId,
                        parameter_group: parameterGroup
                    };
                }
            })())
        })
        .then(response => response.json())
        .then(data => {
            console.log("Unlink response:", data);
            
            // 关闭加载提示
            Swal.close();
            
            if (data.status === 'success' || !data.status) {
                // 显示成功通知
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                
                toast.fire({
                    icon: 'success',
                    title: data.message || 'Product link successfully removed'
                });
                
                // 刷新当前参数组合数据（无需刷新整页）
                loadParameterComboData(window.currentParameterCombo);
                
                // 更新本地参数组合状态
                if (window.allParameterCombinations.length > 0) {
                    const comboIndex = window.allParameterCombinations.findIndex(c => c.parameter_group_string === parameterGroup);
                    if (comboIndex !== -1) {
                        window.allParameterCombinations[comboIndex].is_linked = false;
                        const linkedIndex = window.linkedParameterCombinations.indexOf(parameterGroup);
                        if (linkedIndex !== -1) {
                            window.linkedParameterCombinations.splice(linkedIndex, 1);
                        }
                    }
                }
            } else {
                // 显示错误通知
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                
                toast.fire({
                    icon: 'error',
                    title: data.message || 'Failed to unlink, please try again'
                });
            }
        })
        .catch(error => {
            console.error("Unlink request error:", error);
            
            // 关闭加载提示
            Swal.close();
            
            // 显示错误通知
            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            toast.fire({
                icon: 'error',
                title: 'Network error, please try again'
            });
        });
    }

    // 初始化参数组合处理
    function initParameterComboHandlers() {
        // ... existing code ...
    }

    // 解除参数组合链接
    function unlinkParameterCombo(templateId, parameterGroup) {
        // 显示加载中通知
        const loadingToast = Swal.mixin({
            toast: true,
            position: 'top-end', 
            showConfirmButton: false,
            didOpen: (toast) => {
                Swal.showLoading();
            }
        });
        
        loadingToast.fire({
            title: 'Unlinking...'
        });
        
        // 发送请求
        fetch('{{ route("product-templates.unlink-parameter-combo") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                template_id: templateId,
                parameter_group: parameterGroup
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log("Unlink response:", data);
            
            // 关闭加载提示
            Swal.close();
            
            if (data.status === 'success' || !data.status) {
                // 显示成功通知
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                
                toast.fire({
                    icon: 'success',
                    title: data.message || 'Parameter combination link removed successfully'
                });
                
                // 更新本地参数组合状态
                if (window.allParameterCombinations.length > 0) {
                    const comboIndex = window.allParameterCombinations.findIndex(c => c.parameter_group_string === parameterGroup);
                    if (comboIndex !== -1) {
                        window.allParameterCombinations[comboIndex].is_linked = false;
                        const linkedIndex = window.linkedParameterCombinations.indexOf(parameterGroup);
                        if (linkedIndex !== -1) {
                            window.linkedParameterCombinations.splice(linkedIndex, 1);
                        }
                    }
                }
                
                // 重新加载参数组合数据（无需刷新页面）
                loadParameterComboData(parameterGroup);
            } else {
                // 显示错误通知
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                
                toast.fire({
                    icon: 'error',
                    title: data.message || 'Failed to remove link, please try again'
                });
            }
        })
        .catch(error => {
            console.error("Unlink request error:", error);
            
            // 关闭加载提示
            Swal.close();
            
            // 显示错误通知
            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            toast.fire({
                icon: 'error',
                title: 'Network error, please try again'
            });
        });
    }

    // 设置删除按钮事件
    function setupDeleteButtons() {
        console.log('Setting up delete product buttons');
        // 使用事件委托，避免每次都要为每个按钮绑定事件
        document.addEventListener('click', function(e) {
            const deleteButton = e.target.closest('.delete-product-btn');
            if (!deleteButton) return;
            
            e.preventDefault();
            
            const productId = deleteButton.getAttribute('data-product-id');
            const productName = deleteButton.getAttribute('data-product-name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete "${productName}". This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 获取删除表单并提交
                    const form = deleteButton.closest('form');
                    if (form) {
                        console.log('Submitting delete form for product:', productId);
                        form.submit();
                    } else {
                        console.error('Delete form not found for product:', productId);
                    }
                }
            });
        });
    }

    // 监听各种页面加载事件，确保在各种环境下都能正确初始化
    document.addEventListener('DOMContentLoaded', initializeProductPage);
    document.addEventListener('turbolinks:load', initializeProductPage);
    document.addEventListener('page:load', initializeProductPage);
    document.addEventListener('turbo:load', initializeProductPage);
    document.addEventListener('livewire:load', initializeProductPage);
})();
</script>
@endpush 

