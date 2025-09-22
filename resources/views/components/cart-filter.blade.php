@props([
    'customers' => [],
    'statusOptions' => ['active', 'abandoned', 'completed'],
    'dateRanges' => ['today', 'yesterday', 'last_7_days', 'last_30_days', 'this_month', 'last_month', 'custom'],
    'sortOptions' => [
        'latest' => 'Latest First', 
        'oldest' => 'Oldest First',
        'total_high' => 'Total (High to Low)', 
        'total_low' => 'Total (Low to High)',
        'items_high' => 'Item Count (High to Low)',
        'items_low' => 'Item Count (Low to High)'
    ],
    'currentFilters' => []
])

<div class="cart-filter bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h4 class="text-lg font-medium text-gray-900">过滤与搜索</h4>
        <button type="button" id="toggle-filter" class="text-blue-600 hover:text-blue-800 flex items-center text-sm font-medium">
            <span class="toggle-text">显示过滤器</span>
            <svg class="inline-block ml-1 w-5 h-5 transform transition-transform duration-200" id="toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
    </div>
    
    <div id="filter-content" class="p-4 hidden">
        <form action="{{ route('cart.index') }}" method="GET" class="space-y-4">
            {{-- 搜索与快速过滤 --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">搜索</label>
                    <div class="relative rounded-md shadow-sm">
                        <input type="text" name="search" id="search" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md" placeholder="搜索客户名称、邮箱或商品" value="{{ $currentFilters['search'] ?? '' }}">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">状态</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">所有状态</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" {{ isset($currentFilters['status']) && $currentFilters['status'] == $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">排序方式</label>
                    <select id="sort" name="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($sortOptions as $value => $label)
                            <option value="{{ $value }}" {{ isset($currentFilters['sort']) && $currentFilters['sort'] == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            {{-- 高级过滤 --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">客户</label>
                    <select id="customer_id" name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">所有客户</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ isset($currentFilters['customer_id']) && $currentFilters['customer_id'] == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">日期范围</label>
                    <select id="date_range" name="date_range" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">所有时间</option>
                        @foreach($dateRanges as $range)
                            <option value="{{ $range }}" {{ isset($currentFilters['date_range']) && $currentFilters['date_range'] == $range ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $range)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div id="custom_date_container" class="{{ isset($currentFilters['date_range']) && $currentFilters['date_range'] == 'custom' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">开始日期</label>
                            <input type="date" id="date_from" name="date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" value="{{ $currentFilters['date_from'] ?? '' }}">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">结束日期</label>
                            <input type="date" id="date_to" name="date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" value="{{ $currentFilters['date_to'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="min_total" class="block text-sm font-medium text-gray-700 mb-1">最小金额</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="min_total" id="min_total" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md" placeholder="0.00" step="0.01" min="0" value="{{ $currentFilters['min_total'] ?? '' }}">
                    </div>
                </div>
                
                <div>
                    <label for="max_total" class="block text-sm font-medium text-gray-700 mb-1">最大金额</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="max_total" id="max_total" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md" placeholder="0.00" step="0.01" min="0" value="{{ $currentFilters['max_total'] ?? '' }}">
                    </div>
                </div>
                
                <div>
                    <label for="min_items" class="block text-sm font-medium text-gray-700 mb-1">最少商品数</label>
                    <input type="number" name="min_items" id="min_items" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="1" min="1" value="{{ $currentFilters['min_items'] ?? '' }}">
                </div>
            </div>
            
            {{-- 操作按钮 --}}
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('cart.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    重置
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    应用过滤
                </button>
            </div>
        </form>
    </div>
    
    {{-- 当前应用的过滤条件 --}}
    @if(count(array_filter($currentFilters)) > 0)
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-gray-700">已应用过滤器:</span>
                
                @foreach($currentFilters as $key => $value)
                    @if(!empty($value) && $key != 'page')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ ucfirst(str_replace('_', ' ', $key)) }}: 
                            @if($key == 'status' || $key == 'date_range')
                                {{ ucfirst(str_replace('_', ' ', $value)) }}
                            @elseif($key == 'customer_id')
                                {{ $customers->firstWhere('id', $value)->name ?? $value }}
                            @elseif($key == 'sort')
                                {{ $sortOptions[$value] ?? $value }}
                            @else
                                {{ $value }}
                            @endif
                            <a href="{{ route('cart.index', array_merge(request()->except([$key]), ['page' => 1])) }}" class="ml-1 text-blue-600 hover:text-blue-800">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        </span>
                    @endif
                @endforeach
                
                <a href="{{ route('cart.index') }}" class="text-sm text-red-600 hover:text-red-900 ml-auto">
                    清除所有过滤器
                </a>
            </div>
        </div>
    @endif
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 切换过滤器显示
            const toggleBtn = document.getElementById('toggle-filter');
            const filterContent = document.getElementById('filter-content');
            const toggleIcon = document.getElementById('toggle-icon');
            const toggleText = document.querySelector('.toggle-text');
            
            if(toggleBtn && filterContent) {
                toggleBtn.addEventListener('click', function() {
                    filterContent.classList.toggle('hidden');
                    toggleIcon.classList.toggle('rotate-180');
                    
                    if(filterContent.classList.contains('hidden')) {
                        toggleText.textContent = '显示过滤器';
                    } else {
                        toggleText.textContent = '隐藏过滤器';
                    }
                });
            }
            
            // 自定义日期范围控制
            const dateRangeSelect = document.getElementById('date_range');
            const customDateContainer = document.getElementById('custom_date_container');
            
            if(dateRangeSelect && customDateContainer) {
                dateRangeSelect.addEventListener('change', function() {
                    if(this.value === 'custom') {
                        customDateContainer.classList.remove('hidden');
                    } else {
                        customDateContainer.classList.add('hidden');
                    }
                });
            }
            
            // 优化移动端体验的辅助功能
            const filterForm = document.querySelector('.cart-filter form');
            if(filterForm) {
                const formInputs = filterForm.querySelectorAll('input, select');
                
                formInputs.forEach(input => {
                    // 在移动设备上，点击输入框时自动滚动到可见区域
                    input.addEventListener('focus', function() {
                        if(window.innerWidth < 768) {
                            setTimeout(() => {
                                this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }, 300);
                        }
                    });
                });
            }
        });
    </script>
    @endpush
@endonce 