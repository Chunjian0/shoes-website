@extends('layouts.app')

@section('title', 'Supplier Management')

@section('styles')
<style>
/* 基础动画效果 */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-30px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes slideInRight {
    from { opacity: 0; transform: translateX(30px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes shimmer {
    0% { background-position: -100% 0; }
    100% { background-position: 200% 0; }
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

@keyframes borderPulse {
    0% { border-color: rgba(79, 70, 229, 0.4); }
    50% { border-color: rgba(79, 70, 229, 0.8); }
    100% { border-color: rgba(79, 70, 229, 0.4); }
}

/* 应用动画的类 */
.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}

.animate-slide-in-left {
    animation: slideInLeft 0.5s ease-out forwards;
}

.animate-slide-in-right {
    animation: slideInRight 0.5s ease-out forwards;
}

.animate-pulse {
    animation: pulse 2s infinite;
}

.animate-shimmer {
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-border {
    animation: borderPulse 2s infinite;
}

/* 动画延迟 */
.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
.delay-400 { animation-delay: 0.4s; }
.delay-500 { animation-delay: 0.5s; }

/* 背景网格 */
.bg-mesh {
    background-color: #f9fafb;
    background-image: radial-gradient(#4f46e5 0.5px, transparent 0.5px), radial-gradient(#4f46e5 0.5px, #f9fafb 0.5px);
    background-size: 20px 20px;
    background-position: 0 0, 10px 10px;
    background-attachment: fixed;
}

/* 卡片悬浮效果 */
.card-hover {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* 表格行悬浮效果 */
.row-hover {
    transition: all 0.2s ease;
}

.row-hover:hover {
    background-color: rgba(249, 250, 251, 0.8);
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* 自定义滚动条 */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* 交互元素 */
.btn-interactive {
    position: relative;
    overflow: hidden;
    transition: all 0.3s;
}

.btn-interactive::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(-100%);
    transition: transform 0.3s ease;
}

.btn-interactive:hover::after {
    transform: translateX(0);
}

/* 响应式调整 */
@media (max-width: 768px) {
    .responsive-container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .hide-on-mobile {
        display: none;
    }
}

/* 按钮动画 */
.btn-with-icon {
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
}

.btn-with-icon svg, 
.btn-with-icon img {
    transition: transform 0.3s ease;
}

.btn-with-icon:hover svg,
.btn-with-icon:hover img {
    transform: translateX(3px);
}

/* 加载状态 */
.loading-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
    height: 16px;
    margin-bottom: 8px;
}

/* 表格样式增强 */
.enhanced-table {
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.enhanced-table thead th {
    position: relative;
    cursor: pointer;
    user-select: none;
}

.enhanced-table thead th::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: #4f46e5;
    transition: width 0.3s ease;
}

.enhanced-table thead th:hover::after {
    width: 100%;
}

/* 状态徽章增强 */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
}

.status-badge::before {
    content: '';
    display: inline-block;
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    margin-right: 0.5rem;
}

.status-badge-active {
    background-color: rgba(16, 185, 129, 0.1);
    color: rgb(16, 185, 129);
}

.status-badge-active::before {
    background-color: rgb(16, 185, 129);
}

.status-badge-inactive {
    background-color: rgba(239, 68, 68, 0.1);
    color: rgb(239, 68, 68);
}

.status-badge-inactive::before {
    background-color: rgb(239, 68, 68);
}

/* 搜索框增强 */
.enhanced-search {
    transition: all 0.3s ease;
    border: 1px solid transparent;
    background-color: #f9fafb;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.enhanced-search:focus {
    background-color: white;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    transform: translateY(-1px);
}

/* 下拉菜单增强 */
.enhanced-select {
    transition: all 0.3s ease;
    border: 1px solid transparent;
    background-color: #f9fafb;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
    appearance: none;
}

.enhanced-select:focus {
    background-color: white;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    transform: translateY(-1px);
}

/* 卡片效果 */
.card {
    border-radius: 0.75rem;
    overflow: hidden;
    background: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
@endsection

@section('breadcrumb')
<div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white animate-fade-in">
    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center space-x-2 mb-2 sm:mb-0">
            <h1 class="text-xl font-bold">Supplier Management</h1>
        </div>
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-200 transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h2a1 1 0 001-1v-6.5l-3.5-3.5"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-indigo-200">Suppliers</span>
                </li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="bg-white py-6 min-h-screen">
    <div class="max-w-full mx-auto px-6 responsive-container">
        <!-- 标题和创建按钮 -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 animate-fade-in">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold text-gray-900">
                    Supplier management
                </h2>
            </div>
            <div class="mt-4 sm:mt-0 flex justify-end">
                @can('create suppliers')
                <a href="{{ route('suppliers.create') }}" class="btn-interactive btn-with-icon px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-base flex items-center transition-all duration-300 shadow-md hover:shadow-lg">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create Supplier
                </a>
                @endcan
            </div>
        </div>

        <!-- 搜索和过滤器 -->
        <div class="card mb-6 animate-fade-in delay-100 shadow border border-gray-200">
            <div class="p-4">
                <div class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
                    <div class="flex-1 space-y-1">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" id="client-search" class="enhanced-search pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 text-base" 
                                   placeholder="Search by code, name, contact or phone">
                        </div>
                    </div>
                    
                    <div class="w-full sm:w-48 space-y-1">
                        <label for="status-filter" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status-filter" class="enhanced-select block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 text-base">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="button" id="reset-filters" class="btn-interactive px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg text-base inline-flex items-center transition-all duration-300">
                            <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 供应商列表 -->
        <div class="card animate-fade-in delay-200 shadow border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-4 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                Code
                            </th>
                            <th scope="col" class="px-4 py-4 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-4 py-4 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                Contact
                            </th>
                            <th scope="col" class="px-4 py-4 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                Phone
                            </th>
                            <th scope="col" class="px-4 py-4 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                Credit
                            </th>
                            <th scope="col" class="px-4 py-4 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                Term
                            </th>
                            <th scope="col" class="px-4 py-4 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-4 text-right text-sm font-medium text-gray-700 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($suppliers as $supplier)
                            <tr class="row-hover">
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-medium text-indigo-600 truncate max-w-[100px]">{{ $supplier->code }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-medium text-gray-900 truncate max-w-[150px]">{{ $supplier->name }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="text-gray-600 truncate max-w-[120px]">{{ $supplier->contact_person }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="text-gray-600 truncate max-w-[120px]">{{ $supplier->phone }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="text-gray-600 truncate max-w-[100px]">RM {{ number_format($supplier->credit_limit, 2) }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="text-gray-600 truncate max-w-[70px]">{{ $supplier->payment_term }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <span class="status-badge {{ $supplier->is_active ? 'status-badge-active' : 'status-badge-inactive' }}">
                                        {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right text-sm">
                                    <div class="flex justify-end space-x-3">
                                        @can('view suppliers')
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" title="View Details">
                                            <span class="sr-only">View</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        @endcan
                                        
                                        @can('edit suppliers')
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="text-yellow-600 hover:text-yellow-900 transition-colors duration-200" title="Edit">
                                            <span class="sr-only">Edit</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        @endcan
                                        
                                        @can('delete suppliers')
                                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline-block delete-supplier-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="text-red-600 hover:text-red-900 transition-colors duration-200 delete-supplier-btn" data-supplier-id="{{ $supplier->id }}" data-supplier-name="{{ $supplier->name }}" title="Delete">
                                                <span class="sr-only">Delete</span>
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-6 text-sm text-gray-500 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-gray-500 text-base font-medium">No suppliers found</p>
                                        <a href="{{ route('suppliers.create') }}" class="mt-3 inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Create New Supplier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 

@push('scripts')
<script>
    // 确保脚本只运行一次（防止Turbolinks重复初始化）
    (function() {
        if (window.supplierIndexInitialized) return;
        window.supplierIndexInitialized = true;
        
        // 初始化函数 - 同时支持DOM加载和Turbolinks加载
        function initPage() {
            console.log('Initializing supplier index page...');
            initDeleteButtons();
            initAnimations();
            initSortableColumns();
            addTableHighlighting();
            initClientSideSearch();
        }
        
        // 初始化前端搜索功能
        function initClientSideSearch() {
            const searchInput = document.getElementById('client-search');
            const statusFilter = document.getElementById('status-filter');
            const resetButton = document.getElementById('reset-filters');
            const tableRows = document.querySelectorAll('tbody tr');
            
            // 搜索和过滤函数
            function filterRows() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value;
                
                tableRows.forEach(row => {
                    if (row.querySelector('td[colspan]')) {
                        // 这是一个空行（没有供应商时显示的行），始终显示
                        return;
                    }
                    
                    const code = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                    const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const contact = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const phone = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                    const statusBadge = row.querySelector('.status-badge');
                    const isActive = statusBadge && statusBadge.textContent.trim().toLowerCase() === 'active';
                    
                    // 搜索词匹配
                    const matchesSearch = searchTerm === '' || 
                        code.includes(searchTerm) || 
                        name.includes(searchTerm) || 
                        contact.includes(searchTerm) || 
                        phone.includes(searchTerm);
                    
                    // 状态过滤
                    const matchesStatus = statusValue === 'all' || 
                        (statusValue === 'active' && isActive) || 
                        (statusValue === 'inactive' && !isActive);
                    
                    // 同时满足搜索和状态过滤条件才显示
                    if (matchesSearch && matchesStatus) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
                
                // 检查是否有可见行
                let visibleRows = 0;
                tableRows.forEach(row => {
                    if (!row.classList.contains('hidden') && !row.querySelector('td[colspan]')) {
                        visibleRows++;
                    }
                });
                
                // 如果没有可见行，显示"无结果"消息
                const noResultsRow = document.getElementById('no-results-row');
                
                if (visibleRows === 0 && !noResultsRow) {
                    const tbody = document.querySelector('tbody');
                    const newRow = document.createElement('tr');
                    newRow.id = 'no-results-row';
                    newRow.innerHTML = `
                        <td colspan="8" class="px-3 py-6 text-sm text-gray-500 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-10 w-10 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-gray-500 text-base font-medium">No matching suppliers found</p>
                                <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(newRow);
                } else if (visibleRows > 0 && noResultsRow) {
                    noResultsRow.remove();
                }
            }
            
            // 为搜索输入框添加事件监听
            if (searchInput) {
                searchInput.addEventListener('input', filterRows);
            }
            
            // 为状态过滤器添加事件监听
            if (statusFilter) {
                statusFilter.addEventListener('change', filterRows);
            }
            
            // 为重置按钮添加事件监听
            if (resetButton) {
                resetButton.addEventListener('click', function() {
                    if (searchInput) {
                        searchInput.value = '';
                    }
                    if (statusFilter) {
                        statusFilter.value = 'all';
                    }
                    filterRows();
                    
                    // 添加重置按钮动画
                    resetButton.classList.add('animate-pulse');
                    setTimeout(() => {
                        resetButton.classList.remove('animate-pulse');
                    }, 1000);
                });
            }
        }
        
        // 初始化删除按钮
        function initDeleteButtons() {
            const deleteButtons = document.querySelectorAll('.delete-supplier-btn');
            
            deleteButtons.forEach(button => {
                // 防止多次绑定事件
                if (button.getAttribute('data-initialized') === 'true') return;
                
                button.setAttribute('data-initialized', 'true');
                button.addEventListener('click', function() {
                    const supplierId = this.getAttribute('data-supplier-id');
                    const supplierName = this.getAttribute('data-supplier-name');
                    const form = this.closest('form');
                    
                    // 使用SweetAlert2进行确认
                    Swal.fire({
                        title: 'Delete Supplier?',
                        html: `Are you sure you want to delete <strong>${supplierName}</strong>?<br><small class="text-gray-500">This action cannot be undone.</small>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel',
                        buttonsStyling: true,
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown animate__faster'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp animate__faster'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // 显示加载中
                            Swal.fire({
                                title: 'Deleting...',
                                html: 'Please wait while we process your request.',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                willOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // 提交表单
                            form.submit();
                        }
                    });
                });
            });
        }
        
        // 初始化动画效果
        function initAnimations() {
            // 为表格行添加鼠标悬停特效
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.classList.add('bg-gray-50');
                    this.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
                    this.style.transition = 'all 0.2s ease';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.classList.remove('bg-gray-50');
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });
            
            // 徽章动画
            const statusBadges = document.querySelectorAll('.status-badge');
            statusBadges.forEach(badge => {
                badge.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                });
                
                badge.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
            });
        }
        
        // 添加表格列排序功能
        function initSortableColumns() {
            const tableHeaders = document.querySelectorAll('thead th');
            
            tableHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    // 这里可以添加实际的排序逻辑
                    // 目前只添加视觉效果
                    this.classList.toggle('text-indigo-600');
                    
                    // 高亮动画
                    const afterElement = document.createElement('span');
                    afterElement.className = 'block absolute bottom-0 left-0 w-full h-0.5 bg-indigo-500 animate-shimmer';
                    afterElement.style.animation = 'shimmer 1s';
                    
                    this.appendChild(afterElement);
                    
                    setTimeout(() => {
                        afterElement.remove();
                    }, 1000);
                });
            });
        }
        
        // 添加表格行鼠标悬停高亮效果
        function addTableHighlighting() {
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                
                row.addEventListener('mouseenter', function() {
                    cells.forEach(cell => {
                        cell.style.backgroundColor = 'rgba(249, 250, 251, 0.7)';
                    });
                });
                
                row.addEventListener('mouseleave', function() {
                    cells.forEach(cell => {
                        cell.style.backgroundColor = '';
                    });
                });
            });
        }
        
        // 支持多种页面加载方式
        document.addEventListener('DOMContentLoaded', initPage);
        document.addEventListener('turbolinks:load', initPage);
        document.addEventListener('livewire:load', initPage);
    })();
</script>
@endpush 