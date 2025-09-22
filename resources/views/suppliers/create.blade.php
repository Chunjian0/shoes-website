@extends('layouts.app')

@section('title', 'Create a supplier')

@section('styles')
<style>
/* 动画效果 */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes shimmer {
    0% { background-position: -100% 0; }
    100% { background-position: 200% 0; }
}

@keyframes borderPulse {
    0% { border-color: rgba(79, 70, 229, 0.4); }
    50% { border-color: rgba(79, 70, 229, 0.8); }
    100% { border-color: rgba(79, 70, 229, 0.4); }
}

@keyframes highlight {
    0% { background-color: transparent; }
    30% { background-color: rgba(79, 70, 229, 0.2); }
    100% { background-color: transparent; }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-pulse-slow {
    animation: pulse 3s ease-in-out infinite;
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}

.animate-shimmer {
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}

.animate-border {
    animation: borderPulse 2s infinite;
}

.highlight-animation {
    animation: highlight 1.5s ease;
}

.animation-delay-100 { animation-delay: 0.1s; }
.animation-delay-200 { animation-delay: 0.2s; }
.animation-delay-300 { animation-delay: 0.3s; }
.animation-delay-400 { animation-delay: 0.4s; }
.animation-delay-500 { animation-delay: 0.5s; }

.bg-mesh {
    background-color: #f9fafb;
    background-image: radial-gradient(#4f46e5 0.5px, transparent 0.5px), radial-gradient(#4f46e5 0.5px, #f9fafb 0.5px);
    background-size: 20px 20px;
    background-position: 0 0, 10px 10px;
    background-attachment: fixed;
}

/* 卡片悬浮效果 */
.card-hover-effect {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-hover-effect:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* 表单元素悬停效果 */
.form-input-hover {
    transition: all 0.3s ease;
}

.form-input-hover:hover {
    border-color: #6366F1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* 响应式调整 */
@media (max-width: 768px) {
    .responsive-padding {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .responsive-container {
        width: 100%;
    }
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

.card {
    border-radius: 1rem;
    overflow: hidden;
    background: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* 按钮交互效果 */
.btn-animated {
    position: relative;
    overflow: hidden;
    transform: translate3d(0, 0, 0);
}

.btn-animated::before {
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

.btn-animated:hover::before {
    transform: translateX(0);
}

/* 输入框焦点动画 */
.input-focus-effect {
    transition: all 0.3s ease;
}

.input-focus-effect:focus {
    transform: scale(1.02);
}

/* 添加按钮动画 */
.add-btn-pulse {
    animation: pulse 2s infinite;
}

/* 全局输入框样式 */
input[type="text"],
input[type="email"],
input[type="number"],
input[type="password"],
input[type="tel"],
input[type="url"],
input[type="search"],
textarea,
select {
    height: auto;
    padding-top: 0.625rem;
    padding-bottom: 0.625rem;
    line-height: 1.5;
}

/* 提升checkboxes和radio buttons的可点击性 */
input[type="checkbox"],
input[type="radio"] {
    height: 1.25rem;
    width: 1.25rem;
    cursor: pointer;
}

/* 改善表单标签的清晰度 */
label {
    font-weight: 500;
    margin-bottom: 0.375rem;
}
</style>
@endsection

@section('breadcrumb')
<div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white animate-fade-in">
    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center space-x-2 mb-2 sm:mb-0">
            <h1 class="text-xl font-bold">Create New Supplier</h1>
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
                    <a href="{{ route('suppliers.index') }}" class="hover:text-indigo-200 transition-colors duration-200">
                        Suppliers
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-indigo-200">Create</span>
                </li>
            </ol>
        </nav>
    </div>
</div>
@endsection

@section('content')
<div class="bg-mesh py-8 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 responsive-padding">
        <!-- 主表单卡片 -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in animation-delay-100">
            <form action="{{ route('suppliers.store') }}" method="POST" id="supplierForm" class="relative">
                @csrf
                
                <div class="p-6 sm:p-8">
                    <!-- 页面标题和介绍 -->
                    <div class="mb-8 border-b border-gray-200 pb-5">
                        <div class="flex items-center">
                            <div class="mr-4 flex-shrink-0 bg-indigo-100 rounded-full p-3 animate-pulse-slow">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Create New Supplier</h2>
                                <p class="mt-1 text-sm text-gray-500">Add a new supplier to your inventory management system.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 卡片式表单布局 -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <!-- 左侧：基本信息卡片 -->
                        <div class="lg:col-span-8 space-y-8">
                            <!-- 基本信息卡片 -->
                            <div class="card border border-gray-200 group hover:border-indigo-400 animate-fade-in animation-delay-200">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Supplier Information
                                        </h3>
                                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full font-medium">Required</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="form-group animate-fade-in animation-delay-100">
                                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Supplier Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover input-focus-effect py-2.5">
                                            @error('name')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="form-group animate-fade-in animation-delay-200">
                                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Supplier Code <span class="text-red-500">*</span></label>
                                            <input type="text" name="code" id="code" value="{{ old('code') }}" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover input-focus-effect py-2.5">
                                            @error('code')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="form-group animate-fade-in animation-delay-300">
                                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                    </svg>
                                                </div>
                                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                                                    class="pl-10 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover input-focus-effect py-2.5">
                                            </div>
                                            @error('phone')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="form-group animate-fade-in animation-delay-400">
                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                                    </svg>
                                                </div>
                                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                                    class="pl-10 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover input-focus-effect py-2.5">
                                            </div>
                                            @error('email')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="col-span-2 form-group animate-fade-in animation-delay-500">
                                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </div>
                                                <input type="text" name="address" id="address" value="{{ old('address') }}"
                                                    class="pl-10 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover input-focus-effect py-2.5">
                                            </div>
                                            @error('address')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 联系信息卡片 -->
                            <div class="card border border-gray-200 group hover:border-indigo-400 animate-fade-in animation-delay-300">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            Contact Information
                                        </h3>
                                        <button type="button" onclick="window.addContact()" class="flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900 add-btn-pulse">
                                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Add Contact
                                        </button>
                                    </div>
                                    
                                    <div id="contacts-container" class="space-y-4 custom-scrollbar max-h-96 overflow-y-auto pr-2">
                                        <!-- Contact form will be dynamically added here by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 右侧：财务信息和设置 -->
                        <div class="lg:col-span-4 space-y-8">
                            <!-- 财务信息卡片 -->
                            <div class="card border border-gray-200 hover:border-indigo-400 animate-fade-in animation-delay-400">
                                <div class="p-6">
                                    <div class="flex items-center mb-4">
                                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900">Financial Information</h3>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div class="form-group animate-fade-in animation-delay-100">
                                            <label for="credit_limit" class="block text-sm font-medium text-gray-700 mb-1">Credit Limit</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">RM</span>
                                                </div>
                                                <input type="number" name="credit_limit" id="credit_limit" value="{{ old('credit_limit') }}" step="0.01" min="0"
                                                    class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover py-2.5">
                                            </div>
                                            @error('credit_limit')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="form-group animate-fade-in animation-delay-200">
                                            <label for="payment_term" class="block text-sm font-medium text-gray-700 mb-1">Payment Terms (Days)</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <input type="number" name="payment_term" id="payment_term" value="{{ old('payment_term', 30) }}" min="0"
                                                    class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover py-2.5">
                                            </div>
                                            @error('payment_term')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 附加信息卡片 -->
                            <div class="card border border-gray-200 hover:border-indigo-400 animate-fade-in animation-delay-500">
                                <div class="p-6">
                                    <div class="flex items-center mb-4">
                                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div class="form-group animate-fade-in animation-delay-100">
                                            <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                                            <textarea name="remarks" id="remarks" rows="4"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover py-2.5">{{ old('remarks') }}</textarea>
                                            @error('remarks')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="form-group animate-fade-in animation-delay-200">
                                            <label for="is_active" class="flex items-center">
                                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-5 w-5">
                                                <span class="ml-2 text-sm text-gray-700">Active Supplier</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 提交按钮 -->
                            <div class="sticky top-4 card border border-gray-200 hover:border-indigo-400 animate-fade-in animation-delay-600">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <button type="submit" class="w-full flex justify-center items-center px-4 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all ease-in-out duration-150 btn-animated">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            SAVE SUPPLIER
                                        </button>
                                        
                                        <a href="{{ route('suppliers.index') }}" class="w-full flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all ease-in-out duration-150">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.contactCount = 0;

// 添加联系人表单
window.addContact = function() {
    const container = document.getElementById('contacts-container');
    const contactHtml = `
        <div class="contact-form bg-white border border-gray-200 rounded-xl p-6 mb-4 animate-fade-in shadow-sm hover:shadow-md transition-all duration-300 card-hover-effect">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-sm font-medium text-gray-800 flex items-center">
                    <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Contact Person #${window.contactCount + 1}
                </h4>
                <button type="button" onclick="removeContact(this)" class="text-sm text-red-600 hover:text-red-900 transition-colors duration-150 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" name="contacts[${window.contactCount}][name]" required
                            class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover py-2.5">
                    </div>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="text" name="contacts[${window.contactCount}][position]"
                            class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover py-2.5">
                    </div>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <input type="text" name="contacts[${window.contactCount}][phone]" required
                            class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover py-2.5">
                    </div>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input type="email" name="contacts[${window.contactCount}][email]"
                            class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover py-2.5">
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                    <textarea name="contacts[${window.contactCount}][remarks]" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 form-input-hover"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="inline-flex items-center">
                        <input type="hidden" name="contacts[${window.contactCount}][is_primary]" value="0">
                        <input type="radio" name="primary_contact" value="${window.contactCount}" 
                            onchange="updatePrimaryContact(this)"
                            class="rounded-full border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-5 w-5">
                        <span class="ml-2 text-sm text-gray-700">Primary Contact</span>
                    </label>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', contactHtml);
    
    // 添加动画效果
    const newContact = container.lastElementChild;
    newContact.classList.add('highlight-animation');
    
    // 更新联系人计数
    window.contactCount++;

    // 如果这是第一个联系人，默认设为主要联系人
    if (window.contactCount === 1) {
        const radio = container.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
            updatePrimaryContact(radio);
        }
    }
};

// 删除联系人表单
window.removeContact = function(button) {
    const contactForm = button.closest('.contact-form');
    
    // 添加淡出动画
    contactForm.style.transition = 'all 0.3s ease';
    contactForm.style.opacity = '0';
    contactForm.style.transform = 'translateY(10px)';
    
    // 动画结束后移除元素
    setTimeout(() => {
        contactForm.remove();
        
        // 重新编号所有联系人
        const contactForms = document.querySelectorAll('.contact-form');
        contactForms.forEach((form, index) => {
            const heading = form.querySelector('h4');
            if (heading) {
                heading.innerHTML = `
                    <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Contact Person #${index + 1}
                `;
            }
        });
        
        // 如果没有选中的主要联系人但还有联系人表单，选择第一个为主要联系人
        const hasCheckedPrimary = document.querySelector('input[name="primary_contact"]:checked');
        if (!hasCheckedPrimary && contactForms.length > 0) {
            const firstRadio = document.querySelector('input[name="primary_contact"]');
            if (firstRadio) {
                firstRadio.checked = true;
                updatePrimaryContact(firstRadio);
            }
        }
    }, 300);
};

// 更新主要联系人
window.updatePrimaryContact = function(radio) {
    // 重置所有联系人的is_primary为0
    document.querySelectorAll('input[name$="[is_primary]"]').forEach(input => {
        input.value = "0";
    });
    
    // 设置选中的联系人is_primary为1
    if (radio.checked) {
        const contactIndex = radio.value;
        const primaryInput = document.querySelector(`input[name="contacts[${contactIndex}][is_primary]"]`);
        if (primaryInput) {
            primaryInput.value = "1";
            
            // 添加高亮动画
            const contactForm = radio.closest('.contact-form');
            contactForm.classList.add('highlight-animation');
            
            // 移除之前动画类
            setTimeout(() => {
                contactForm.classList.remove('highlight-animation');
            }, 1500);
        }
    }
};

// 表单提交前验证
document.getElementById('supplierForm').addEventListener('submit', function(e) {
    let hasContacts = window.contactCount > 0;
    let hasPrimaryContact = document.querySelector('input[name="primary_contact"]:checked') !== null;
    
    if (!hasContacts) {
        e.preventDefault();
        Swal.fire({
            title: 'Missing Contact',
            text: 'Please add at least one contact person for this supplier.',
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#4f46e5'
        });
        return false;
    }
    
    if (hasContacts && !hasPrimaryContact) {
        e.preventDefault();
        Swal.fire({
            title: 'Primary Contact Required',
            text: 'Please select a primary contact for this supplier.',
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#4f46e5'
        });
        return false;
    }
    
    // 显示加载动画
    Swal.fire({
        title: 'Saving...',
        text: 'Creating your new supplier',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
});

// 页面加载后添加表单元素交互效果和默认联系人
document.addEventListener('DOMContentLoaded', function() {
    // 添加输入框焦点效果
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.closest('.form-group')?.classList.add('animate-border');
        });
        
        input.addEventListener('blur', function() {
            this.closest('.form-group')?.classList.remove('animate-border');
        });
    });
    
    // 添加一个默认联系人表单
    window.addContact();
    
    // 页面滚动动画
    const animatedElements = document.querySelectorAll('.animate-fade-in');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
});
</script>
@endpush 