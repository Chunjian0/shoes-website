@extends('layouts.app')

@section('title', 'Create Customer')

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
    0% { border-color: rgba(99, 102, 241, 0.4); }
    50% { border-color: rgba(99, 102, 241, 0.8); }
    100% { border-color: rgba(99, 102, 241, 0.4); }
}

@keyframes highlight {
    0% { background-color: transparent; }
    30% { background-color: rgba(79, 70, 229, 0.2); }
    100% { background-color: transparent; }
}

@keyframes spinner {
    to { transform: rotate(360deg); }
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

.animate-spinner {
    animation: spin 1s linear infinite;
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

@media (prefers-reduced-motion) {
    .animate-float, .animate-pulse-slow, .animate-shimmer, .animate-border {
        animation: none !important;
    }
}

/* 移动设备优化 */
@media (max-width: 640px) {
    .hide-on-mobile {
        display: none;
    }
    .card-stack {
        margin-bottom: 1rem;
    }
}

/* 加载性能优化 */
.low-motion {
    transition: none !important;
    animation: none !important;
}

/* 高对比度模式支持 */
@media (prefers-contrast: more) {
    :root {
        --text-color: #000;
        --bg-color: #fff;
        --border-color: #000;
    }
}

/* 暗黑模式支持 */
@media (prefers-color-scheme: dark) {
    .dark-mode-support {
        --text-color: #f9fafb;
        --bg-color: #111827;
        --border-color: #4b5563;
    }
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

/* 卡片布局 */
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
</style>
@endsection

@section('breadcrumb')
<div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white animate-fade-in">
    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center space-x-2 mb-2 sm:mb-0">
            <h1 class="text-xl font-bold">Create New Customer</h1>
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
                    <a href="{{ route('customers.index') }}" class="hover:text-indigo-200 transition-colors duration-200">
                        Customers
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
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in animation-delay-400">
            <form action="{{ route('customers.store') }}" method="POST" id="customerForm" class="relative">
                @csrf
                <div class="p-6 sm:p-8">                    
                    <!-- 卡片式表单布局 -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- 左侧：个人信息卡片 -->
                        <div class="lg:col-span-8 space-y-6">
                            <!-- 个人信息卡片 -->
                            <div class="card border border-gray-200 group hover:border-indigo-400 animate-fade-in animation-delay-100">
                                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-2 mr-3 group-hover:bg-indigo-200 transition-colors duration-300">
                                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
                                    </div>
                                </div>
    <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                                        <!-- 姓名字段 -->
                                        <div class="animate-fade-in animation-delay-200">
                                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                                Full Name <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative group">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
        </div>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                                       class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all duration-200 py-3 shadow-sm hover:bg-white form-input-hover">
                                            </div>
                    @error('name')
                                            <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
                    @enderror
                </div>

                                        <!-- IC号码字段 -->
                                        <div class="animate-fade-in animation-delay-300">
                                            <label for="ic_number" class="block text-sm font-medium text-gray-700 mb-1">
                                                IC Number
                                            </label>
                                            <div class="relative group">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"></path>
                                                    </svg>
                                                </div>
                    <input type="text" name="ic_number" id="ic_number" value="{{ old('ic_number') }}"
                                                       class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all duration-200 py-3 shadow-sm hover:bg-white form-input-hover">
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500 flex items-center">
                                                <svg class="w-3 h-3 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                </svg>
                                                Format: 12 digits, first 6 digits may contain birthday info
                                            </p>
                    @error('ic_number')
                                            <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
                    @enderror
                                        </div>
                                    </div>
                                </div>
                </div>

                            <!-- 联系方式卡片 -->
                            <div class="card border border-gray-200 group hover:border-indigo-400 animate-fade-in animation-delay-200">
                                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-2 mr-3 group-hover:bg-indigo-200 transition-colors duration-300">
                                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                                        <!-- 联系电话字段 -->
                                        <div class="animate-fade-in animation-delay-400">
                                            <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">
                                                Contact Number
                                            </label>
                                            <div class="relative group">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                    </svg>
                                                </div>
                    <input type="tel" name="contact_number" id="contact_number" value="{{ old('contact_number') }}"
                                                       class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all duration-200 py-3 shadow-sm hover:bg-white form-input-hover">
                                            </div>
                    @error('contact_number')
                                            <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
                    @enderror
                </div>

                                        <!-- 电子邮件字段 -->
                                        <div class="animate-fade-in animation-delay-500">
                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                                Email Address
                                            </label>
                                            <div class="relative group">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                                                       class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all duration-200 py-3 shadow-sm hover:bg-white form-input-hover">
                                            </div>
                    @error('email')
                                            <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 地址和备注卡片 -->
                            <div class="card border border-gray-200 group hover:border-indigo-400 animate-fade-in animation-delay-300">
                                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-2 mr-3 group-hover:bg-indigo-200 transition-colors duration-300">
                                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">Address & Notes</h3>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <div class="space-y-5">
                                        <!-- 地址字段 -->
                                        <div class="animate-fade-in animation-delay-500">
                                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                                Address
                                            </label>
                                            <div class="relative group">
                                                <div class="absolute top-3 left-3 pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h2a1 1 0 001-1v-6.5l-3.5-3.5"></path>
                                                    </svg>
                                                </div>
                                                <textarea name="address" id="address" rows="3" 
                                                          class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all duration-200 shadow-sm hover:bg-white form-input-hover">{{ old('address') }}</textarea>
                                            </div>
                                            @error('address')
                                            <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
                    @enderror
                </div>

                                        <!-- 备注字段 -->
                                        <div class="animate-fade-in animation-delay-500">
                                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                                Notes
                                            </label>
                                            <div class="relative group">
                                                <div class="absolute top-3 left-3 pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </div>
                                                <textarea name="notes" id="notes" rows="3" 
                                                          class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all duration-200 shadow-sm hover:bg-white form-input-hover">{{ old('notes') }}</textarea>
                                            </div>
                                            @error('notes')
                                            <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 右侧：账户设置和会员等级 -->
                        <div class="lg:col-span-4 space-y-6">
                            <!-- 账户设置卡片 -->
                            <div class="card border border-gray-200 group hover:border-indigo-400 animate-fade-in animation-delay-400">
                                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-2 mr-3 group-hover:bg-indigo-200 transition-colors duration-300">
                                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">Account Settings</h3>
                                    </div>
                                </div>
                                <div class="p-6 space-y-6">
                                    <!-- 密码字段 -->
                                    <div class="animate-fade-in animation-delay-300">
                                        <label for="customer_password" class="block text-sm font-medium text-gray-700 mb-1">
                                            Password
                                        </label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                </svg>
                                            </div>
                                            <input type="text" name="customer_password" id="customer_password" value="{{ old('customer_password') }}"
                                                   class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all duration-200 py-3 shadow-sm hover:bg-white form-input-hover">
                        <button type="button" id="generatePasswordBtn" 
                                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-sm">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                                <span class="hide-on-mobile">Generate</span>
                        </button>
                    </div>
                                        <div class="mt-2 text-xs text-gray-500 flex items-center">
                                            <svg class="w-3 h-3 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                            Auto-generated if email is provided but password is empty
                                        </div>
                                        <div id="passwordStrength" class="hidden mt-3">
                        <div class="h-1.5 w-full bg-gray-200 rounded-full overflow-hidden">
                            <div id="passwordStrengthBar" class="h-full bg-gray-500 transition-all duration-300"></div>
                        </div>
                        <div class="flex justify-between mt-1">
                                                <span class="text-xs text-gray-500">Password Strength:</span>
                            <span id="strengthText" class="text-xs font-medium"></span>
                        </div>
                    </div>
                    @error('customer_password')
                                        <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
                    @enderror
                </div>

                                    <!-- 生日字段 -->
                                    <div class="animate-fade-in animation-delay-400">
                                        <label for="birthday" class="block text-sm font-medium text-gray-700 mb-1">
                                            Birthday
                                        </label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                    <input type="date" name="birthday" id="birthday" value="{{ old('birthday') }}"
                                                   placeholder="YYYY-MM-DD" pattern="([0-9]{4}|0[0-9]{3})-([0][1-9]|1[0-2])-([0][1-9]|[1-2][0-9]|3[0-1])" max="{{ date('Y-m-d') }}"
                                                   class="pl-10 block w-full rounded-lg border-gray-300 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 text-sm transition-all duration-200 py-3 shadow-sm hover:bg-white form-input-hover">
                                        </div>
                                        <div class="mt-2 text-xs text-gray-500 flex items-center">
                                            <svg class="w-3 h-3 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                            Format: YYYY-MM-DD (extracted from IC if provided)
                                        </div>
                    @error('birthday')
                                        <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
                    @enderror
                </div>
                                </div>
                            </div>
                            
                            <!-- 会员等级卡片 -->
                            <div class="card border border-gray-200 group hover:border-indigo-400 animate-fade-in animation-delay-500" id="memberLevelContainer">
                                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-2 mr-3 group-hover:bg-indigo-200 transition-colors duration-300">
                                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">Membership Level</h3>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <div class="space-y-2">
                                        <label for="member_level" class="block text-sm font-medium text-gray-700 mb-1">
                                            Select Membership Level
                                        </label>
                                        <div class="relative">
                                            <button type="button" id="memberLevelButton" 
                                                    class="relative w-full bg-gray-50 border border-gray-300 rounded-lg shadow-sm py-3 px-4 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm hover:bg-white transition-all duration-200">
                                                <div class="flex items-center">
                                                    <span id="memberLevelSelected" class="block truncate text-gray-700 flex-grow">
                                                        {{ old('member_level', 'normal') == 'normal' ? 'Ordinary member' : (old('member_level') == 'silver' ? 'Silver' : (old('member_level') == 'gold' ? 'Golden member' : 'Platinum')) }}
                                                    </span>
                                                    <svg class="h-5 w-5 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                                    </svg>
                                                </div>
                                            </button>
                                            
                                            <select name="member_level" id="member_level" class="hidden" value="{{ old('member_level', 'normal') }}">
                        <option value="normal" {{ old('member_level', 'normal') == 'normal' ? 'selected' : '' }}>Ordinary member</option>
                                                <option value="silver" {{ old('member_level') == 'silver' ? 'selected' : '' }}>Silver</option>
                                                <option value="gold" {{ old('member_level') == 'gold' ? 'selected' : '' }}>Golden member</option>
                                                <option value="platinum" {{ old('member_level') == 'platinum' ? 'selected' : '' }}>Platinum</option>
                    </select>
                                            
                                            <div id="memberLevelDropdown" class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-lg py-1 border border-gray-200 focus:outline-none text-sm hidden transform opacity-0 scale-95 transition-all duration-150 ease-in-out origin-top">
                                                <div class="p-1">
                                                    <div class="memberLevelOption flex items-center px-3 py-3 cursor-pointer hover:bg-indigo-50 transition-colors duration-150 rounded-md" data-value="normal">
                                                        <div class="flex-shrink-0 mr-3">
                                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-gray-600">O</span>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <span class="block text-sm font-medium text-gray-900">Ordinary member</span>
                                                            <span class="block text-xs text-gray-500">Basic membership benefits</span>
                                                        </div>
                                                        <div class="flex-shrink-0 ml-2">
                                                            <span class="w-2 h-2 inline-block rounded-full {{ old('member_level', 'normal') == 'normal' ? 'bg-indigo-600' : 'bg-gray-200' }}"></span>
                                                        </div>
                                                    </div>
                                                    <div class="memberLevelOption flex items-center px-3 py-3 cursor-pointer hover:bg-indigo-50 transition-colors duration-150 rounded-md" data-value="silver">
                                                        <div class="flex-shrink-0 mr-3">
                                                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-gray-600">S</span>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <span class="block text-sm font-medium text-gray-900">Silver</span>
                                                            <span class="block text-xs text-gray-500">Premium benefits & discounts</span>
                                                        </div>
                                                        <div class="flex-shrink-0 ml-2">
                                                            <span class="w-2 h-2 inline-block rounded-full {{ old('member_level') == 'silver' ? 'bg-indigo-600' : 'bg-gray-200' }}"></span>
                                                        </div>
                                                    </div>
                                                    <div class="memberLevelOption flex items-center px-3 py-3 cursor-pointer hover:bg-indigo-50 transition-colors duration-150 rounded-md" data-value="gold">
                                                        <div class="flex-shrink-0 mr-3">
                                                            <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-yellow-600">G</span>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <span class="block text-sm font-medium text-gray-900">Golden member</span>
                                                            <span class="block text-xs text-gray-500">VIP service & special offers</span>
                                                        </div>
                                                        <div class="flex-shrink-0 ml-2">
                                                            <span class="w-2 h-2 inline-block rounded-full {{ old('member_level') == 'gold' ? 'bg-indigo-600' : 'bg-gray-200' }}"></span>
                                                        </div>
                                                    </div>
                                                    <div class="memberLevelOption flex items-center px-3 py-3 cursor-pointer hover:bg-indigo-50 transition-colors duration-150 rounded-md" data-value="platinum">
                                                        <div class="flex-shrink-0 mr-3">
                                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-blue-600">P</span>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <span class="block text-sm font-medium text-gray-900">Platinum</span>
                                                            <span class="block text-xs text-gray-500">Exclusive access & premium care</span>
                                                        </div>
                                                        <div class="flex-shrink-0 ml-2">
                                                            <span class="w-2 h-2 inline-block rounded-full {{ old('member_level') == 'platinum' ? 'bg-indigo-600' : 'bg-gray-200' }}"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                    @error('member_level')
                                        <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            </div>

                            <!-- 提示卡片 -->
                            <div class="bg-indigo-50 rounded-xl p-5 border border-indigo-100     animation-delay-600">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-indigo-800">Tips</h3>
                                        <div class="mt-2 text-sm text-indigo-700">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li>Required fields are marked with <span class="text-red-500">*</span></li>
                                                <li>Birthday will be auto-extracted from IC number</li>
                                                <li>Password will be auto-generated if email is provided</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
            </div>

                    <!-- 表单底部按钮区域 -->
                    <div class="mt-8 pt-5 border-t border-gray-200">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('customers.index') }}" class="group relative inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-all duration-200">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </span>
                                <span class="pl-10">Cancel</span>
                            </a>
                            <button type="submit" id="submitBtn" class="group relative inline-flex items-center px-8 py-3 border border-transparent text-sm font-bold rounded-md text-black bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md transition-all duration-200">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="w-5 h-5  group-hover:text-white transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </span>
                                <span class="pl-10 text-black font-medium">Save Customer</span>
                </button>
                        </div>
                    </div>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- Toast容器 -->
<div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col items-end"></div>
@endsection

@push('scripts')
<script>
(function() {
    // 初始化标记，用于确保只执行一次
    let isInitialized = false;
    
    // 主初始化函数，处理各种交互
    function initializeCustomerForm() {
        // 防止重复初始化
        if (isInitialized) return;
        console.log('Initializing enhanced customer form UI');
        
        // 获取DOM元素
    const icNumberInput = document.getElementById('ic_number');
    const birthdayInput = document.getElementById('birthday');
        const passwordInput = document.getElementById('customer_password');
        const generateBtn = document.getElementById('generatePasswordBtn');
        const passwordStrength = document.getElementById('passwordStrength');
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('strengthText');
        const memberLevelButton = document.getElementById('memberLevelButton');
        const memberLevelDropdown = document.getElementById('memberLevelDropdown');
        const memberLevelOptions = document.querySelectorAll('.memberLevelOption');
        const memberLevelSelected = document.getElementById('memberLevelSelected');
        const memberLevelSelect = document.getElementById('member_level');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('customerForm');
        const memberLevelContainer = document.getElementById('memberLevelContainer');
        
        // 低性能设备检测 - 如果设备可能是低性能的，禁用一些动画
        const isLowPerfDevice = window.navigator.hardwareConcurrency <= 2 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        if (isLowPerfDevice) {
            console.log('Low performance device detected, optimizing animations');
            document.querySelectorAll('.animate-shimmer, .animate-float, .animate-pulse-slow').forEach(el => {
                el.classList.add('low-motion');
            });
        }
        
        // 处理尊重用户减少动画的偏好
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            console.log('Respecting user preference for reduced motion');
            document.body.classList.add('low-motion');
        }
        
        // IC号码自动提取生日功能
        if (icNumberInput && birthdayInput) {
    icNumberInput.addEventListener('input', function() {
        const icNumber = this.value.replace(/[^0-9]/g, '');
        
        if (icNumber.length === 12) {
            const year = icNumber.substring(0, 2);
            const month = icNumber.substring(2, 4);
            const day = icNumber.substring(4, 6);
            
                    // 判断年份的世纪
            let fullYear;
            const currentYear = new Date().getFullYear() % 100;
            if (parseInt(year) > currentYear) {
                fullYear = '19' + year;
            } else {
                fullYear = '20' + year;
            }
            
                    // 验证月份和日期
            const monthNum = parseInt(month);
            const dayNum = parseInt(day);
            
            if (monthNum >= 1 && monthNum <= 12 && dayNum >= 1 && dayNum <= 31) {
                const formattedMonth = month.padStart(2, '0');
                const formattedDay = day.padStart(2, '0');
                const birthDate = `${fullYear}-${formattedMonth}-${formattedDay}`;
                
                birthdayInput.value = birthDate;
                        // 添加高亮效果
                        birthdayInput.classList.add('highlight-animation');
                        setTimeout(() => birthdayInput.classList.remove('highlight-animation'), 1500);
                        
                        showToast('Birthday extracted from IC number', 'success');
                
                        // 触发change事件
                const event = new Event('change', { bubbles: true });
                birthdayInput.dispatchEvent(event);
                    }
                }
            });
        }
        
        // 密码生成和强度检测
        if (passwordInput && generateBtn && passwordStrength) {
    // 生成随机密码
    generateBtn.addEventListener('click', function() {
        const password = generateRandomPassword();
        passwordInput.value = password;
        checkPasswordStrength(password);
                
                // 添加高亮动画
                passwordInput.classList.add('highlight-animation');
                setTimeout(() => passwordInput.classList.remove('highlight-animation'), 1500);
                
                showToast('New password generated', 'success');
    });

    // 监听密码输入
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        if (password) {
            checkPasswordStrength(password);
        } else {
            passwordStrength.classList.add('hidden');
        }
    });

    // 初始检查密码强度
    if (passwordInput.value) {
        checkPasswordStrength(passwordInput.value);
    }
        }
        
        // 自定义会员等级下拉框
        if (memberLevelButton && memberLevelDropdown && memberLevelOptions.length > 0) {
            // 切换下拉菜单
            memberLevelButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (memberLevelDropdown.classList.contains('hidden')) {
                    // 显示下拉菜单并添加动画
                    memberLevelDropdown.classList.remove('hidden');
                    setTimeout(() => {
                        memberLevelDropdown.classList.remove('opacity-0', 'scale-95');
                        memberLevelDropdown.classList.add('opacity-100', 'scale-100');
                    }, 10);
                    
                    // 当点击外部区域时关闭下拉菜单
                    document.addEventListener('click', closeDropdownOnClickOutside);
                } else {
                    closeDropdown();
                }
            });
            
            // 处理选项选择
            memberLevelOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const value = this.dataset.value;
                    const text = this.querySelector('.text-gray-900').textContent.trim();
                    
                    // 更新隐藏的select元素
                    memberLevelSelect.value = value;
                    
                    // 更新显示的文本
                    memberLevelSelected.textContent = text;
                    
                    // 更新视觉指示器
                    memberLevelOptions.forEach(opt => {
                        const indicator = opt.querySelector('.rounded-full');
                        indicator.classList.remove('bg-indigo-600');
                        indicator.classList.add('bg-gray-200');
                    });
                    const selectedIndicator = this.querySelector('.rounded-full');
                    selectedIndicator.classList.remove('bg-gray-200');
                    selectedIndicator.classList.add('bg-indigo-600');
                    
                    // 关闭下拉菜单
                    closeDropdown();
                    
                    // 触发change事件
                    const event = new Event('change', { bubbles: true });
                    memberLevelSelect.dispatchEvent(event);
                    
                    // 显示反馈信息
                    showToast(`Membership level set to ${text}`, 'success');
                });
            });
            
            // 监听点击外部区域，关闭下拉菜单
            function closeDropdownOnClickOutside(e) {
                if (memberLevelContainer && !memberLevelContainer.contains(e.target)) {
                    closeDropdown();
                    document.removeEventListener('click', closeDropdownOnClickOutside);
                }
            }
            
            // 关闭下拉菜单
            function closeDropdown() {
                memberLevelDropdown.classList.add('opacity-0', 'scale-95');
                memberLevelDropdown.classList.remove('opacity-100', 'scale-100');
                setTimeout(() => {
                    memberLevelDropdown.classList.add('hidden');
                }, 150);
            }
        }
        
        // 表单提交 - 显示加载状态
        if (form && submitBtn) {
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75');
                
                // 替换按钮内容为加载动画
                submitBtn.innerHTML = `
                    <div class="flex items-center">
                        <svg class="animate-spinner -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Processing...</span>
                    </div>
                `;
                
                // 如果表单提交时间过长，提示用户
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        showToast('Saving customer data, please wait...', 'info', 10000);
                    }
                }, 3000);
                
                return true;
            });
        }
        
        // 标记已初始化
        isInitialized = true;
    }
    
    // 辅助函数

    // 生成随机密码
    function generateRandomPassword(length = 12) {
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const numbers = '0123456789';
        const specialChars = '!@#$%^&*()_-+=';
        
        const all = lowercase + uppercase + numbers + specialChars;
        
        // 确保至少包含各种类型的字符
        let password = '';
        password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
        password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
        password += numbers.charAt(Math.floor(Math.random() * numbers.length));
        password += specialChars.charAt(Math.floor(Math.random() * specialChars.length));
        
        // 填充剩余字符
        for (let i = password.length; i < length; i++) {
            password += all.charAt(Math.floor(Math.random() * all.length));
        }
        
        // 打乱密码字符顺序
        return password.split('').sort(() => 0.5 - Math.random()).join('');
    }

    // 检查密码强度
    function checkPasswordStrength(password) {
        const passwordStrength = document.getElementById('passwordStrength');
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('strengthText');
        
        if (!passwordStrength || !strengthBar || !strengthText) return;
        
        // 显示强度指示器
        passwordStrength.classList.remove('hidden');
        
        // 计算分数
        let score = 0;
        
        // 长度检查
        if (password.length >= 8) score += 1;
        if (password.length >= 12) score += 1;
        
        // 复杂性检查
        if (/[a-z]/.test(password)) score += 1;
        if (/[A-Z]/.test(password)) score += 1;
        if (/[0-9]/.test(password)) score += 1;
        if (/[^a-zA-Z0-9]/.test(password)) score += 1;
        
        // 更新强度条
        const percentage = (score / 6) * 100;
        strengthBar.style.width = `${percentage}%`;
        
        // 设置颜色和文本
        if (score < 3) {
            strengthBar.className = 'h-full bg-red-500 transition-all duration-300';
            strengthText.innerText = 'Weak';
            strengthText.className = 'text-xs font-medium text-red-500';
        } else if (score < 5) {
            strengthBar.className = 'h-full bg-yellow-500 transition-all duration-300';
            strengthText.innerText = 'Medium';
            strengthText.className = 'text-xs font-medium text-yellow-500';
        } else {
            strengthBar.className = 'h-full bg-green-500 transition-all duration-300';
            strengthText.innerText = 'Strong';
            strengthText.className = 'text-xs font-medium text-green-500';
        }
    }
    
    // 显示Toast通知
    function showToast(message, type = 'info', duration = 3000) {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) return;
        
        // 创建Toast元素
        const toast = document.createElement('div');
        toast.className = `flex items-center p-4 mb-3 max-w-md rounded-lg shadow-lg transform transition-all duration-300 ease-out translate-y-2 opacity-0 ${
            type === 'success' 
                ? 'bg-green-50 text-green-800 border-l-4 border-green-500' 
                : type === 'error' 
                    ? 'bg-red-50 text-red-800 border-l-4 border-red-500'
                    : 'bg-blue-50 text-blue-800 border-l-4 border-blue-500'
        }`;
        
        // 根据类型设置图标
        const icon = document.createElement('div');
        icon.className = 'flex-shrink-0';
        icon.innerHTML = type === 'success' 
            ? `<svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>`
            : type === 'error'
                ? `<svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>`
                : `<svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>`;
        
        // 内容部分
        const content = document.createElement('div');
        content.className = 'ml-3 text-sm font-medium';
        content.textContent = message;
        
        // 关闭按钮
        const closeButton = document.createElement('button');
        closeButton.className = 'ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 inline-flex h-8 w-8 text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-all duration-200';
        closeButton.innerHTML = `<span class="sr-only">Close</span><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>`;
        closeButton.addEventListener('click', () => {
            removeToast(toast);
        });
        
        // 组装Toast
        toast.appendChild(icon);
        toast.appendChild(content);
        toast.appendChild(closeButton);
        toastContainer.appendChild(toast);
        
        // 显示动画
        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 10);
        
        // 设置自动移除
        const timeout = setTimeout(() => {
            removeToast(toast);
        }, duration);
        
        function removeToast(toastElement) {
            toastElement.classList.remove('translate-y-0', 'opacity-100');
            toastElement.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                toastElement.remove();
            }, 300);
        }
        
        // 如果手动关闭，清除定时器
        closeButton.addEventListener('click', () => {
            clearTimeout(timeout);
        });
        
        return toast;
    }
    
    // 在多种事件下初始化，以支持Turbolinks和常规页面加载
    document.addEventListener('DOMContentLoaded', initializeCustomerForm);
    document.addEventListener('turbolinks:load', initializeCustomerForm);
    
    // 如果页面已加载完成，立即初始化
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initializeCustomerForm();
    }
})();
</script>
@endpush 
