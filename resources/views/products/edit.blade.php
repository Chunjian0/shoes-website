@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Top navigation bar -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
                        <p class="mt-1 text-sm text-gray-500">Modify product information, with * The number is required</p>
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
        <form action="{{ route('products.update', $product->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

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
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">Please select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                                <div class="sm:col-span-3">
                                    <label for="name" class="block text-sm font-medium text-gray-700 required-field">
                                        Product Name
                                    </label>
                                    <input type="text" 
                                        name="name" 
                                        id="name" 
                                        value="{{ old('name', $product->name) }}"
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
                                        value="{{ old('brand', $product->brand) }}"
                                        placeholder="Enter product brand"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('brand')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="sku" class="block text-sm font-medium text-gray-700 required-field">
                                        SKUcoding
                                    </label>
                                    <input type="text" 
                                        name="sku" 
                                        id="sku" 
                                        value="{{ old('sku', $product->sku) }}"
                                        placeholder="enterSKUcoding"
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
                                        value="{{ old('barcode', $product->barcode) }}"
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
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
                    </div>

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
                                            <span class="text-gray-500 sm:text-sm">RM</span>
                                        </div>
                                        <input type="number" 
                                            name="selling_price" 
                                            id="selling_price" 
                                            value="{{ old('selling_price', $product->selling_price) }}"
                                            step="0.01"
                                            min="0"
                                            placeholder="0.00"
                                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-12 sm:text-sm border-gray-300 rounded-md">
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
                                        value="{{ old('min_stock', $product->min_stock) }}"
                                        min="0"
                                        placeholder="Enter the minimum inventory quantity"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
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
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-medium text-gray-900">Product pictures</h2>
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    Image Management
                                </span>
            </div>
            
                            <div class="sm:col-span-6">
                                <label class="block text-sm font-medium text-gray-700">Product Images</label>
                                {{-- Pass the prepared array to the component --}}
                                <x-image-uploader :images="$imagesForAlpine ?? []" :model-id="$product->id" model-type="products" />
                            </div>
                </div>
            </div>
                </div>
            </div>

            <!-- 促销折扣设置 -->
            <div class="mt-6 bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Promotional Discount Settings</h3>
                    <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- 折扣百分比 -->
                        <div class="sm:col-span-2">
                            <label for="discount_percentage" class="block text-sm font-medium text-gray-700">Discount Percentage</label>
                            <div class="mt-1 flex items-center">
                                <input type="number" min="0" max="100" step="0.01" name="discount_percentage" id="discount_percentage" 
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                    value="{{ old('discount_percentage', $product->discount_percentage ?? 0) }}">
                                <span class="ml-2 text-gray-500">%</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Enter a value between 0 and 100, for example, 20 means a 20% discount (80% of the original price)</p>
                        </div>
                        
                        <!-- 折扣时间段 -->
                        <div class="sm:col-span-4">
                            <label class="block text-sm font-medium text-gray-700">Discount Time Period</label>
                            <div class="mt-1 grid grid-cols-2 gap-4">
                                <div>
                                    <label for="discount_start_date" class="block text-xs text-gray-500">Start Date</label>
                                    <input type="datetime-local" name="discount_start_date" id="discount_start_date" 
                                        class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        value="{{ old('discount_start_date', $product->discount_start_date ? $product->discount_start_date->format('Y-m-d\TH:i') : '') }}">
                                </div>
                                <div>
                                    <label for="discount_end_date" class="block text-xs text-gray-500">End Date</label>
                                    <input type="datetime-local" name="discount_end_date" id="discount_end_date" 
                                        class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        value="{{ old('discount_end_date', $product->discount_end_date ? $product->discount_end_date->format('Y-m-d\TH:i') : '') }}">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quantity limit -->
                        <div class="sm:col-span-3">
                            <label for="min_quantity_for_discount" class="block text-sm font-medium text-gray-700">Minimum Purchase Quantity</label>
                            <div class="mt-1">
                                <input type="number" min="0" step="1" name="min_quantity_for_discount" id="min_quantity_for_discount" 
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    value="{{ old('min_quantity_for_discount', $product->min_quantity_for_discount ?? 0) }}">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Purchase this quantity or more to enjoy the discount, 0 means no limit</p>
                        </div>
                        
                        <div class="sm:col-span-3">
                            <label for="max_quantity_for_discount" class="block text-sm font-medium text-gray-700">Maximum Purchase Quantity</label>
                            <div class="mt-1">
                                <input type="number" min="0" step="1" name="max_quantity_for_discount" id="max_quantity_for_discount" 
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    value="{{ old('max_quantity_for_discount', $product->max_quantity_for_discount ?? 0) }}">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">The maximum number of discounts you can enjoy, 0 means no limit</p>
                        </div>
                        
                        <!-- 折扣预览 -->
                        <div class="sm:col-span-6 mt-4 bg-gray-50 p-4 rounded-lg border border-gray-200" id="discount-preview">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Discount Preview</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Original Price</p>
                                    <p class="text-base font-medium text-gray-900">¥{{ number_format($product->selling_price, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Discounted Price</p>
                                    <p class="text-base font-medium text-green-600" id="discounted-price">
                                        ¥{{ number_format($product->selling_price * (1 - ($product->discount_percentage / 100)), 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>

            <!-- Bottom Save Button -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <span class="text-red-500">*</span> It is required
                        </div>
                        <div class="flex items-center space-x-4">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Modify
                </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// 使用IIFE（立即执行函数表达式）避免全局变量重复声明
(function() {
    // 使用window对象存储状态，避免重复声明
    if (!window.productEditInitialized) {
        window.productEditInitialized = true;
        window.productParameters = @json($product->parameters ?? []);
        window.productEditParameters = [];
    }

    // 初始化函数，用于在页面加载时或Turbolinks加载时重新初始化变量和事件
    function initializeProductEdit() {
        console.log('初始化产品编辑页面');
        
        // 重新获取DOM元素
        const categorySelect = document.getElementById('category_id');
        const parametersCard = document.getElementById('parameters-card');
        const parametersContainer = document.getElementById('parameters-container');
        const productForm = document.querySelector('form[action*="products"]');
        
        console.log('表单元素:', productForm);
        console.log('分类选择元素:', categorySelect);
        console.log('当前选择的分类:', categorySelect?.value);
        console.log('产品参数:', window.productParameters);
        
        // 不再克隆和替换整个表单，而是重新绑定事件
        if (productForm) {
            // 移除所有已有的事件监听器
            const oldForm = productForm;
            
            // 确保表单提交正常工作
            oldForm.addEventListener('submit', function(e) {
                console.log('表单正在提交...');
                // 确保提交不被阻止
                return true;
            });
            
            console.log('表单提交事件已重新绑定');
        }
        
        // 如果分类已选择，加载参数
        if (categorySelect && categorySelect.value) {
            console.log('页面加载时分类已选择，自动加载参数');
            loadParameters(categorySelect.value, parametersCard, parametersContainer);
        }
        
        // 监听分类选择变化
        if (categorySelect) {
            // 移除原有事件监听器
            const newCategorySelect = categorySelect.cloneNode(true);
            categorySelect.parentNode.replaceChild(newCategorySelect, categorySelect);
            
            // 添加新的事件监听器
            newCategorySelect.addEventListener('change', function() {
                console.log('分类选择已更改:', this.value);
                loadParameters(this.value, parametersCard, parametersContainer);
            });
        }
        
        // 初始化折扣预览
        initializeDiscountPreview();
        
        // 处理保存按钮 - 不再替换按钮，直接添加事件监听
        const saveButton = document.querySelector('button[type="submit"]');
        if (saveButton) {
            // 清除现有事件 (通过克隆和替换)
            const newSaveButton = saveButton.cloneNode(true);
            saveButton.parentNode.replaceChild(newSaveButton, saveButton);
            
            // 添加新的点击事件
            newSaveButton.addEventListener('click', function(e) {
                console.log('保存按钮被点击');
                // 手动触发表单提交
                if (productForm) {
                    console.log('手动提交表单');
                    setTimeout(function() {
                        productForm.submit();
                    }, 10);
                }
            });
        }
        
        // 初始化Alpine.js组件
        initializeAlpineComponents();
    }
    
    // 初始化Alpine.js组件
    function initializeAlpineComponents() {
        console.log('初始化Alpine.js组件');
        
        // 查找所有使用x-data的Alpine组件
        document.querySelectorAll('[x-data]').forEach(el => {
            // 如果组件已经初始化，跳过
            if (el._x_dataStack) return;
            
            // 触发Alpine重新评估此元素
            if (window.Alpine) {
                try {
                    // 尝试初始化Alpine组件
                    window.Alpine.initTree(el);
                    console.log('已重新初始化Alpine组件:', el);
                } catch (error) {
                    console.error('Alpine组件初始化失败:', error);
                }
            }
        });
    }

    // 加载参数的函数
    async function loadParameters(categoryId, parametersCard, parametersContainer) {
        console.log('开始加载参数，分类ID:', categoryId);
        
        if (!categoryId) {
            console.log('未选择分类，隐藏参数卡片');
            window.productEditParameters = [];
            if (parametersCard) parametersCard.style.display = 'none';
            return;
        }

        try {
            console.log('发送请求获取参数数据...');
            const response = await fetch(`/product-categories/${categoryId}/parameters`);
            console.log('收到响应:', response.status);
            
            const result = await response.json();
            console.log('解析响应数据:', result);
            
            if (result.status === 'success') {
                window.productEditParameters = result.data;
                console.log('参数加载成功:', window.productEditParameters);
                console.log('现有产品参数:', window.productParameters);
                if (parametersCard) parametersCard.style.display = 'block';
                renderParameters(parametersContainer);
            } else {
                console.error('加载参数失败:', result.message);
            }
        } catch (error) {
            console.error('加载参数时发生错误:', error);
        }
    }

    function renderParameters(parametersContainer) {
        if (!window.productEditParameters.length || !parametersContainer) {
            const parametersCard = document.getElementById('parameters-card');
            if (parametersCard) parametersCard.style.display = 'none';
            return;
        }

        const parametersCard = document.getElementById('parameters-card');
        if (parametersCard) parametersCard.style.display = 'block';
        parametersContainer.innerHTML = '';

        window.productEditParameters.forEach(param => {
            const div = document.createElement('div');
            
            const labelDiv = document.createElement('div');
            labelDiv.className = 'flex items-center justify-between mb-2';
            
            const label = document.createElement('label');
            label.className = `block text-sm font-medium text-gray-700 ${param.is_required ? 'required-field' : ''}`;
            label.textContent = param.name;
            label.setAttribute('for', `parameters.${param.code}`);
            
            const typeSpan = document.createElement('span');
            typeSpan.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                param.type === 'select' ? 'bg-blue-100 text-blue-800' :
                param.type === 'number' ? 'bg-green-100 text-green-800' :
                'bg-gray-100 text-gray-800'
            }`;
            typeSpan.textContent = param.type === 'select' ? 'Selection' : 
                                param.type === 'number' ? 'Value' : 'text';
            
            labelDiv.appendChild(label);
            labelDiv.appendChild(typeSpan);
            div.appendChild(labelDiv);

            if (param.type === 'select') {
                const select = document.createElement('select');
                select.className = 'mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md';
                select.name = `parameters[${param.code}]`;
                select.id = `parameters.${param.code}`;
                select.required = param.is_required;

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Please select';
                select.appendChild(defaultOption);

                param.options.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option;
                    optionElement.textContent = option;
                    if (window.productParameters && window.productParameters[param.code] === option) {
                        optionElement.selected = true;
                    }
                    select.appendChild(optionElement);
                });

                div.appendChild(select);
            } else {
                const input = document.createElement('input');
                input.type = param.type;
                input.className = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm';
                input.name = `parameters[${param.code}]`;
                input.id = `parameters.${param.code}`;
                input.required = param.is_required;
                if (param.type === 'number') {
                    input.step = 'any';
                }
                if (window.productParameters && window.productParameters[param.code]) {
                    input.value = window.productParameters[param.code];
                }
                div.appendChild(input);
            }

            const helpText = document.createElement('p');
            helpText.className = 'mt-2 text-xs text-gray-500';
            helpText.textContent = param.is_required ? 'This parameter is required' : 'Optional parameters';
            div.appendChild(helpText);

            parametersContainer.appendChild(div);
        });
    }

    // 提取折扣预览初始化为独立函数以避免代码重复
    function initializeDiscountPreview() {
        // 折扣价格实时预览
        let sellingPriceInput = document.querySelector('[name="selling_price"]');
        let discountPercentageInput = document.getElementById('discount_percentage');
        const discountedPriceDisplay = document.getElementById('discounted-price');
        
        if (!sellingPriceInput || !discountPercentageInput || !discountedPriceDisplay) {
            console.log('折扣价格预览元素未找到');
            return;
        }
        
        function updateDiscountedPrice() {
            const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
            const discountPercentage = parseFloat(discountPercentageInput.value) || 0;
            const discountedPrice = sellingPrice * (1 - (discountPercentage / 100));
            
            discountedPriceDisplay.textContent = '¥' + discountedPrice.toFixed(2);
        }
        
        // 移除旧的事件监听器以避免重复
        const newDiscountPercentageInput = discountPercentageInput.cloneNode(true);
        discountPercentageInput.parentNode.replaceChild(newDiscountPercentageInput, discountPercentageInput);
        discountPercentageInput = newDiscountPercentageInput;
        
        const newSellingPriceInput = sellingPriceInput.cloneNode(true);
        sellingPriceInput.parentNode.replaceChild(newSellingPriceInput, sellingPriceInput);
        sellingPriceInput = newSellingPriceInput;
        
        // 添加新的事件监听器
        discountPercentageInput.addEventListener('input', updateDiscountedPrice);
        sellingPriceInput.addEventListener('input', updateDiscountedPrice);
        
        // 初始更新一次
        updateDiscountedPrice();
    }

    // 设置Turbolinks和DOM事件监听器
    const setupEventListeners = function() {
        document.removeEventListener('turbolinks:load', setupEventListeners);
        document.removeEventListener('DOMContentLoaded', setupEventListeners);
        
        // 重新添加事件监听器，确保只绑定一次
        document.addEventListener('turbolinks:load', initializeProductEdit);
        document.addEventListener('DOMContentLoaded', initializeProductEdit);
        
        // 初始执行一次
        initializeProductEdit();
    };

    // 注册事件监听器
    document.addEventListener('turbolinks:load', setupEventListeners);
    document.addEventListener('DOMContentLoaded', setupEventListeners);
    
    // Alpine.js重建处理
    document.addEventListener('turbolinks:load', function() {
        if (window.Alpine) {
            window.Alpine.destroyTree(document.body);
            window.Alpine.initTree(document.body);
            console.log('Alpine组件已在Turbolinks加载后重新初始化');
        }
    });
    
    // 特别处理turbolinks:before-cache事件
    document.addEventListener('turbolinks:before-cache', function() {
        console.log('页面即将被Turbolinks缓存');
        // 确保表单元素不被缓存导致问题
        const form = document.querySelector('form[action*="products"]');
        if (form) {
            console.log('标记表单为未缓存状态');
            form.dataset.turbolinksProcessed = false;
        }
        
        // 在页面被缓存前处理Alpine组件
        if (window.Alpine) {
            try {
                // 标记Alpine组件以便在加载时重建
                document.querySelectorAll('[x-data]').forEach(el => {
                    el.setAttribute('data-alpine-destroyed', 'true');
                });
                
                // 可选：销毁Alpine组件，避免缓存时的状态问题
                window.Alpine.destroyTree(document.body);
            } catch (error) {
                console.error('销毁Alpine组件时出错:', error);
            }
        }
    });
})();
</script>
@endpush

@endsection 