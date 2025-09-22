@extends('layouts.app')

@section('title', 'Product details')

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
                <a href="{{ route('products.index') }}" class="text-gray-700 hover:text-gray-900 ml-1 md:ml-2 text-sm font-medium">Product management</a>
            </div>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-400 ml-1 md:ml-2 text-sm font-medium">Product details</span>
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
                    {{ $product->name }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    SKU: {{ $product->sku }}
                </p>
            </div>
            <div class="mt-4 md:mt-0 md:ml-4 space-x-2">
                <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    edit
                </a>
                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Are you sure you want to delete this product?')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Basic information -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Commodity classification</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $product->category->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">brand</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $product->brand ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Barcode</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $product->barcode ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">state</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $product->is_active ? 'Open up' : 'Disable' }}
                        </span>
                    </dd>
                </div>
            </div>
        </div>

        <!-- Price and inventory -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Price information -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Price information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Cost</dt>
                        <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($product->cost_price, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Sales price</dt>
                        <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($product->selling_price, 2) }}</dd>
                    </div>
                    
                    @if($product->discount_percentage > 0)
                    <div class="col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Discount</dt>
                        <dd class="mt-1 flex items-center">
                            <span class="text-sm font-medium text-red-600">
                                {{ $product->discount_percentage }}% OFF
                            </span>
                            <span class="ml-2 text-sm text-gray-700">
                                RM {{ number_format($product->selling_price * (1 - $product->discount_percentage/100), 2) }}
                            </span>
                            @if($product->discount_start_date || $product->discount_end_date)
                                <span class="ml-2 text-xs text-gray-500">
                                    ({{ $product->discount_start_date ? $product->discount_start_date->format('Y-m-d') : 'Anytime' }} 
                                    to 
                                    {{ $product->discount_end_date ? $product->discount_end_date->format('Y-m-d') : 'Indefinite' }})
                                </span>
                            @endif
                        </dd>
                    </div>
                    @else
                    <div class="col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Discount</dt>
                        <dd class="mt-1 text-sm text-gray-500">No discount applied</dd>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Inventory information -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Inventory information</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Current stock</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->inventory_count ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Minimum memory</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->min_stock }}</dd>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Homepage display settings -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Homepage display</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Featured product</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_featured ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $product->is_featured ? 'Yes' : 'No' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">New arrival</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_new_arrival || $product->isNewArrival() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $product->is_new_arrival || $product->isNewArrival() ? 'Yes' : 'No' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Sale product</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->show_in_sale ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $product->show_in_sale ? 'Yes' : 'No' }}
                        </span>
                    </dd>
                </div>
            </div>
        </div>
        
        <!-- 促销折扣设置 -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Discount Settings</h3>
                <button type="button" id="saveDiscountBtn" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Save Discount
                </button>
            </div>
            
            <form id="discountForm" class="space-y-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="discount_percentage" class="block text-sm font-medium text-gray-700">Discount Percentage (%)</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number" name="discount_percentage" id="discount_percentage" min="0" max="100" step="0.01" class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" value="{{ $product->discount_percentage }}">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">%</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Set to 0 to disable discount</p>
                    </div>
                    
                    <div>
                        <label for="show_in_sale" class="block text-sm font-medium text-gray-700">Display in Sale Section</label>
                        <div class="mt-1">
                            <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="show_in_sale" id="show_in_sale" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" {{ $product->show_in_sale ? 'checked' : '' }}>
                                <label for="show_in_sale" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            </div>
                            <label for="show_in_sale" class="inline-block text-xs text-gray-700">
                                {{ $product->show_in_sale ? 'Shown in sale section' : 'Not shown in sale section' }}
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="discount_start_date" class="block text-sm font-medium text-gray-700">Discount Start Date</label>
                        <div class="mt-1">
                            <input type="date" name="discount_start_date" id="discount_start_date" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ $product->discount_start_date ? $product->discount_start_date->format('Y-m-d') : '' }}">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Leave empty for immediate start</p>
                    </div>
                    
                    <div>
                        <label for="discount_end_date" class="block text-sm font-medium text-gray-700">Discount End Date</label>
                        <div class="mt-1">
                            <input type="date" name="discount_end_date" id="discount_end_date" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ $product->discount_end_date ? $product->discount_end_date->format('Y-m-d') : '' }}">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Leave empty for no end date</p>
                    </div>
                </div>
                
                <div class="bg-gray-100 rounded p-3 mt-4 text-sm">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            If discount is applied, the price will be <span class="font-medium text-red-600" id="discounted_price">
                                RM {{ number_format($product->selling_price * (1 - $product->discount_percentage/100), 2) }}
                            </span>
                        </span>
                    </div>
                </div>
            </form>
        </div>

        <!-- Product picture -->
        @if($product->media && $product->media->count() > 0)
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Product picture</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($product->media as $media)
                <div class="relative group">
                    <div class="aspect-w-1 aspect-h-1 rounded-lg overflow-hidden bg-gray-100 shadow-sm group-hover:shadow-md transition-all duration-200">
                        <img src="{{ Storage::disk('public')->url($media->path) }}" alt="{{ $product->name }}" class="object-cover w-full h-full transform group-hover:scale-105 transition-transform duration-200">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="absolute bottom-0 left-0 right-0 p-3">
                                <p class="text-xs text-white truncate">{{ $media->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Product picture</h3>
            <div class="flex justify-center items-center h-48 bg-gray-100 rounded-lg border border-gray-200">
                <div class="text-center text-gray-400">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="mt-2">No pictures available</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Classification parameter -->
        @if($product->parameters)
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Classification parameter</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($product->category->parameters as $parameter)
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ $parameter->name }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @switch($parameter->type)
                            @case('checkbox')
                                {{ is_array($product->parameters[$parameter->code] ?? null) ? implode(', ', $product->parameters[$parameter->code]) : '-' }}
                                @break
                            @default
                                {{ $product->parameters[$parameter->code] ?? '-' }}
                        @endswitch
                    </dd>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Product specifications -->
        @if($product->specifications)
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Product specifications</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($product->specifications as $key => $value)
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ $key }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $value }}</dd>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Product description -->
        @if($product->description)
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Product description</h3>
            <div class="prose max-w-none">
                {{ $product->description }}
            </div>
        </div>
        @endif

        <!-- 查找可能引用了homepage.index的链接 -->
        @if(isset($product->is_featured) && $product->is_featured)
            <a href="{{ route('admin.homepage.index') }}#featured-products" class="text-blue-600 hover:text-blue-800">
                在首页特色产品中查看
            </a>
        @endif

        @if(isset($product->show_in_new_arrivals) && $product->show_in_new_arrivals)
            <a href="{{ route('admin.homepage.index') }}#new-products" class="text-blue-600 hover:text-blue-800">
                在首页新品中查看
            </a>
        @endif

        @if(isset($product->show_in_sale) && $product->show_in_sale)
            <a href="{{ route('admin.homepage.index') }}#sale-products" class="text-blue-600 hover:text-blue-800">
                在首页促销产品中查看
            </a>
        @endif
    </div>
</div>
@endsection 

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toast通知配置
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    
    // 保存折扣设置
    const saveDiscountBtn = document.getElementById('saveDiscountBtn');
    if (saveDiscountBtn) {
        saveDiscountBtn.addEventListener('click', function() {
            // 显示加载提示
            Swal.fire({
                title: '保存中...',
                text: '正在更新折扣设置',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
                allowEscapeKey: false
            });
            
            // 获取表单数据
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('product_id', {{ $product->id }});
            formData.append('discount_percentage', document.getElementById('discount_percentage').value || 0);
            formData.append('show_in_sale', document.getElementById('show_in_sale').checked ? 1 : 0);
            
            const startDate = document.getElementById('discount_start_date').value;
            const endDate = document.getElementById('discount_end_date').value;
            
            if (startDate) formData.append('discount_start_date', startDate);
            if (endDate) formData.append('discount_end_date', endDate);
            
            // 发送请求
            fetch('/admin/products/update-discount', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('网络响应错误');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Toast.fire({
                        icon: 'success',
                        title: '折扣设置已保存'
                    });
                } else {
                    throw new Error(data.message || '保存失败');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Toast.fire({
                    icon: 'error',
                    title: '保存失败',
                    text: error.message
                });
            });
        });
    }
    
    // 实时计算折扣价格
    const discountPercentageInput = document.getElementById('discount_percentage');
    const discountedPriceElement = document.getElementById('discounted_price');
    
    if (discountPercentageInput && discountedPriceElement) {
        discountPercentageInput.addEventListener('input', function() {
            const percentage = parseFloat(this.value) || 0;
            const originalPrice = {{ $product->selling_price }};
            const discountedPrice = originalPrice * (1 - percentage / 100);
            
            discountedPriceElement.textContent = 'RM ' + discountedPrice.toFixed(2);
        });
    }
});
</script>
<style>
    /* 开关样式 */
    .toggle-checkbox:checked {
        right: 0;
        border-color: #68D391;
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #68D391;
    }
    .toggle-checkbox {
        right: 0;
        z-index: 1;
        border-color: #D1D5DB;
        transition: all 0.3s ease-in-out;
    }
    .toggle-label {
        transition: all 0.3s ease-in-out;
    }
</style>
@endpush 