@extends('layouts.app')

@section('title', 'Commodity classification')

@section('head')
<!-- Animate.css for SweetAlert2 animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endsection

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
                <span class="text-gray-400 ml-1 md:ml-2 text-sm font-medium">Commodity Classification</span>
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
                    Product Classification List
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage All Product Classification Information
                </p>
            </div>
            <div class="mt-4 md:mt-0 md:ml-4">
                <a href="{{ route('product-categories.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add classification
                </a>
            </div>
        </div>

        <!-- Search and screen -->
        <div class="mb-6">
            <form id="search-form" class="grid grid-cols-1 md:grid-cols-4 gap-4" onsubmit="return false;">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md search-field"
                           placeholder="Classification name/Code">
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">type</label>
                    <select name="type" id="type" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm select-field">
                        <option value="">All types</option>
                        <option value="frame" {{ request('type') === 'frame' ? 'selected' : '' }}>Mirror frame</option>
                        <option value="lens" {{ request('type') === 'lens' ? 'selected' : '' }}>lens</option>
                        <option value="sunglasses" {{ request('type') === 'sunglasses' ? 'selected' : '' }}>sunglasses</option>
                        <option value="accessory" {{ request('type') === 'accessory' ? 'selected' : '' }}>Accessories</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">state</label>
                    <select name="status" id="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm select-field">
                        <option value="">All states</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Open up</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Disable</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" id="search-btn" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 search-btn">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                        Search
                    </button>
                    <button type="button" class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="reset-filters">
                        <svg class="-ml-1 mr-1 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Reset
                    </button>
                    
                </div>
            </form>

            <!-- Search Indicator & Results Counter -->
            <div class="mt-3 flex justify-between items-center">
                <div class="hidden" id="search-indicator">
                    <div class="flex items-center text-sm text-indigo-600">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Filtering...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classification list -->
        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Classification information
                                    </th>
                                    
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        state
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        operate
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($categories as $category)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                            <div class="text-sm text-gray-500">Code:{{ $category->code }}</div>
                                            @if($category->description)
                                                <div class="text-sm text-gray-500">{{ $category->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $category->products_count ?? 0 }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge px-3 py-1.5 inline-flex items-center rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                                                <span class="relative flex h-2 w-2 mr-1.5">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $category->is_active ? 'bg-green-400' : 'bg-red-400' }} opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $category->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                                </span>
                                                {{ $category->is_active ? 'Open up' : 'Disable' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <a href="{{ route('product-categories.show', $category) }}" class="action-btn view-btn inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 hover:border-indigo-300 hover:text-indigo-600 focus:outline-none transition-colors duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Check
                                                </a>
                                                <a href="{{ route('product-categories.edit', $category) }}" class="action-btn edit-btn inline-flex items-center px-2.5 py-1.5 border border-yellow-300 shadow-sm text-xs font-medium rounded text-yellow-700 bg-white hover:bg-yellow-50 hover:text-yellow-800 focus:outline-none transition-colors duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    edit
                                                </a>
                                                <form action="{{ route('product-categories.destroy', $category) }}" method="POST" class="inline-block delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="delete-btn action-btn inline-flex items-center px-2.5 py-1.5 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50 hover:text-red-800 focus:outline-none transition-colors duration-200">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr data-empty="true">
                                        <td colspan="5" class="px-6 py-12 whitespace-nowrap text-center">
                                            <div class="empty-state flex flex-col items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                </svg>
                                                <p class="text-gray-500 text-lg mb-1">No classification data</p>
                                                <p class="text-gray-400 text-sm mb-4">Create a new classification to get started</p>
                                                <a href="{{ route('product-categories.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                                    </svg>
                                                    Add classification
                                                </a>
                                            </div>
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
        <div class="mt-6 pagination-container">
            {{ $categories->withQueryString()->links() }}
        </div>
    </div>
</div>

<style>
    /* 状态标签样式 */
    .status-badge {
        transition: all 0.3s ease;
    }
    
    .status-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    /* 操作按钮效果 */
    .action-btn {
        transform: translateZ(0);
        transition: all 0.2s ease;
    }
    
    .action-btn:hover {
        transform: translateY(-1px);
    }
    
    .action-btn:active {
        transform: translateY(1px);
    }
    
    /* 表格行样式 */
    .table-row {
        transform: translateZ(0);
        animation: tableRowFadeIn 0.5s ease-out forwards;
        opacity: 0;
    }
    
    @keyframes tableRowFadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .table-row:nth-child(1) { animation-delay: 0.1s; }
    .table-row:nth-child(2) { animation-delay: 0.15s; }
    .table-row:nth-child(3) { animation-delay: 0.2s; }
    .table-row:nth-child(4) { animation-delay: 0.25s; }
    .table-row:nth-child(5) { animation-delay: 0.3s; }
    .table-row:nth-child(6) { animation-delay: 0.35s; }
    .table-row:nth-child(7) { animation-delay: 0.4s; }
    .table-row:nth-child(8) { animation-delay: 0.45s; }
    .table-row:nth-child(9) { animation-delay: 0.5s; }
    .table-row:nth-child(10) { animation-delay: 0.55s; }
    
    /* 分页控件美化 */
    .pagination-container nav {
        display: flex;
        justify-content: center;
    }
    
    .pagination-container .flex.justify-between.flex-1 {
        display: none; /* 隐藏不必要的元素 */
    }
    
    .pagination-container nav > div:last-child {
        width: 100%;
    }
    
    .pagination-container span[aria-current="page"] > span {
        background-color: #4f46e5 !important;
        border-color: #4f46e5 !important;
        color: white !important;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1), 0 2px 4px -1px rgba(79, 70, 229, 0.06);
        transform: scale(1.05);
        z-index: 10;
        position: relative;
    }
    
    .pagination-container a, 
    .pagination-container span {
        transition: all 0.3s ease;
        margin: 0 2px;
    }
    
    .pagination-container a:hover {
        background-color: #f3f4f6 !important;
        border-color: #6366f1 !important;
        color: #4f46e5 !important;
        transform: translateY(-1px);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    /* 移动设备适配 */
    @media (max-width: 640px) {
        /* 表格调整 */
        .table-fixed {
            table-layout: fixed;
        }
        
        th, td {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        
        thead th:nth-child(3),
        tbody td:nth-child(3) {
            width: 20% !important;
        }
        
        thead th:nth-child(4),
        tbody td:nth-child(4) {
            width: 30% !important;
        }
        
        /* 状态标签缩小 */
        .status-badge {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
        
        /* 操作按钮缩小和垂直排列 */
        .flex.justify-end.space-x-2 {
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }
        
        .action-btn {
            width: auto;
            white-space: nowrap;
        }
        
        /* 搜索表单调整 */
        .bg-gray-50.rounded-lg.p-4 {
            padding: 0.75rem !important;
        }
        
        .grid.grid-cols-1.md\:grid-cols-4.gap-4 {
            gap: 0.75rem !important;
        }
        
        /* 分页样式调整 */
        .pagination-container a,
        .pagination-container span {
            padding: 0.4rem 0.5rem !important;
            font-size: 0.75rem !important;
            margin: 0 1px !important;
        }
        
        /* 标题和添加按钮调整 */
        .flex.flex-col.md\:flex-row h2 {
            font-size: 1.5rem !important;
            line-height: 1.3 !important;
            margin-bottom: 0.5rem;
        }
        
        .flex.flex-col.md\:flex-row .mt-4.md\:mt-0.md\:ml-4 a {
            width: 100%;
            justify-content: center;
        }
    }
    
    /* 空状态动画 */
    .empty-state svg {
        animation: pulse 3s infinite ease-in-out;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
    }
</style>

<script>
    // 使用IIFE防止全局变量污染和重复初始化
    (function() {
        // 保存初始化状态，防止重复初始化
        let initialized = false;
        let allCategories = [];
        let tableRows = [];
        
        // 主初始化函数
        function initializeElements() {
            // 避免重复初始化
            if (initialized) {
                return;
            }
            initialized = true;
            
            console.log('初始化商品分类页面...');
            
            // 收集所有表格行数据用于前端筛选
            collectTableData();
            
            // 设置前端筛选事件
            setupFrontendFiltering();
            
            // 搜索表单字段的动画效果
            const formFields = document.querySelectorAll('.search-field, .select-field');
            
            formFields.forEach((field, index) => {
                field.style.opacity = 0;
                field.style.transform = 'translateY(10px)';
                
                setTimeout(() => {
                    field.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    field.style.opacity = 1;
                    field.style.transform = 'translateY(0)';
                }, 100 + (index * 100));
            });
            
            // 搜索按钮动画
            const searchBtn = document.querySelector('.search-btn');
            if (searchBtn) {
                searchBtn.style.opacity = 0;
                searchBtn.style.transform = 'translateY(10px)';
                
                setTimeout(() => {
                    searchBtn.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    searchBtn.style.opacity = 1;
                    searchBtn.style.transform = 'translateY(0)';
                }, 500);
            }
            
            // 删除确认对话框
            const deleteBtns = document.querySelectorAll('.delete-btn');
            
            deleteBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // 使用SweetAlert2替代原生confirm
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Confirm deletion',
                            text: 'Are you sure you want to delete this classification?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Delete',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#6b7280',
                            background: 'rgba(255, 255, 255, 0.95)',
                            backdrop: 'rgba(0, 0, 0, 0.4)',
                            customClass: {
                                confirmButton: 'swal2-confirm confirm-btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500',
                                cancelButton: 'cancel-btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500',
                                popup: 'rounded-lg shadow-xl border border-gray-200'
                            },
                            reverseButtons: true,
                            buttonsStyling: true,
                            showClass: {
                                popup: 'animate__animated animate__fadeIn animate__faster'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOut animate__faster'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // 用户确认删除，提交表单
                                this.closest('form').submit();
                            }
                        });
                    } else {
                        // 降级处理：如果SweetAlert2不可用，回退到原生confirm
                        if (confirm('Are you sure you want to delete this classification?')) {
                            this.closest('form').submit();
                        }
                    }
                });
            });
            
            // 添加分页项动画效果
            const paginationLinks = document.querySelectorAll('.pagination-container a, .pagination-container span[aria-current="page"] > span');
            
            paginationLinks.forEach((link, index) => {
                link.style.opacity = 0;
                link.style.transform = 'translateY(10px)';
                
                setTimeout(() => {
                    link.style.transition = 'all 0.3s ease';
                    link.style.opacity = 1;
                    link.style.transform = 'translateY(0)';
                }, 100 + (index * 50));
            });
            
            // 移动设备优化
            function adjustForMobile() {
                const isMobile = window.innerWidth <= 640;
                
                if (isMobile) {
                    // 简化表格头部
                    const tableHeaders = document.querySelectorAll('th');
                    tableHeaders.forEach(th => {
                        const text = th.textContent.trim();
                        if (text === 'Classification information') {
                            th.textContent = 'Category';
                        } else if (text === 'Quantity') {
                            th.textContent = 'Qty';
                        } else if (text === 'state') {
                            th.textContent = 'Status';
                        } else if (text === 'operate') {
                            th.textContent = 'Action';
                        }
                    });
                    
                    // 调整分页的响应性
                    const paginationContainer = document.querySelector('.pagination-container');
                    if (paginationContainer) {
                        paginationContainer.classList.add('overflow-x-auto');
                        paginationContainer.style.WebkitOverflowScrolling = 'touch';
                        paginationContainer.style.scrollbarWidth = 'none';
                        paginationContainer.style.msOverflowStyle = 'none';
                    }
                }
            }
            
            // 初始调整
            adjustForMobile();
            
            // 窗口大小改变时重新调整
            window.addEventListener('resize', adjustForMobile);
        }

        // 收集表格数据用于前端筛选
        function collectTableData() {
            const tbody = document.querySelector('table tbody');
            if (!tbody) return;
            
            // 清空之前的数据
            allCategories = [];
            
            const rows = tbody.querySelectorAll('tr:not([data-empty])');
            tableRows = Array.from(rows);
            
            tableRows.forEach(row => {
                // 保存原始行的引用
                const nameElement = row.querySelector('.text-sm.font-medium.text-gray-900');
                const codeElement = row.querySelectorAll('.text-sm.text-gray-500')[0];
                const descElement = row.querySelectorAll('.text-sm.text-gray-500')[1];
                const qtyElement = row.querySelector('td:nth-child(2) .text-sm.text-gray-900');
                const statusElement = row.querySelector('.status-badge');
                
                // 提取类型信息 - 通过获取对应的hidden属性或者名称推断
                let type = 'unknown';
                // 根据名称中包含的关键词推断类型
                const name = nameElement ? nameElement.textContent.trim().toLowerCase() : '';
                if (name.includes('frame') || name.includes('mirror')) {
                    type = 'frame';
                } else if (name.includes('lens')) {
                    type = 'lens';
                } else if (name.includes('sunglasses') || name.includes('sun glasses')) {
                    type = 'sunglasses';
                } else if (name.includes('accessory') || name.includes('accessories')) {
                    type = 'accessory';
                }
                
                // 读取状态
                let status = '';
                if (statusElement) {
                    status = statusElement.textContent.trim();
                    // 确保状态值可以正确匹配
                    if (status.includes('Open up')) {
                        status = 'Open up';
                    } else if (status.includes('Disable')) {
                        status = 'Disable';
                    }
                }
                
                const category = {
                    element: row,
                    name: nameElement ? nameElement.textContent.trim() : '',
                    code: codeElement ? codeElement.textContent.replace('Code:', '').trim() : '',
                    description: descElement ? descElement.textContent.trim() : '',
                    quantity: qtyElement ? parseInt(qtyElement.textContent.trim()) || 0 : 0,
                    status: status,
                    type: type
                };
                
                allCategories.push(category);
            });
            
            console.log('收集到的分类数据:', allCategories);
        }
        
        // 设置前端筛选功能
        function setupFrontendFiltering() {
            const searchField = document.getElementById('search');
            const typeSelect = document.getElementById('type');
            const statusSelect = document.getElementById('status');
            const searchBtn = document.getElementById('search-btn');
            const resetFilters = document.getElementById('reset-filters');
            const searchForm = document.getElementById('search-form');
            const searchIndicator = document.getElementById('search-indicator');
            const filterSummary = document.getElementById('filter-summary');
            
            // 如果找不到必要的元素，则退出
            if (!searchField || !typeSelect || !statusSelect || !searchBtn || !searchForm) return;
            
            // 点击搜索按钮时执行前端筛选
            searchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                performFrontendFiltering();
            });
            
            // 重置筛选按钮
            resetFilters.addEventListener('click', function(e) {
                e.preventDefault();
                searchField.value = '';
                typeSelect.value = '';
                statusSelect.value = '';
                
                // 重置后立即执行前端筛选
                performFrontendFiltering();
            });
            
            // 添加Enter键按下事件
            searchField.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performFrontendFiltering();
                }
            });
            
            // 防止表单提交
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                performFrontendFiltering();
                return false;
            });
            
            // 更新筛选摘要信息
            function updateFilterSummary(filteredCount, totalCount) {
                if (!filterSummary) return;
                
                const searchText = searchField.value.trim();
                const typeIndex = typeSelect.selectedIndex;
                const statusIndex = statusSelect.selectedIndex;
                
                // 获取文本而不是值
                const typeValue = typeIndex >= 0 ? typeSelect.options[typeIndex].text : 'All types';
                const statusValue = statusIndex >= 0 ? statusSelect.options[statusIndex].text : 'All states';
                
                // 保存提示元素
                const tipElement = filterSummary.querySelector('span');
                
                let summaryText = '';
                
                if (searchText || typeValue !== 'All types' || statusValue !== 'All states') {
                    summaryText = `Showing ${filteredCount} of ${totalCount} categories`;
                    
                    let filterDetails = [];
                    if (searchText) filterDetails.push(`matching "${searchText}"`);
                    if (typeValue !== 'All types') filterDetails.push(`type: ${typeValue}`);
                    if (statusValue !== 'All states') filterDetails.push(`status: ${statusValue}`);
                    
                    if (filterDetails.length > 0) {
                        summaryText += ` (${filterDetails.join(', ')})`;
                    }
                } else {
                    summaryText = `Showing all ${totalCount} categories`;
                }
                
                // 设置摘要文本
                filterSummary.textContent = summaryText;
                
                // 如果有提示元素，重新添加
                if (tipElement) {
                    filterSummary.appendChild(tipElement);
                }
            }
            
            // 执行前端筛选
            function performFrontendFiltering() {
                // 每次过滤前重新收集数据，确保数据是最新的
                collectTableData();
                
                if (allCategories.length === 0) {
                    console.log('没有可用的分类数据进行过滤');
                    updateFilterSummary(0, 0);
                    return;
                }
                
                const searchText = searchField.value.toLowerCase();
                const typeFilter = typeSelect.value.toLowerCase();
                const statusFilter = statusSelect.value;
                
                console.log('开始过滤, 关键词:', searchText, ', 类型:', typeFilter, ', 状态:', statusFilter);
                
                // 显示搜索指示器
                if (searchIndicator) searchIndicator.classList.remove('hidden');
                
                // 使用setTimeout使UI能够更新
                setTimeout(() => {
                    try {
                        // 重置表格显示并清除所有动画类
                        allCategories.forEach(category => {
                            if (category && category.element) {
                                const row = category.element;
                                row.style.display = 'none';
                                row.classList.remove('table-row');
                                row.style.animationDelay = '';
                            }
                        });
                        
                        // 应用筛选
                        const filteredCategories = allCategories.filter(category => {
                            // 搜索文本匹配
                            const textMatch = searchText === '' || 
                                category.name.toLowerCase().includes(searchText) || 
                                category.code.toLowerCase().includes(searchText) || 
                                (category.description && category.description.toLowerCase().includes(searchText));
                            
                            // 类型匹配 (根据选择的类型进行过滤)
                            const typeMatch = typeFilter === '' || 
                                            (typeFilter && category.type && category.type.toLowerCase() === typeFilter);
                            
                            // 状态匹配
                            let statusMatch = true;
                            if (statusFilter === '1') {
                                statusMatch = category.status.includes('Open up');
                            } else if (statusFilter === '0') {
                                statusMatch = category.status.includes('Disable');
                            }
                            
                            const result = textMatch && typeMatch && statusMatch;
                            return result;
                        });
                        
                        console.log('过滤后的分类:', filteredCategories.length, '/', allCategories.length);
                        
                        // 显示匹配的行
                        filteredCategories.forEach(category => {
                            if (category && category.element) {
                                category.element.style.display = '';
                            }
                        });
                        
                        // 更新筛选摘要信息
                        updateFilterSummary(filteredCategories.length, allCategories.length);
                        
                        // 更新空状态显示
                        const emptyRow = document.querySelector('tr[data-empty]');
                        if (emptyRow) {
                            if (filteredCategories.length === 0) {
                                emptyRow.style.display = '';
                            } else {
                                emptyRow.style.display = 'none';
                            }
                        }
                        
                        // 为筛选后的行添加动画 - 延迟一帧添加动画类，确保样式重置生效
                        requestAnimationFrame(() => {
                            filteredCategories.forEach((category, index) => {
                                if (category && category.element) {
                                    const row = category.element;
                                    row.classList.add('table-row');
                                    row.style.animationDelay = `${0.05 * index}s`;
                                }
                            });
                        });
                    } catch (error) {
                        console.error('过滤出错:', error);
                    } finally {
                        // 隐藏搜索指示器
                        if (searchIndicator) searchIndicator.classList.add('hidden');
                    }
                }, 100);
            }
            
            // 防抖函数
            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        func.apply(context, args);
                    }, wait);
                };
            }
        }
        
        // 同时监听多种页面加载事件，确保在各种情况下都能正确初始化
        document.addEventListener('DOMContentLoaded', initializeElements);
        document.addEventListener('turbolinks:load', initializeElements);
        document.addEventListener('livewire:load', initializeElements);
        
        // 如果页面已经加载完成，则立即初始化
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(initializeElements, 1);
        } else {
            // 确保页面加载后立即初始化
            initializeElements();
        }
    })();
</script>
@endsection

@push('styles')
<!-- Animate.css for SweetAlert2 animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    /* 状态标签样式 */
    .status-badge {
        transition: all 0.3s ease;
    }
    
    .status-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    /* 操作按钮效果 */
    .action-btn {
        transform: translateZ(0);
        transition: all 0.2s ease;
    }
    
    .action-btn:hover {
        transform: translateY(-1px);
    }
    
    .action-btn:active {
        transform: translateY(1px);
    }
    
    /* 表格行样式 */
    .table-row {
        transform: translateZ(0);
        animation: tableRowFadeIn 0.5s ease-out forwards;
        opacity: 0;
    }
    
    @keyframes tableRowFadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .table-row:nth-child(1) { animation-delay: 0.1s; }
    .table-row:nth-child(2) { animation-delay: 0.15s; }
    .table-row:nth-child(3) { animation-delay: 0.2s; }
    .table-row:nth-child(4) { animation-delay: 0.25s; }
    .table-row:nth-child(5) { animation-delay: 0.3s; }
    .table-row:nth-child(6) { animation-delay: 0.35s; }
    .table-row:nth-child(7) { animation-delay: 0.4s; }
    .table-row:nth-child(8) { animation-delay: 0.45s; }
    .table-row:nth-child(9) { animation-delay: 0.5s; }
    .table-row:nth-child(10) { animation-delay: 0.55s; }
    
    /* 分页控件美化 */
    .pagination-container nav {
        display: flex;
        justify-content: center;
    }
    
    .pagination-container .flex.justify-between.flex-1 {
        display: none; /* 隐藏不必要的元素 */
    }
    
    .pagination-container nav > div:last-child {
        width: 100%;
    }
    
    .pagination-container span[aria-current="page"] > span {
        background-color: #4f46e5 !important;
        border-color: #4f46e5 !important;
        color: white !important;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1), 0 2px 4px -1px rgba(79, 70, 229, 0.06);
        transform: scale(1.05);
        z-index: 10;
        position: relative;
    }
    
    .pagination-container a, 
    .pagination-container span {
        transition: all 0.3s ease;
        margin: 0 2px;
    }
    
    .pagination-container a:hover {
        background-color: #f3f4f6 !important;
        border-color: #6366f1 !important;
        color: #4f46e5 !important;
        transform: translateY(-1px);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    /* 移动设备适配 */
    @media (max-width: 640px) {
        /* 表格调整 */
        .table-fixed {
            table-layout: fixed;
        }
        
        th, td {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        
        thead th:nth-child(3),
        tbody td:nth-child(3) {
            width: 20% !important;
        }
        
        thead th:nth-child(4),
        tbody td:nth-child(4) {
            width: 30% !important;
        }
        
        /* 状态标签缩小 */
        .status-badge {
            padding: 0.25rem 0.5rem;
            font-size: 0.65rem;
        }
        
        /* 操作按钮缩小和垂直排列 */
        .flex.justify-end.space-x-2 {
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }
        
        .action-btn {
            width: auto;
            white-space: nowrap;
        }
        
        /* 搜索表单调整 */
        .bg-gray-50.rounded-lg.p-4 {
            padding: 0.75rem !important;
        }
        
        .grid.grid-cols-1.md\:grid-cols-4.gap-4 {
            gap: 0.75rem !important;
        }
        
        /* 分页样式调整 */
        .pagination-container a,
        .pagination-container span {
            padding: 0.4rem 0.5rem !important;
            font-size: 0.75rem !important;
            margin: 0 1px !important;
        }
        
        /* 标题和添加按钮调整 */
        .flex.flex-col.md\:flex-row h2 {
            font-size: 1.5rem !important;
            line-height: 1.3 !important;
            margin-bottom: 0.5rem;
        }
        
        .flex.flex-col.md\:flex-row .mt-4.md\:mt-0.md\:ml-4 a {
            width: 100%;
            justify-content: center;
        }
    }
    
    /* 空状态动画 */
    .empty-state svg {
        animation: pulse 3s infinite ease-in-out;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
    }
</style>
@endpush 