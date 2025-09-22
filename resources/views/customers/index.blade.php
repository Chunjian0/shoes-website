@extends('layouts.app')

@section('title', 'Customer Management')

@section('styles')
<style>
    /* 卡片和动画效果 */
    .card-hover-effect {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card-hover-effect:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
    
    .animation-delay-100 { animation-delay: 0.1s; }
    .animation-delay-200 { animation-delay: 0.2s; }
    .animation-delay-300 { animation-delay: 0.3s; }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .animate-pulse-slow {
        animation: pulse 3s ease-in-out infinite;
    }
    
    /* 表格响应式设计 */
    @media (max-width: 1024px) {
        .responsive-table {
            display: block;
        }
        .responsive-table thead {
            display: none;
        }
        .responsive-table tbody {
            display: block;
        }
        .responsive-table tbody tr {
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 0.5rem;
        }
        .responsive-table tbody td {
            display: flex;
            flex-direction: column;
            border-bottom: 1px solid #f3f4f6;
            padding: 0.75rem 0.5rem;
        }
        .responsive-table tbody td:last-child {
            border-bottom: none;
        }
        .responsive-table td[data-label]:before {
            content: attr(data-label);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        .responsive-table .flex.justify-end {
            justify-content: flex-start;
        }
    }
    
    /* 会员级别标签 */
    .badge-member {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1;
    }
    
    .badge-platinum {
        background-color: #9333ea;
        color: white;
    }
    
    .badge-gold {
        background-color: #f59e0b;
        color: white;
    }
    
    .badge-silver {
        background-color: #6b7280;
        color: white;
    }
    
    .badge-normal {
        background-color: #10b981;
        color: white;
    }
    
    /* 按钮交互效果增强 */
    .action-btn {
        transition: all 0.2s ease-in-out;
        border-radius: 8px;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
    }
    
    .action-btn:active {
        transform: translateY(1px);
    }
    
    .action-btn-view {
        color: #4f46e5;
        background-color: rgba(79, 70, 229, 0.1);
    }
    
    .action-btn-view:hover {
        background-color: rgba(79, 70, 229, 0.2);
    }
    
    .action-btn-edit {
        color: #2563eb;
        background-color: rgba(37, 99, 235, 0.1);
    }
    
    .action-btn-edit:hover {
        background-color: rgba(37, 99, 235, 0.2);
    }
    
    .action-btn-delete {
        color: #dc2626;
        background-color: rgba(220, 38, 38, 0.1);
    }
    
    .action-btn-delete:hover {
        background-color: rgba(220, 38, 38, 0.2);
    }
    
    /* 筛选器动画优化 */
    .filter-open {
        max-height: 500px;
        opacity: 1;
        transition: max-height 0.4s ease-in-out, opacity 0.3s ease-in-out, transform 0.3s ease-out;
        transform: translateY(0);
    }
    
    .filter-closed {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-in-out, opacity 0.2s ease-in-out, transform 0.3s ease-in;
        transform: translateY(-10px);
    }
    
    /* 响应式按钮组 */
    @media (max-width: 640px) {
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            width: 100%;
        }
        
        .action-btn {
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 0.5rem;
        }
    }
</style>
@endsection

@section('breadcrumb')
<div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white animate-fade-in">
    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center space-x-2 mb-2 sm:mb-0">
            <svg class="h-8 w-8 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h1 class="text-xl font-bold">Customer Management</h1>
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
                    <span class="text-indigo-200">Customers</span>
        </li>
    </ol>
</nav>
    </div>
</div>
@endsection

@section('content')
<div class="bg-gray-50 min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 筛选器和操作按钮 -->
        <div class="bg-white rounded-xl shadow-sm mb-4 p-4 animate-fade-in">
            <div class="flex flex-wrap justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">
                    Customers <span class="text-sm font-normal text-gray-500">({{ $customers->total() }})</span>
                </h2>
                <div class="flex space-x-2 mt-2 sm:mt-0">
                    <button type="button" id="toggleFilters" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="h-4 w-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span>Filters</span>
                    </button>
                    <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="h-4 w-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Customer
                    </a>
                </div>
            </div>

            <!-- 活跃筛选条件展示 -->
            @if(request('search') || request('member_level') || request('last_visit'))
            <div class="flex flex-wrap items-center text-xs text-gray-500 mb-2">
                <span class="mr-2">Active filters:</span>
                @if(request('search'))
                <div class="mr-2 mb-1 py-1 px-2 bg-gray-100 rounded-full">
                    "{{ request('search') }}"
                </div>
                @endif
                
                @if(request('member_level'))
                <div class="mr-2 mb-1 py-1 px-2 bg-gray-100 rounded-full">
                    Level: {{ ucfirst(request('member_level')) }}
                </div>
                @endif
                
                @if(request('last_visit'))
                <div class="mr-2 mb-1 py-1 px-2 bg-gray-100 rounded-full">
                    Last {{ request('last_visit') }} days
                </div>
                @endif
                
                <a href="{{ route('customers.index') }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                    Clear all
                </a>
            </div>
            @endif
        </div>

        <!-- 筛选器部分 -->
        <div id="filtersContainer" class="bg-white rounded-xl shadow-sm mb-4 filter-transition filter-closed animate-fade-in animation-delay-100">
            <div class="p-4">
                <form action="{{ route('customers.index') }}" method="GET" id="filterForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- 搜索框 -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md" 
                                    placeholder="Search by name, IC number or contact">
                    </div>
                </div>

                        <!-- 会员等级筛选 -->
                <div>
                            <label for="member_level" class="block text-sm font-medium text-gray-700 mb-1">Member Level</label>
                            <select id="member_level" name="member_level" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Levels</option>
                                <option value="normal" {{ request('member_level') == 'normal' ? 'selected' : '' }}>Regular</option>
                        <option value="silver" {{ request('member_level') == 'silver' ? 'selected' : '' }}>Silver</option>
                                <option value="gold" {{ request('member_level') == 'gold' ? 'selected' : '' }}>Gold</option>
                        <option value="platinum" {{ request('member_level') == 'platinum' ? 'selected' : '' }}>Platinum</option>
                    </select>
                </div>

                        <!-- 最近访问筛选 -->
                <div>
                            <label for="last_visit" class="block text-sm font-medium text-gray-700 mb-1">Recent Visit</label>
                            <select id="last_visit" name="last_visit" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Time</option>
                                <option value="7" {{ request('last_visit') == '7' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="30" {{ request('last_visit') == '30' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="90" {{ request('last_visit') == '90' ? 'selected' : '' }}>Last 90 Days</option>
                                <option value="365" {{ request('last_visit') == '365' ? 'selected' : '' }}>Last Year</option>
                    </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="resetFilters()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            Reset
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                            Apply Filters
                        </button>
                </div>
            </form>
            </div>
        </div>

        <!-- 客户列表和信息摘要 -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in animation-delay-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 responsive-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Member
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        points
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Last visit
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($customers as $customer)
                            <tr class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                                <td class="p-4 whitespace-nowrap text-sm" data-label="Name">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                            <span class="font-semibold">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $customer->ic_number ?? 'Not set' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm" data-label="Contact">
                                    <div class="text-sm text-gray-900">{{ $customer->contact_number ?? 'Not set' }}</div>
                                    <div class="text-sm text-gray-500">{{ $customer->email ?? 'Not set' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="Member Level">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $customer->member_level_color }}">
                                        {{ ucfirst($customer->member_level) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Points">
                                    {{ $customer->points }} points
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Last Visit">
                                    {{ $customer->last_visit_at ? $customer->last_visit_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-3 justify-end">
                                        <a href="{{ route('customers.show', $customer) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        <a href="{{ route('customers.edit', $customer) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    </div>
                                </td>
                            </tr>
                                @empty
                                    <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-500 text-lg mb-2">No customers found</p>
                                        <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                            <svg class="h-4 w-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                            Add Customer
                                        </a>
                                    </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
            </div>
            
            <!-- 分页控件 -->
            <div class="px-4 py-3 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        @if($customers->total() > 0)
                            {{ $customers->firstItem() }}-{{ $customers->lastItem() }} of {{ $customers->total() }}
                        @else
                            No customers found
                        @endif
                    </div>
                    <div>
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast 通知容器 -->
<div id="toastContainer" class="fixed right-0 bottom-0 p-4 z-50 flex flex-col items-end space-y-2"></div>

<!-- 删除确认模态框 -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- 背景遮罩 -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
        
        <!-- 居中内容 -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block overflow-hidden text-left align-bottom bg-white rounded-lg shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 p-4">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none" onclick="closeDeleteModal()">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="sm:flex sm:items-start">
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Delete Customer</h3>
                    <div class="mt-2">
                        <p id="deleteModalText" class="text-sm text-gray-500"></p>
                </div>
            </div>
        </div>

            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                </form>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm" onclick="closeDeleteModal()">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection 

@push('scripts')
<script>
    (function() {
        'use strict';
        
        // 初始化标记，确保只执行一次
        let isInitialized = false;
        
        // 主初始化函数
        function initPage() {
            if (isInitialized) return;
            
            // 初始化筛选器切换
            initFilters();
            
            // 初始化表单自动提交功能
            initAutoSubmit();
            
            // 初始化响应式表格
            initResponsiveTables();
            
            // 初始化通知系统
            initNotifications();
            
            // 标记已初始化
            isInitialized = true;
        }
        
        // 筛选器初始化
        function initFilters() {
            const toggleBtn = document.getElementById('toggleFilters');
            const filtersContainer = document.getElementById('filtersContainer');
            
            if (!toggleBtn || !filtersContainer) return;
            
            // 如果有活跃的筛选条件，自动展开
            if ({{ request('search') || request('member_level') || request('last_visit') ? 'true' : 'false' }}) {
                filtersContainer.classList.remove('filter-closed');
                filtersContainer.classList.add('filter-open');
            }
            
            // 切换按钮点击事件
            toggleBtn.addEventListener('click', function() {
                if (filtersContainer.classList.contains('filter-closed')) {
                    filtersContainer.classList.remove('filter-closed');
                    filtersContainer.classList.add('filter-open');
                } else {
                    filtersContainer.classList.add('filter-closed');
                    filtersContainer.classList.remove('filter-open');
                }
            });
        }
        
        // 表单自动提交初始化
        function initAutoSubmit() {
            const form = document.getElementById('filterForm');
            if (!form) return;
            
            const selects = form.querySelectorAll('select');
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    form.submit();
                });
            });
        }
        
        // 重置筛选器
        window.resetFilters = function() {
            window.location.href = '{{ route('customers.index') }}';
        };
        
        // 删除确认
        window.confirmDelete = function(id, name) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            const text = document.getElementById('deleteModalText');
            
            if (!modal || !form || !text) return;
            
            form.action = '{{ url('customers') }}/' + id;
            text.textContent = `Are you sure you want to delete ${name}? This action cannot be undone.`;
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };
        
        // 关闭删除确认框
        window.closeDeleteModal = function() {
            const modal = document.getElementById('deleteModal');
            if (!modal) return;
            
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        };
        
        // 响应式表格增强
        function initResponsiveTables() {
            const updateTables = () => {
                const tables = document.querySelectorAll('.responsive-table');
                if (!tables.length) return;
                
                tables.forEach(table => {
                    const isMobile = window.innerWidth < 768;
                    
                    if (isMobile) {
                        if (!table.classList.contains('mobile-ready')) {
                            table.classList.add('mobile-ready');
                        }
                    } else if (table.classList.contains('mobile-ready')) {
                        table.classList.remove('mobile-ready');
                    }
                });
            };
            
            updateTables();
            window.addEventListener('resize', updateTables);
        }
        
        // 通知系统
        function initNotifications() {
            // Session闪存消息
            const successMsg = "{{ session('success') }}";
            const errorMsg = "{{ session('error') }}";
            
            if (successMsg) {
                showToast(successMsg, 'success');
            } else if (errorMsg) {
                showToast(errorMsg, 'error');
            }
        }
        
        // 显示Toast通知
        window.showToast = function(message, type = 'info') {
            if (!message) return;
            
            const container = document.getElementById('toastContainer');
            if (!container) return;
            
            const toast = document.createElement('div');
            
            // 设置样式
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            
            toast.className = `${bgColor} text-white py-2 px-4 rounded shadow-lg flex items-center transform transition-all duration-300 opacity-0 translate-y-2`;
            
            // 设置内容
            toast.innerHTML = `
                <div class="mr-2">
                    ${type === 'success' 
                        ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>'
                        : type === 'error'
                            ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>' 
                            : '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-1 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg>'
                    }
                </div>
                <span>${message}</span>
            `;
            
            container.appendChild(toast);
            
            // 动画显示
            setTimeout(() => {
                toast.classList.remove('opacity-0', 'translate-y-2');
            }, 10);
            
            // 自动消失
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => {
                    container.removeChild(toast);
                }, 300);
            }, 3000);
        };
        
        // 在各种环境下初始化页面
        ['DOMContentLoaded', 'turbolinks:load', 'livewire:load'].forEach(event => {
            document.addEventListener(event, initPage);
        });
        
        // 如果DOM已经加载完成，立即初始化
        if (['complete', 'interactive', 'loaded'].includes(document.readyState)) {
            initPage();
        }
    })();
</script>
@endpush 