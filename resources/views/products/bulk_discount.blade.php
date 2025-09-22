@extends('layouts.app')

@section('title', 'Bulk Discount Settings')

@section('content')
<div class="container mx-auto px-4 py-5">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Bulk Discount Settings</h1>
        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Products
        </a>
    </div>
    
    <!-- Alert Messages -->
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif
    
    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Products</h2>
        <form action="{{ route('products.discounts.bulk') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="category_id" name="category_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name, SKU or barcode" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="discount_status" class="block text-sm font-medium text-gray-700 mb-1">Discount Status</label>
                    <select id="discount_status" name="discount_status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">All Products</option>
                        <option value="with_discount" {{ request('discount_status') == 'with_discount' ? 'selected' : '' }}>With Discount</option>
                        <option value="without_discount" {{ request('discount_status') == 'without_discount' ? 'selected' : '' }}>Without Discount</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                
                <a href="{{ route('products.discounts.bulk') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Clear Filters
                </a>
            </div>
        </form>
    </div>
    
    <!-- Bulk Discount Form -->
    <form action="{{ route('products.discounts.bulk.update') }}" method="POST">
        @csrf
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <!-- Products Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <input id="select-all" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="select-all" class="ml-2 block text-sm text-gray-900">Select All</label>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Regular Price
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Current Discount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Discounted Price
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Discount Period
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input id="product-{{ $product->id }}" name="product_ids[]" value="{{ $product->id }}" type="checkbox" class="product-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($product->images)
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . json_decode($product->images)[0] ?? 'placeholder.jpg') }}" alt="{{ $product->name }}">
                                            </div>
                                        @endif
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $product->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                SKU: {{ $product->sku }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $product->category->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ number_format($product->selling_price, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->discount_percentage > 0)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ $product->discount_percentage }}%
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            No Discount
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->discount_percentage > 0)
                                        <div class="text-sm text-red-600 font-medium">
                                            {{ number_format($product->selling_price * (1 - $product->discount_percentage/100), 2) }}
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-500">-</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($product->discount_start_date || $product->discount_end_date)
                                        {{ $product->discount_start_date ? $product->discount_start_date->format('Y-m-d') : 'Anytime' }}
                                        to
                                        {{ $product->discount_end_date ? $product->discount_end_date->format('Y-m-d') : 'Indefinite' }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No products found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $products->links() }}
            </div>
        </div>
        
        <!-- Bulk Discount Settings -->
        <div class="bg-white shadow-md rounded-lg p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Bulk Discount Settings</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="discount_percentage" class="block text-sm font-medium text-gray-700 mb-1">Discount Percentage (%)</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="number" name="discount_percentage" id="discount_percentage" min="0" max="100" step="0.01" class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" value="{{ old('discount_percentage', 0) }}">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">%</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Set to 0 to disable discount</p>
                </div>
                
                <div class="flex items-start mt-6">
                    <div class="flex items-center h-5">
                        <input id="show_in_sale" name="show_in_sale" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" {{ old('show_in_sale') ? 'checked' : '' }}>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="show_in_sale" class="font-medium text-gray-700">Show in Sale Section</label>
                        <p class="text-gray-500">Display these products in the sale section on your store</p>
                    </div>
                </div>
                
                <div>
                    <label for="discount_start_date" class="block text-sm font-medium text-gray-700 mb-1">Discount Start Date</label>
                    <input type="date" name="discount_start_date" id="discount_start_date" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('discount_start_date') }}">
                    <p class="mt-1 text-xs text-gray-500">Leave empty for immediate start</p>
                </div>
                
                <div>
                    <label for="discount_end_date" class="block text-sm font-medium text-gray-700 mb-1">Discount End Date</label>
                    <input type="date" name="discount_end_date" id="discount_end_date" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('discount_end_date') }}">
                    <p class="mt-1 text-xs text-gray-500">Leave empty for no end date</p>
                </div>
                
                <div>
                    <label for="min_quantity_for_discount" class="block text-sm font-medium text-gray-700 mb-1">Minimum Quantity for Discount</label>
                    <input type="number" name="min_quantity_for_discount" id="min_quantity_for_discount" min="0" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('min_quantity_for_discount', 0) }}">
                    <p class="mt-1 text-xs text-gray-500">Minimum quantity required to apply the discount</p>
                </div>
                
                <div>
                    <label for="max_quantity_for_discount" class="block text-sm font-medium text-gray-700 mb-1">Maximum Quantity for Discount</label>
                    <input type="number" name="max_quantity_for_discount" id="max_quantity_for_discount" min="0" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('max_quantity_for_discount') }}">
                    <p class="mt-1 text-xs text-gray-500">Maximum quantity eligible for discount (leave empty for no limit)</p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="submit" id="apply-discount-btn" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" disabled>
                    Apply Discount to Selected Products
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const productCheckboxes = document.querySelectorAll('.product-checkbox');
        const applyDiscountBtn = document.getElementById('apply-discount-btn');
        
        // 全选/取消全选
        selectAllCheckbox.addEventListener('change', function() {
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateApplyButtonState();
        });
        
        // 监听单个产品复选框变化
        productCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // 如果取消选中单个复选框，则取消"全选"
                if (!checkbox.checked) {
                    selectAllCheckbox.checked = false;
                }
                
                // 检查是否所有产品都被选中
                const allChecked = Array.from(productCheckboxes).every(cb => cb.checked);
                if (allChecked) {
                    selectAllCheckbox.checked = true;
                }
                
                updateApplyButtonState();
            });
        });
        
        // 更新应用按钮状态
        function updateApplyButtonState() {
            const anyChecked = Array.from(productCheckboxes).some(cb => cb.checked);
            applyDiscountBtn.disabled = !anyChecked;
        }
        
        // 初始化按钮状态
        updateApplyButtonState();
        
        // 确保折扣期间的日期有效
        const startDateInput = document.getElementById('discount_start_date');
        const endDateInput = document.getElementById('discount_end_date');
        
        endDateInput.addEventListener('change', function() {
            if (startDateInput.value && endDateInput.value) {
                if (new Date(endDateInput.value) < new Date(startDateInput.value)) {
                    alert('Discount end date cannot be earlier than start date');
                    endDateInput.value = '';
                }
            }
        });
        
        startDateInput.addEventListener('change', function() {
            if (startDateInput.value && endDateInput.value) {
                if (new Date(endDateInput.value) < new Date(startDateInput.value)) {
                    alert('Discount end date cannot be earlier than start date');
                    endDateInput.value = '';
                }
            }
        });
    });
</script>
@endpush 