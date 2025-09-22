@extends('layouts.settings')

@section('title', '通知设置')

@section('breadcrumb')
    <nav class="text-sm">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">控制台</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li>
                <span class="text-gray-700">通知设置</span>
            </li>
        </ol>
    </nav>
@endsection

@section('settings-content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- 页面标题 -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">通知设置</h1>
                <p class="mt-1 text-sm text-gray-500">管理系统通知接收人及通知方式</p>
            </div>
            <a href="{{ route('notification-history') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                查看通知历史
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 flex p-4 bg-green-50 rounded-lg" role="alert" id="success-alert">
                <svg class="flex-shrink-0 w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                    <span class="sr-only">关闭</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 flex p-4 bg-red-50 rounded-lg" role="alert" id="error-alert">
                <svg class="flex-shrink-0 w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                    <span class="sr-only">关闭</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">电子邮件通知</h2>
                    <div>
                                    <button type="button" 
                                            id="emailToggle"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $emailEnabled ? 'bg-blue-600' : 'bg-gray-200' }}" 
                                            role="switch" 
                                            aria-checked="{{ $emailEnabled ? 'true' : 'false' }}"
                                            data-url="{{ route('settings.notifications.toggle-email') }}">
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $emailEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </div>
                            </div>
                <p class="mt-2 text-sm text-gray-500">启用后，系统将通过邮件发送通知给指定的接收人</p>
            </div>

                <form method="POST" action="{{ route('settings.notifications.update') }}" id="notificationForm">
                    @csrf
                    @method('PUT')

                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-base font-medium text-gray-700">选择通知接收人</h3>
                    <p class="mt-1 text-sm text-gray-500">为每种类型的通知指定接收人，您可以为不同的通知选择不同的接收人</p>
                </div>

                <div class="px-6 py-4">
                    <div class="space-y-6">
                                @foreach ($notificationTypes as $type => $label)
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                                        <div class="p-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    @switch($type)
                                                        @case('product_created')
                                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                                </svg>
                                                            </div>
                                                            @break
                                                        @case('quality_inspection_created')
                                                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                </svg>
                                                            </div>
                                                            @break
                                                        @case('inventory_alert')
                                                            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                                                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                                </svg>
                                                            </div>
                                                            @break
                                                        @case('low_stock_removal')
                                                            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                                                                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                                                </svg>
                                                            </div>
                                                            @break
                                                        @case('purchase_created')
                                                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                                </svg>
                                                            </div>
                                                            @break
                                                        @case('payment_status_changed')
                                                            <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                                                <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                                </svg>
                                                            </div>
                                                            @break
                                                        @default
                                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                                </svg>
                                                            </div>
                                                    @endswitch
                                                </div>
                                                <div class="ml-3">
                                                <h3 class="text-base font-medium text-gray-900">{{ $label }}</h3>
                                                </div>
                                            </div>

                                        <div>
                                            <button type="button" class="text-blue-600 hover:text-blue-800 text-sm font-medium focus:outline-none toggle-recipients" data-type="{{ $type }}">
                                                选择接收人
                                                <svg class="inline-block ml-1 w-4 h-4 toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="recipient-selector hidden" id="selector-{{ $type }}">
                                        <div class="border border-gray-300 rounded-md shadow-sm overflow-y-auto max-h-60">
                                            <div class="p-2 border-b border-gray-200 bg-gray-50 sticky top-0">
                                                <input type="text" 
                                                    class="recipient-search w-full p-2 border border-gray-300 rounded-md" 
                                                    placeholder="搜索用户..." 
                                                        data-type="{{ $type }}">
                                            </div>
                                            <div class="user-list p-2" data-type="{{ $type }}">
                                                    @foreach ($users as $user)
                                                    <label class="user-item flex items-center p-2 hover:bg-gray-50 rounded-md cursor-pointer mb-1">
                                                        <input type="checkbox" 
                                                            name="receivers[{{ $type }}][]" 
                                                            value="{{ $user->email }}" 
                                                            class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                            {{ in_array($user->email, $receivers[$type] ?? []) ? 'checked' : '' }}>
                                                        <div class="ml-3 flex items-center">
                                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <span class="text-blue-800 font-medium">{{ substr($user->name, 0, 1) }}</span>
                                                            </div>
                                                            <div class="ml-2">
                                                                <p class="text-sm font-medium text-gray-700">{{ $user->name }}</p>
                                                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                                            </div>
                                                        </div>
                                                    </label>
                                                    @endforeach
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2">
                                            <div class="flex space-x-2">
                                                <button type="button" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 select-all-btn" data-type="{{ $type }}">
                                                    全选
                                                </button>
                                                <button type="button" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 deselect-all-btn" data-type="{{ $type }}">
                                                    清空
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 selected-recipients" id="selected-{{ $type }}">
                                        @if(isset($receivers[$type]) && count($receivers[$type]) > 0)
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium">已选择 {{ count($receivers[$type]) }} 人：</span> 
                                                <span class="recipient-summary">
                                                    @foreach($users as $user)
                                                        @if(in_array($user->email, $receivers[$type] ?? []))
                                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium mr-1 px-2 py-0.5 rounded">{{ $user->name }}</span>
                                                        @endif
                                @endforeach
                                                </span>
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-500 italic">未选择接收人</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                            </div>

                <div class="px-6 py-4 bg-gray-50 text-right">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                        保存设置
                                </button>
                            </div>
            </form>
                        </div>
        
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">通知相关说明</h2>
                    <div class="prose max-w-none">
                        <p>系统会根据您的设置，向指定的接收人发送相应的通知。请确保接收人的邮箱地址正确无误。</p>
                        <h3>通知类型说明</h3>
                        <ul>
                            <li><strong>新商品创建通知</strong> - 当系统中创建新商品时发送通知</li>
                            <li><strong>质检记录创建通知</strong> - 当新的质检记录被创建时发送通知</li>
                            <li><strong>采购单创建通知</strong> - 当新的采购单被创建时发送通知</li>
                            <li><strong>采购单状态更新通知</strong> - 当采购单状态发生变化时发送通知</li>
                            <li><strong>库存警告通知</strong> - 当商品库存低于预警阈值时发送通知</li>
                            <li><strong>低库存产品移除通知</strong> - 当低库存产品从首页自动移除时发送通知</li>
                            <li><strong>付款状态更新通知</strong> - 当付款状态发生变化时发送通知</li>
                            <li><strong>系统警告通知</strong> - 系统发生异常情况时发送通知</li>
                            <li><strong>自动采购通知</strong> - 系统自动生成采购单时发送通知</li>
                        </ul>
                        <p class="text-sm text-gray-500 mt-4">
                            提示：您可以在<a href="{{ route('settings.message-templates') }}" class="text-blue-600 hover:text-blue-800">消息模板</a>页面自定义各类通知的内容和格式。
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">历史消息记录</h2>
                    <p class="mt-1 text-sm text-gray-500">查看系统发送的历史通知消息</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">发送时间</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">通知类型</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">接收人</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">主题</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($notificationHistory ?? [] as $notification)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $notification->created_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $notificationTypes[$notification->type] ?? $notification->type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $notification->recipient }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $notification->subject }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($notification->status === 'sent')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                已发送
                                            </span>
                                        @elseif($notification->status === 'failed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                发送失败
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $notification->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button type="button" class="text-blue-600 hover:text-blue-900 view-notification" data-id="{{ $notification->id }}">
                                            查看详情
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        暂无历史消息记录
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if(isset($notificationHistory) && $notificationHistory->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $notificationHistory->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* 用户列表样式 */
    .user-item:hover {
        background-color: #f9fafb;
    }
    
    /* 自定义复选框样式 */
    .form-checkbox {
        appearance: none;
        -webkit-appearance: none;
        padding: 0;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
        display: inline-block;
        vertical-align: middle;
        background-origin: border-box;
        user-select: none;
        height: 1rem;
        width: 1rem;
        color: #3b82f6;
        background-color: #fff;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
    }
    
    .form-checkbox:checked {
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
        background-color: currentColor;
        border-color: transparent;
        background-position: center;
        background-repeat: no-repeat;
        background-size: 100% 100%;
    }
    
    .form-checkbox:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
    }
    
    /* 搜索框样式 */
    .recipient-search {
        background-color: white;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
    }
    
    .recipient-search:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.3);
    }
    
    /* 选中标签样式 */
    .selected-recipients .recipient-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        margin-top: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // 通知设置状态管理
    if (typeof window.NotificationManager === 'undefined') {
        window.NotificationManager = {
            // 存储状态
            state: {
                recipientSelectors: {},
                initialized: false
            },
            
            // 初始化
            init: function() {
                if (this.state.initialized) return;
                
                this.setupRecipientToggles();
                this.setupRecipientSearch();
                this.setupSelectButtons();
                this.setupToggleSwitches();
                this.setupAlertDismissal();
                this.updateAllRecipientDisplays();
                
                // 标记为已初始化
                this.state.initialized = true;
                console.log('NotificationManager initialized');
            },
            
            // 设置接收人选择器切换
            setupRecipientToggles: function() {
                const toggles = document.querySelectorAll('.toggle-recipients');
                if (!toggles.length) return;
                
                toggles.forEach(toggle => {
                    // 移除已存在的事件监听器以防止重复
                    toggle.removeEventListener('click', this.handleToggleClick);
                    // 添加新的事件监听器
                    toggle.addEventListener('click', this.handleToggleClick);
                });
            },
            
            // 处理选择器切换点击
            handleToggleClick: function() {
                const type = this.dataset.type;
                const selector = document.getElementById(`selector-${type}`);
                if (!selector) return;
                
                // 先隐藏所有选择器
                document.querySelectorAll('.recipient-selector').forEach(el => {
                    if (el.id !== `selector-${type}`) {
                        el.classList.add('hidden');
                        const otherToggleIcon = document.querySelector(`.toggle-recipients[data-type="${el.id.replace('selector-', '')}"] .toggle-icon`);
                        if (otherToggleIcon) otherToggleIcon.classList.remove('rotate-180');
                    }
                });
                
                // 再切换当前选择器
                selector.classList.toggle('hidden');
                
                // 更新箭头图标
                const toggleIcon = this.querySelector('.toggle-icon');
                if (toggleIcon) toggleIcon.classList.toggle('rotate-180');
            },
            
            // 设置接收人搜索功能
            setupRecipientSearch: function() {
                const searchInputs = document.querySelectorAll('.recipient-search');
                if (!searchInputs.length) return;
                
                searchInputs.forEach(search => {
                    // 移除已存在的事件监听器以防止重复
                    search.removeEventListener('input', this.handleSearchInput);
                    // 添加新的事件监听器
                    search.addEventListener('input', this.handleSearchInput);
                });
            },
            
            // 处理搜索输入
            handleSearchInput: function() {
                const type = this.dataset.type;
                const query = this.value.toLowerCase();
                const userList = document.querySelector(`.user-list[data-type="${type}"]`);
                
                if (!userList) return;
                
                const userItems = userList.querySelectorAll('.user-item');
                
                userItems.forEach(item => {
                    const nameElement = item.querySelector('.text-gray-700');
                    const emailElement = item.querySelector('.text-gray-500');
                    
                    if (!nameElement || !emailElement) return;
                    
                    const name = nameElement.textContent.toLowerCase();
                    const email = emailElement.textContent.toLowerCase();
                    
                    if (name.includes(query) || email.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            },
            
            // 设置全选/清空按钮
            setupSelectButtons: function() {
                // 全选按钮
                document.querySelectorAll('.select-all-btn').forEach(btn => {
                    btn.removeEventListener('click', this.handleSelectAll);
                    btn.addEventListener('click', this.handleSelectAll);
                });
                
                // 清空按钮
                document.querySelectorAll('.deselect-all-btn').forEach(btn => {
                    btn.removeEventListener('click', this.handleDeselectAll);
                    btn.addEventListener('click', this.handleDeselectAll);
                });
                
                // 复选框更改时更新显示
                document.querySelectorAll('input[type="checkbox"][name^="receivers"]').forEach(checkbox => {
                    checkbox.removeEventListener('change', this.handleCheckboxChange);
                    checkbox.addEventListener('change', this.handleCheckboxChange);
                });
            },
            
            // 处理全选按钮
            handleSelectAll: function() {
                const type = this.dataset.type;
                const checkboxes = document.querySelectorAll(`input[name="receivers[${type}][]"]`);
                
                checkboxes.forEach(checkbox => {
                    if (checkbox.closest('.user-item') && !checkbox.closest('.user-item').style.display) {
                        checkbox.checked = true;
                    }
                });
                
                NotificationManager.updateRecipientDisplay(type);
            },
            
            // 处理清空按钮
            handleDeselectAll: function() {
                const type = this.dataset.type;
                const checkboxes = document.querySelectorAll(`input[name="receivers[${type}][]"]`);
                
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                NotificationManager.updateRecipientDisplay(type);
            },
            
            // 处理复选框更改
            handleCheckboxChange: function() {
                const nameAttr = this.getAttribute('name');
                const match = nameAttr.match(/receivers\[(.*?)\]/);
                if (match && match[1]) {
                    NotificationManager.updateRecipientDisplay(match[1]);
                }
            },
            
            // 更新接收人显示
            updateRecipientDisplay: function(type) {
                const checkboxes = document.querySelectorAll(`input[name="receivers[${type}][]"]:checked`);
                const container = document.getElementById(`selected-${type}`);
                
                if (!container) return;
                
                if (checkboxes.length > 0) {
                    const names = Array.from(checkboxes).map(checkbox => {
                        const userItem = checkbox.closest('.user-item');
                        if (!userItem) return '';
                        
                        const nameElement = userItem.querySelector('.text-gray-700');
                        if (!nameElement) return '';
                        
                        const name = nameElement.textContent;
                        return `<span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium mr-1 px-2 py-0.5 rounded">${name}</span>`;
                    }).filter(name => name !== '');
                    
                    container.innerHTML = `
                        <div class="text-sm text-gray-600">
                            <span class="font-medium">Selected ${checkboxes.length} recipient(s):</span>
                            <span class="recipient-summary">
                                ${names.join('')}
                            </span>
                        </div>
                    `;
                } else {
                    container.innerHTML = '<div class="text-sm text-gray-500 italic">No recipients selected</div>';
                }
            },
            
            // 更新所有接收人显示
            updateAllRecipientDisplays: function() {
                const notificationTypes = document.querySelectorAll('.toggle-recipients');
                notificationTypes.forEach(toggle => {
                    const type = toggle.dataset.type;
                    this.updateRecipientDisplay(type);
                });
            },
            
            // 设置开关状态切换
            setupToggleSwitches: function() {
                const emailToggle = document.getElementById('emailToggle');
                if (!emailToggle) return;
                
                emailToggle.removeEventListener('click', this.handleToggleSwitch);
                emailToggle.addEventListener('click', this.handleToggleSwitch);
            },
            
            // 处理开关状态切换
            handleToggleSwitch: function() {
                const toggle = this;
                const isActive = toggle.getAttribute('aria-checked') === 'true';
                const newState = !isActive;
                
                // 发送请求前禁用按钮
                toggle.disabled = true;
                
                // 确保有CSRF令牌
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    NotificationManager.showNotification('Operation failed: CSRF token not found', 'error');
                    toggle.disabled = false;
                    return;
                }
                
                fetch(toggle.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ enabled: newState })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 更新UI状态
                        toggle.setAttribute('aria-checked', newState ? 'true' : 'false');
                        toggle.classList.toggle('bg-blue-600', newState);
                        toggle.classList.toggle('bg-gray-200', !newState);
                        toggle.querySelector('span').classList.toggle('translate-x-5', newState);
                        toggle.querySelector('span').classList.toggle('translate-x-0', !newState);
                        
                        // 显示成功提示
                        NotificationManager.showNotification('Notification settings updated', 'success');
                    } else {
                        // 显示错误提示
                        throw new Error(data.error || 'Operation failed, please try again');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    NotificationManager.showNotification(error.message || 'Operation failed, please try again', 'error');
                })
                .finally(() => {
                    // 重新启用按钮
                    toggle.disabled = false;
                });
            },
            
            // 显示提示消息
            showNotification: function(message, type = 'success') {
                const notificationContainer = document.createElement('div');
                notificationContainer.className = `fixed top-4 right-4 z-50 flex p-4 rounded-lg ${type === 'success' ? 'bg-green-50' : 'bg-red-50'}`;
                notificationContainer.setAttribute('role', 'alert');
                
                notificationContainer.innerHTML = `
                    <svg class="flex-shrink-0 w-5 h-5 ${type === 'success' ? 'text-green-600' : 'text-red-600'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'}"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium ${type === 'success' ? 'text-green-800' : 'text-red-800'}">${message}</p>
                    </div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 ${type === 'success' ? 'bg-green-50 text-green-500 hover:bg-green-200' : 'bg-red-50 text-red-500 hover:bg-red-200'} rounded-lg focus:ring-2 focus:ring-offset-2 ${type === 'success' ? 'focus:ring-green-400' : 'focus:ring-red-400'} p-1.5 inline-flex h-8 w-8">
                        <span class="sr-only">Close</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                `;
                
                document.body.appendChild(notificationContainer);
                
                // 添加关闭功能
                notificationContainer.querySelector('button').addEventListener('click', () => {
                    notificationContainer.remove();
                });
                
                // 3秒后自动关闭
                setTimeout(() => {
                    if (notificationContainer.parentNode) {
                        notificationContainer.remove();
                    }
                }, 3000);
            },
            
            // 设置自动关闭提示
            setupAlertDismissal: function() {
                const alerts = document.querySelectorAll('#success-alert, #error-alert');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        if (alert && alert.parentNode) {
                            alert.remove();
                        }
                    }, 5000);
                });
            }
        };
    }
    
    // 立即尝试初始化
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(function() {
            NotificationManager.init();
        }, 1);
    } else {
        // 如果文档还没准备好，等待DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            NotificationManager.init();
        });
    }
    
    // 确保在页面完成加载后再次初始化，以防DOMContentLoaded已经触发
    window.addEventListener('load', function() {
        setTimeout(function() {
            NotificationManager.init();
        }, 100);
    });
    
    // 支持Livewire页面导航
    if (typeof window.Livewire !== 'undefined') {
        // Livewire钩子
        document.addEventListener('livewire:navigated', function() {
            setTimeout(function() {
                NotificationManager.init();
            }, 100);
        });
        
        window.Livewire.hook('message.processed', () => {
            setTimeout(function() {
                NotificationManager.init();
            }, 100);
        });
    }
</script>
@endpush 