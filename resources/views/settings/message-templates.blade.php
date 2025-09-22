@extends('layouts.settings')

@section('title', 'Message Templates')

@section('breadcrumb')
<nav class="text-sm">
    <ol class="list-none p-0 inline-flex">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('settings.index') }}" class="text-gray-500 hover:text-gray-700">Settings</a>
            <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
        </li>
        <li>
            <span class="text-gray-700">Message Templates</span>
        </li>
    </ol>
</nav>
@endsection

@section('settings-content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- 页面标题 -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Message Templates</h1>
            <p class="mt-1 text-sm text-gray-500">Customize email notification templates used by the system</p>
        </div>

        <div class="flex justify-between items-center mb-6">
            <div></div>
            <a href="{{ route('settings.message-templates.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create New Template
            </a>
        </div>

        @if (session('success'))
        <div class="mb-6 flex p-4 bg-green-50 rounded-lg transition-opacity duration-300" role="alert" id="success-alert">
            <svg class="flex-shrink-0 w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                <span class="sr-only">Close</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-6 flex p-4 bg-red-50 rounded-lg transition-opacity duration-300" role="alert" id="error-alert">
            <svg class="flex-shrink-0 w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                <span class="sr-only">Close</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        @endif

        <!-- 使用指南 -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Usage Guide</h3>
            <p class="text-sm text-gray-500 mb-2">Email templates are used to customize various notifications sent by the system. You can create templates for specific suppliers or general templates for all suppliers.</p>
            <ul class="list-disc pl-5 text-sm text-gray-500 space-y-1">
                <li>Templates marked as "Default" will be used as the default template for that type</li>
                <li>Supplier-specific templates have higher priority than general templates</li>
                <li>Only active templates will be used for sending notifications</li>
            </ul>
        </div>

        @if(count($emailTemplates) == 0)
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="mb-4">
                <svg class="mx-auto h-16 w-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h4 class="text-xl font-medium text-gray-900 mb-2">No Email Templates Found</h4>
            <p class="text-gray-500 mb-4">You can create new email templates to customize system notifications.</p>
            <a href="{{ route('settings.message-templates.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create First Template
            </a>
        </div>
        @else
        <!-- 全局模板部分 -->
        <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Global Templates</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php $hasGlobalTemplates = false; @endphp
                @foreach($emailTemplates as $key => $templates)
                    @if($key === 'global')
                        @foreach($templates as $template)
                            @php $hasGlobalTemplates = true; @endphp
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden border border-gray-200">
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="text-md font-medium text-indigo-600 truncate" title="{{ $template->name }}">
                                            {{ $template->name }}
                                        </h3>
                                        <div class="flex space-x-1">
                                            @if($template->is_default)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Default
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 mb-3 h-10 overflow-hidden">
                                        {{ $template->description ?: 'No description' }}
                                    </p>
                                    <div class="mb-2">
                                        <span class="text-xs font-medium text-gray-500">Subject:</span>
                                        <p class="text-sm truncate" title="{{ $template->subject }}">{{ $template->subject }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <span class="text-xs font-medium text-gray-500">Status:</span>
                                        @if($template->status == 'active')
                                        <span class="text-sm text-green-600">Active</span>
                                        @else
                                        <span class="text-sm text-red-600">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                                    <a href="{{ route('settings.message-templates.edit', ['type' => $template->channel, 'id' => $template->id]) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-0.5 mr-1 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endforeach
                
                @if(!$hasGlobalTemplates)
                <div class="col-span-full bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center text-gray-500">
                        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>No global templates found.</span>
                        <a href="{{ route('settings.message-templates.create') }}" class="ml-2 text-indigo-600 hover:text-indigo-900">Create one</a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- 供应商特定模板部分 -->
        <div>
            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Supplier-Specific Templates</h2>
            
            @php 
                $hasSupplierTemplates = false;
                $supplierTemplates = isset($emailTemplates['custom']) ? $emailTemplates['custom'] : collect();
                
                if (count($supplierTemplates) > 0) {
                    $supplierTemplates = $supplierTemplates->groupBy(function($item) {
                        return $item->suppliers->count() > 0 ? $item->suppliers->first()->id : 'unknown';
                    });
                }
            @endphp

            @if($supplierTemplates->count() > 0)
                @php $hasSupplierTemplates = true; @endphp
                @foreach($supplierTemplates as $supplierId => $templates)
                    @php 
                        $supplierName = 'Unknown Supplier';
                        foreach($templates as $template) {
                            if($template->suppliers->count() > 0) {
                                $supplierName = $template->suppliers->first()->name;
                                break;
                            }
                        }
                    @endphp
                    
                    <div class="mb-6">
                        <h3 class="text-md font-medium text-gray-700 mb-3 pl-3 border-l-4 border-indigo-500">
                            <svg class="inline-block h-5 w-5 mr-1 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            {{ $supplierName }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($templates as $template)
                            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden border border-gray-200 border-l-4 border-l-green-500">
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="text-md font-medium text-indigo-600 truncate" title="{{ $template->name }}">
                                            {{ $template->name }}
                                        </h3>
                                        <div class="flex space-x-1">
                                            @if($template->is_default)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Default
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 mb-3 h-10 overflow-hidden">
                                        {{ $template->description ?: 'No description' }}
                                    </p>
                                    <div class="mb-2">
                                        <span class="text-xs font-medium text-gray-500">Subject:</span>
                                        <p class="text-sm truncate" title="{{ $template->subject }}">{{ $template->subject }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <span class="text-xs font-medium text-gray-500">Status:</span>
                                        @if($template->status == 'active')
                                        <span class="text-sm text-green-600">Active</span>
                                        @else
                                        <span class="text-sm text-red-600">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                                    <a href="{{ route('settings.message-templates.edit', ['type' => $template->channel, 'id' => $template->id]) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-0.5 mr-1 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif

            @if(!$hasSupplierTemplates)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center text-gray-500">
                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>No supplier-specific templates found.</span>
                    <a href="{{ route('settings.message-templates.create') }}" class="ml-2 text-indigo-600 hover:text-indigo-900">Create one</a>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection 