@extends('layouts.app')

@section('title', 'Notification History')

@section('content')
<div class="py-6 notification-history-container" data-initialized="false">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Debug Info - Only visible in development -->
        @if(config('app.debug'))
            @if(empty($users) || $users->isEmpty())
                <div class="mb-6 bg-yellow-50 p-4 rounded-lg shadow">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>No user data found. This may affect recipient selection functionality.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Page Header -->
        <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Notification History</h1>
                        <p class="mt-1 text-sm text-gray-500">View all system notification messages</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <nav class="text-sm">
                            <ol class="list-none p-0 inline-flex">
                                <li class="flex items-center">
                                    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
                                    <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                        <path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" /></svg>
                                </li>
                                <li>
                                    <span class="text-gray-700">Notification History</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 flex p-4 bg-green-50 rounded-lg shadow" role="alert" id="success-alert">
                <svg class="flex-shrink-0 w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                    <span class="sr-only">Close</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 flex p-4 bg-red-50 rounded-lg shadow" role="alert" id="error-alert">
                <svg class="flex-shrink-0 w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                    <span class="sr-only">Close</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                </button>
            </div>
        @endif

        <!-- Filter Options -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-medium text-gray-900">Filter Options</h2>
                <button type="button" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="toggle-filters">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Toggle Filters
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Notification Type -->
                <div class="relative">
                    <label for="notification_type" class="block text-sm font-medium text-gray-700 mb-2">Notification Type</label>
                    <select id="notification_type" name="notification_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">All Types</option>
                        @foreach($notificationTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="relative">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">All Statuses</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <!-- Recipient Filter -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recipient</label>
                    <div class="relative">
                        <button type="button" class="toggle-recipients w-full inline-flex items-center justify-between px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" data-type="recipient_filter">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <span>Recipient Filter</span>
                            </div>
                            <svg class="toggle-icon h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Recipient Selector Dropdown -->
                        <div id="selector-recipient_filter" class="recipient-selector hidden absolute right-0 mt-2 w-full bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200" aria-hidden="true" style="display: none; visibility: hidden;" data-state="closed">
                            <div class="p-3 border-b border-gray-200">
                                <input type="text" class="recipient-search w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search users..." data-type="recipient_filter">
                            </div>
                            <div class="max-h-60 overflow-y-auto p-3">
                                <div class="user-list" data-type="recipient_filter">

                                    <!-- User items will be loaded here -->
                                    @foreach($users ?? [] as $user)
                                    <label class="user-item flex items-center p-2 hover:bg-gray-50 rounded-md cursor-pointer mb-1">
                                        <input type="checkbox" name="receivers[recipient_filter][]" value="{{ $user->email }}" data-user-id="{{ $user->id }}" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <div class="ml-3 flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-800 font-medium">{{ strtoupper(substr($user->name ?? $user->email, 0, 1)) }}</span>
                                            </div>
                                            <div class="ml-2">
                                                <p class="text-sm font-medium text-gray-700">{{ $user->name ?? 'User' }}</p>
                                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </label>
                                    @endforeach

                                    @if(count($recipients ?? []) > 0)
                                    @foreach($recipients as $email)
                                    @if($email != '0')
                                    @if(!in_array($email, ($users ?? [])->pluck('email')->toArray()))
                                    <label class="user-item flex items-center p-2 hover:bg-gray-50 rounded-md cursor-pointer mb-1">
                                        <input type="checkbox" name="receivers[recipient_filter][]" value="{{ $email }}" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <div class="ml-3 flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-800 font-medium">{{ strtoupper(substr($email, 0, 1)) }}</span>
                                            </div>
                                            <div class="ml-2">
                                                <p class="text-sm font-medium text-gray-700">{{ $email }}</p>
                                                <p class="text-xs text-gray-500">{{ $email }}</p>
                                            </div>
                                        </div>
                                    </label>
                                    @endif
                                    @endif
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="p-3 bg-gray-50 border-t border-gray-200">
                                <div class="flex space-x-2">
                                    <button type="button" class="select-all-btn inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" data-type="recipient_filter">
                                        Select All
                                    </button>
                                    <button type="button" class="deselect-all-btn inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" data-type="recipient_filter">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="selected-recipient_filter" class="mt-2">
                        <div class="text-sm text-gray-500 italic">No recipients selected</div>
                    </div>
                </div>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="from_date" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" id="from_date" name="from_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="to_date" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" id="to_date" name="to_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>

            <!-- Reset Button -->
            <div class="mt-6 text-right">
                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Reset
                </button>
            </div>
        </div>

        <!-- Test Email Form -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">Send Test Email</h2>
                <button type="button" id="toggle-test-email" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Toggle Form
                </button>
            </div>
            <div class="p-6 test-email-container hidden">
                <form id="test-email-form" class="space-y-6">
                    <!-- Recipient Selector - Using Custom Component -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-base font-medium text-gray-900">Test Email Recipient</h3>
                                    </div>
                                </div>

                                <div>
                                    <button type="button" class="text-blue-600 hover:text-blue-800 text-sm font-medium focus:outline-none toggle-recipients" data-type="test_email_recipient">
                                        Select Recipient
                                        <svg class="inline-block ml-1 w-4 h-4 toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="recipient-selector hidden" id="selector-test_email_recipient" aria-hidden="true" style="display: none; visibility: hidden;" data-state="closed">
                                <div class="border border-gray-300 rounded-md shadow-sm overflow-y-auto max-h-60">
                                    <div class="p-2 border-b border-gray-200 bg-gray-50 sticky top-0">
                                        <input type="text" class="recipient-search w-full p-2 border border-gray-300 rounded-md" placeholder="Search users..." data-type="test_email_recipient">
                                    </div>
                                    <div class="user-list p-2" data-type="test_email_recipient">
                                        @foreach($users ?? [] as $user)
                                        <label class="user-item flex items-center p-2 hover:bg-gray-50 rounded-md cursor-pointer mb-1">
                                            <input type="radio" name="recipient" value="{{ $user->email }}" data-user-id="{{ $user->id }}" class="form-radio h-4 w-4 text-blue-600 border-gray-300 rounded-full focus:ring-blue-500 recipient-radio">
                                            <div class="ml-3 flex items-center">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-blue-800 font-medium">{{ strtoupper(substr($user->name ?? $user->email, 0, 1)) }}</span>
                                                </div>
                                                <div class="ml-2">
                                                    <p class="text-sm font-medium text-gray-700">{{ $user->name ?? 'User' }}</p>
                                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                        </label>
                                        @endforeach
                                        
                                        @if(count($recipients ?? []) > 0)
                                            @foreach($recipients as $email)
                                        @if($email != '0')
                                                @if(!in_array($email, ($users ?? [])->pluck('email')->toArray()))
                                                <label class="user-item flex items-center p-2 hover:bg-gray-50 rounded-md cursor-pointer mb-1">
                                                    <input type="radio" name="recipient" value="{{ $email }}" class="form-radio h-4 w-4 text-blue-600 border-gray-300 rounded-full focus:ring-blue-500 recipient-radio">
                                                    <div class="ml-3 flex items-center">
                                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <span class="text-blue-800 font-medium">{{ strtoupper(substr($email, 0, 1)) }}</span>
                                                        </div>
                                                        <div class="ml-2">
                                                            <p class="text-sm font-medium text-gray-700">{{ $email }}</p>
                                                        </div>
                                                    </div>
                                                </label>
                                                @endif
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 selected-recipient" id="selected-test_email_recipient">
                                <div class="text-sm text-gray-500 italic">No recipient selected</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Template Type and Message Template Integration - Beautified Version -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-100 shadow-sm mb-6">
                        <h3 class="text-lg font-medium text-blue-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Message Configuration
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Template Type with enhanced styling -->
                        <div>
                                <label for="template_type" class="block text-sm font-medium text-blue-700 mb-2">Notification Type</label>
                                <div class="relative">
                                    <select name="template_type" id="template_type" class="block w-full pl-10 pr-10 py-2.5 text-base border-blue-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg shadow-sm">
                                @foreach($notificationTypes as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-blue-600">Select the type of notification you want to send</p>
                        </div>
                        
                            <!-- Message Template Selector with enhanced styling -->
                        <div>
                                <label for="message_template_id" class="block text-sm font-medium text-blue-700 mb-2">Message Template</label>
                                <div class="relative">
                                    <select name="message_template_id" id="message_template_id" class="block w-full pl-10 pr-10 py-2.5 text-base border-blue-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg shadow-sm">
                                <option value="">Custom (No Template)</option>
                                <!-- Templates will be loaded dynamically based on selected notification type -->
                            </select>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-blue-600">Choose a template or create a custom message</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" name="subject" id="subject" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" value="Test Email">
                    </div>
                    
                    <!-- Email Content -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700">Email Content</label>
                        <textarea name="content" id="content" rows="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">This is a test email message from the system.</textarea>
                    </div>
                    
                    <!-- Template Variables (will be shown only when a template is selected) -->
                    <div id="template-variables-container" class="hidden">
                        <div class="bg-blue-50 p-3 rounded-md">
                            <h4 class="text-sm font-medium text-blue-800 mb-1">Template Variables</h4>
                            <p class="text-xs text-blue-600 mb-2">These variables will be replaced with test data when the email is sent.</p>
                            <div id="template-variables-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-1.5 text-xs text-blue-700">
                                <!-- Variables will be loaded dynamically -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" id="preview-email-btn" class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Send Test Email
                        </button>
                    </div>
                    
                    <div id="result" class="mt-4 p-4 hidden"></div>
                    
                    <!-- Email Preview Modal -->
                    <div id="preview-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
                        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 animate-fade-in-up">
                            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Email Preview</h3>
                                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none close-preview-modal">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="p-6">
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">Subject</h4>
                                    <p class="text-base text-gray-900 mt-1" id="preview-subject"></p>
                                </div>
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-500">Preview</h4>
                                    <div class="mt-2 p-4 bg-gray-50 rounded-md overflow-auto max-h-96 border border-gray-200">
                                        <div class="prose prose-sm max-w-none" id="preview-content"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
                                <span class="text-xs text-gray-500">Variables will be replaced with test data when the email is sent.</span>
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 close-preview-modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notification History List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table id="notification-table" class="min-w-full divide-y divide-gray-200 table-fixed">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[200px]">Time</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[150px]">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[200px]">Recipient</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider max-w-[300px]">Subject</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[100px]">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[80px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($notificationHistory as $notification)
                        <tr class="notification-row hover:bg-gray-50 transition-colors duration-150" data-type="{{ $notification->type }}" data-status="{{ $notification->status }}" data-recipient="{{ $notification->recipient }}" data-date="{{ $notification->created_at->format('Y-m-d') }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-[150px]">
                                    {{ $notification->created_at->format('Y-m-d H:i:s') }}
                                </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-[150px]">
                                    {{ $notificationTypes[$notification->type] ?? $notification->type }}
                                </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-[200px]">
                                    {{ $notification->recipient }}
                                </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-[300px] overflow-hidden text-ellipsis">
                                    {{ $notification->subject }}
                                </td>
                            <td class="px-6 py-4 whitespace-nowrap max-w-[100px]">
                                    @if($notification->status === 'sent')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Sent
                                        </span>
                                    @elseif($notification->status === 'failed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Failed
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $notification->status }}
                                        </span>
                                    @endif
                                </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm max-w-[80px]">
                                <button type="button" class="text-blue-600 hover:text-blue-900 font-medium view-notification" data-id="{{ $notification->id }}" data-content="{{ htmlspecialchars($notification->content) }}" data-subject="{{ htmlspecialchars($notification->subject) }}" data-recipient="{{ htmlspecialchars($notification->recipient) }}" data-status="{{ $notification->status }}">
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-results-row" class="hidden">
                                <td colspan="6" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center bg-gray-50">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <p class="text-gray-600 font-medium">No notifications match your filters</p>
                                        <p class="text-gray-500 text-sm mt-1">Try adjusting your filter criteria</p>
                                    </div>
                                </td>
                            </tr>
                            <tr id="empty-state-row">
                                <td colspan="6" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center bg-gray-50">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-gray-600 font-medium">No notification records found</p>
                                        <p class="text-gray-500 text-sm mt-1">Notification history will appear here once notifications are sent</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($notificationHistory->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $notificationHistory->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Notification Detail Modal -->
<div id="notification-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    
    <!-- Modal Container -->
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Notification Detail</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none close-modal">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Content -->
                <div class="p-6">
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500">Subject</h4>
                        <p class="text-base text-gray-900 mt-1" id="modal-subject"></p>
                    </div>
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-500">Content</h4>
                        <div class="mt-2 p-4 bg-gray-50 rounded-md overflow-auto max-h-64">
                            <div class="prose prose-sm max-w-none text-gray-800" id="modal-content"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Recipient</h4>
                            <p class="text-base text-gray-900 mt-1" id="modal-recipient"></p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Status</h4>
                            <p class="text-base text-gray-900 mt-1" id="modal-status"></p>
                        </div>
                    </div>
                    <div class="mt-4 hidden" id="modal-error-container">
                        <h4 class="text-sm font-medium text-gray-500">Error</h4>
                        <p class="text-base text-red-600 mt-1" id="modal-error"></p>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 text-right">
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 close-modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.modal-open {
    overflow: hidden;
}

#notification-modal {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease-in-out, visibility 0.2s ease-in-out;
}

#notification-modal.show {
    opacity: 1;
    visibility: visible;
}

#notification-modal .relative {
    transform: scale(0.95);
    transition: transform 0.2s ease-in-out;
}

#notification-modal.show .relative {
    transform: scale(1);
}

    /* 表格样式 */
    #notification-table {
        table-layout: fixed;
        width: 100%;
    }

    #notification-table th,
    #notification-table td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #notification-table td.max-w-\[300px\] {
        max-width: 300px;
        width: 300px;
}

/* Remove other transition styles */
.recipient-selector,
.toggle-recipients,
.hover-card,
.status-badge {
    transition: none !important;
}

button:not(:disabled):hover {
    transform: none !important;
}

.recipient-selector {
    transition: none !important;
    display: none !important; /* 强制确保初始状态为隐藏 */
}

/* 高优先级选择器 */
div.recipient-selector.hidden,
#selector-recipient_filter.hidden,
#selector-test_email_recipient.hidden {
    display: none !important;
}

/* 高优先级选择器 */
div.recipient-selector:not(.hidden),
#selector-recipient_filter:not(.hidden),
#selector-test_email_recipient:not(.hidden) {
    display: block !important;
}

.toggle-recipients {
    transition: none !important;
}

.toggle-icon {
    transition: transform 0.2s ease-in-out;
}

.hover-card {
    transition: none !important;
}

.status-badge {
    transition: none !important;
}

button:not(:disabled):hover {
    transform: none !important;
}

#notification-modal .modal-content {
    position: relative;
    width: 100%;
    max-width: 42rem;
    margin: 2rem auto;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform-origin: center;
    transition: transform 0.3s ease-out;
}

#notification-modal.hidden .modal-content {
    transform: scale(0.95);
}

</style>
@endsection

@push('scripts')
<script>
    // 全局变量，用于跟踪初始化状态
    let notificationHistoryInitialized = false;
    let recipientSelectorsInitialized = false;

        // 主要初始化函数
        function initializeElements() {
            // 如果已经初始化，则不再重复执行
            const container = document.querySelector('.notification-history-container');
            if (container && container.getAttribute('data-initialized') === 'true') {
                // 即使页面已初始化，我们也需要确保接收人选择器在Turbolinks加载时正确初始化
                if (!recipientSelectorsInitialized) {
                    initializeRecipientSelectors();
                    recipientSelectorsInitialized = true;
                }
                return;
            }

            if (container) {
                container.setAttribute('data-initialized', 'true');
            }

            // 初始化日期选择器
            initializeDatePicker();

            // 初始化筛选功能
            initializeFilters();

            // 初始化模态框
            initializeModal();

            // 初始化接收人选择器
            initializeRecipientSelectors();
            recipientSelectorsInitialized = true;

            // 初始化测试邮件表单
            initializeTestEmailForm();

        // 标记为已初始化
        notificationHistoryInitialized = true;
    }

    // 添加多种事件监听，确保在各种环境下都能正确初始化
    document.addEventListener('DOMContentLoaded', function() {
        // 确保所有选择器都处于正确的初始状态
        forceInitializeSelectors();
        // Turbolinks加载时，特别重置接收人选择器初始化标志
        recipientSelectorsInitialized = false;
        initializeElements();
    });
    document.addEventListener('turbolinks:load', function() {
        // 确保所有选择器都处于正确的初始状态
        forceInitializeSelectors();
        // Turbolinks加载时，特别重置接收人选择器初始化标志
        recipientSelectorsInitialized = false;
        initializeElements();
    });

    // 以下是对Livewire环境的额外支持
    if (typeof window.Livewire !== 'undefined') {
        document.addEventListener('livewire:update', function() {
            // 重新初始化组件，但保持全局初始化状态
            const container = document.querySelector('.notification-history-container');
            if (container) container.setAttribute('data-initialized', 'false');
            recipientSelectorsInitialized = false;
            initializeElements();
        });
    }

    // 如果页面已经加载完成，立即初始化
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initializeElements();
        }

        // 初始化测试邮件表单
        function initializeTestEmailForm() {
            const templateTypeSelect = document.getElementById('template_type');
            const messageTemplateSelect = document.getElementById('message_template_id');
            const testEmailForm = document.getElementById('test-email-form');
            const toggleTestEmailBtn = document.getElementById('toggle-test-email');
            const testEmailContainer = document.querySelector('.test-email-container');
            const previewBtn = document.getElementById('preview-email-btn');
            const subjectInput = document.getElementById('subject');
            const contentTextarea = document.getElementById('content');

            // 显示/隐藏测试邮件表单
            if (toggleTestEmailBtn && testEmailContainer) {
                toggleTestEmailBtn.addEventListener('click', function() {
                    testEmailContainer.classList.toggle('hidden');
                });
            }

            // 当通知类型改变时加载模板
            if (templateTypeSelect && messageTemplateSelect) {
                // 初始加载模板
                const initialType = templateTypeSelect.value;
                if (initialType) {
                    loadTemplates(initialType);
                }

                templateTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;
                    loadTemplates(selectedType);
                });

                messageTemplateSelect.addEventListener('change', function() {
                    const selectedTemplateId = this.value;

                    if (selectedTemplateId) {
                        // 获取选中的选项
                        const selectedOption = this.options[this.selectedIndex];

                        // 更新subject和content
                        if (subjectInput && contentTextarea) {
                            subjectInput.value = selectedOption.dataset.subject || '';
                            contentTextarea.value = selectedOption.dataset.content || '';
                        }

                        document.getElementById('template-variables-container').classList.remove('hidden');
                        loadTemplateVariables(selectedTemplateId);
                    } else {
                        document.getElementById('template-variables-container').classList.add('hidden');

                        // 重置为默认值
                        if (subjectInput && contentTextarea) {
                            subjectInput.value = 'Test Email';
                            contentTextarea.value = 'This is a test email message from the system.';
                        }
                    }
                });
            }

            // 提交测试邮件表单
            if (testEmailForm) {
                testEmailForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    sendTestEmail();
                });
            }

            // 预览按钮
            if (previewBtn) {
                previewBtn.addEventListener('click', function() {
                    previewEmail();
                });
            }

            // 关闭预览模态框
            document.querySelectorAll('.close-preview-modal').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('preview-modal').classList.add('hidden');
                });
            });
        }

        // 初始化日期选择器
        function initializeDatePicker() {
            const dateInput = document.getElementById('date_range');
            if (!dateInput) return;

            // 这里可以添加日期选择器的初始化代码
            // 例如使用flatpickr或其他日期选择库
        }

        // 初始化筛选功能
        function initializeFilters() {
            const typeSelect = document.getElementById('notification_type');
            const statusSelect = document.getElementById('status');
            const recipientSelectors = document.querySelectorAll('input[name="receivers[recipient_filter][]"]');
            
            // 从URL获取筛选参数
            const urlParams = new URLSearchParams(window.location.search);
            const typeParam = urlParams.get('type');
            const statusParam = urlParams.get('status');
            
            // 设置初始值
            if (typeParam && typeSelect) typeSelect.value = typeParam;
            if (statusParam && statusSelect) statusSelect.value = statusParam;
            
            // 添加事件监听器，当筛选条件变化时更新列表
            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    fetchNotificationLogs();
                });
            }
            
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    fetchNotificationLogs();
                });
            }
            
            // 为收件人复选框添加事件监听器
            recipientSelectors.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    fetchNotificationLogs();
                });
            });
            
            // 初始加载数据
            fetchNotificationLogs();
        }

        // 从URL应用筛选器
        function applyFiltersFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);
            const typeParam = urlParams.get('type');
            const statusParam = urlParams.get('status');
            const recipientParam = urlParams.get('recipient');
            const dateParam = urlParams.get('date');

            const typeSelect = document.getElementById('notification_type');
            const statusSelect = document.getElementById('status');
            const recipientInput = document.getElementById('recipient');
            const dateInput = document.getElementById('date_range');

            if (typeParam && typeSelect) typeSelect.value = typeParam;
            if (statusParam && statusSelect) statusSelect.value = statusParam;
            if (recipientParam && recipientInput) recipientInput.value = recipientParam;
            if (dateParam && dateInput) dateInput.value = dateParam;

            applyFilters();
        }

        // 应用筛选器
        function applyFilters() {
            const typeValue = document.getElementById('notification_type').value;
            const statusValue = document.getElementById('status').value;
            const recipientValue = document.getElementById('recipient').value.toLowerCase();
            const dateValue = document.getElementById('date_range').value;

            const rows = document.querySelectorAll('.notification-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const rowType = row.getAttribute('data-type');
                const rowStatus = row.getAttribute('data-status');
                const rowRecipient = row.getAttribute('data-recipient').toLowerCase();
                const rowDate = row.getAttribute('data-date');

                const typeMatch = !typeValue || rowType === typeValue;
                const statusMatch = !statusValue || rowStatus === statusValue;
                const recipientMatch = !recipientValue || rowRecipient.includes(recipientValue);
                const dateMatch = !dateValue || rowDate === dateValue;

                const isVisible = typeMatch && statusMatch && recipientMatch && dateMatch;

                row.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleCount++;
            });

            // 显示或隐藏"无结果"行
            const noResultsRow = document.getElementById('no-results-row');
            const emptyStateRow = document.getElementById('empty-state-row');

            if (noResultsRow && emptyStateRow) {
                if (rows.length > 0 && visibleCount === 0) {
                    noResultsRow.style.display = '';
                    emptyStateRow.style.display = 'none';
                } else if (rows.length === 0) {
                    noResultsRow.style.display = 'none';
                    emptyStateRow.style.display = '';
                } else {
                    noResultsRow.style.display = 'none';
                    emptyStateRow.style.display = 'none';
                }
            }

            // 更新URL参数
            updateUrlParams();
        }

        // 更新URL参数
        function updateUrlParams() {
            const typeValue = document.getElementById('notification_type').value;
            const statusValue = document.getElementById('status').value;
            const recipientValue = document.getElementById('recipient').value;
            const dateValue = document.getElementById('date_range').value;

            const urlParams = new URLSearchParams();

            if (typeValue) urlParams.set('type', typeValue);
            if (statusValue) urlParams.set('status', statusValue);
            if (recipientValue) urlParams.set('recipient', recipientValue);
            if (dateValue) urlParams.set('date', dateValue);

            const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
            window.history.replaceState({}, '', newUrl);
        }

        // 重置筛选器
        function resetFilters() {
            const typeSelect = document.getElementById('notification_type');
            const statusSelect = document.getElementById('status');
            const recipientInput = document.getElementById('recipient');
            const dateInput = document.getElementById('date_range');

            if (typeSelect) typeSelect.value = '';
            if (statusSelect) statusSelect.value = '';
            if (recipientInput) recipientInput.value = '';
            if (dateInput) dateInput.value = '';

            applyFilters();
        }

        // 初始化模态框
        function initializeModal() {
            const modal = document.getElementById('notification-modal');
            const closeButtons = document.querySelectorAll('.close-modal');
            const viewButtons = document.querySelectorAll('.view-notification');

            if (!modal) return;

            // 查看按钮点击事件
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const content = this.getAttribute('data-content');
                    const subject = this.getAttribute('data-subject');
                    const recipient = this.getAttribute('data-recipient');
                    const status = this.getAttribute('data-status');

                    // 更新模态框内容
                    const modalContent = document.getElementById('modal-content');
                    const modalSubject = document.getElementById('modal-subject');
                    const modalRecipient = document.getElementById('modal-recipient');
                    const modalStatus = document.getElementById('modal-status');

                    if (modalContent) {
                        // 解码HTML实体并渲染HTML内容
                        try {
                            const parser = new DOMParser();
                            const decodedContent = decodeHTMLEntities(content);

                            // 使用DOMParser解析HTML内容
                            const doc = parser.parseFromString(decodedContent, 'text/html');

                            // 清空现有内容
                            modalContent.innerHTML = '';

                            // 将解析后的内容添加到模态框
                            Array.from(doc.body.childNodes).forEach(node => {
                                modalContent.appendChild(node.cloneNode(true));
                            });
                        } catch (error) {
                            console.error('Error parsing HTML content:', error);
                            modalContent.innerHTML = decodeHTMLEntities(content);
                        }
                    }

                    if (modalSubject) modalSubject.textContent = decodeHTMLEntities(subject);
                    if (modalRecipient) modalRecipient.textContent = decodeHTMLEntities(recipient);
                    if (modalStatus) {
                        let statusText = status;
                        let statusClass = '';

                        if (status === 'sent') {
                            statusText = 'Sent';
                            statusClass = 'text-green-600';
                        } else if (status === 'failed') {
                            statusText = 'Failed';
                            statusClass = 'text-red-600';
                        }

                        modalStatus.textContent = statusText;
                        modalStatus.className = `text-base mt-1 ${statusClass}`;
                    }

                    modal.classList.remove('hidden');
                    modal.classList.add('show');
                    document.body.classList.add('modal-open');
                });
            });

            // 关闭按钮点击事件
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    modal.classList.remove('show');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        document.body.classList.remove('modal-open');
                    }, 2000);
                });
            });

            // 点击背景关闭模态框
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        document.body.classList.remove('modal-open');
                    }, 2000);
                }
            });
        }

        // 初始化接收人选择器
        function initializeRecipientSelectors() {
            const toggleButtons = document.querySelectorAll('.toggle-recipients');
            const selectAllButtons = document.querySelectorAll('.select-all-btn');
            const deselectAllButtons = document.querySelectorAll('.deselect-all-btn');
            const searchInputs = document.querySelectorAll('.recipient-search');
            const recipientRadios = document.querySelectorAll('.recipient-radio');

            // 首先检查并同步所有选择器的显示状态
            document.querySelectorAll('.recipient-selector').forEach(selector => {
                // 获取当前计算样式
                const computedStyle = window.getComputedStyle(selector);
                const isActuallyHidden = computedStyle.display === 'none';
                
                // 确保classList和实际显示状态一致
                if (isActuallyHidden && !selector.classList.contains('hidden')) {
                    selector.classList.add('hidden');
                } else if (!isActuallyHidden && selector.classList.contains('hidden')) {
                    selector.style.display = 'none';
                    selector.style.visibility = 'hidden';
                    selector.classList.add('hidden');
                }
                
                // 记录状态到自定义属性，便于调试
                selector.setAttribute('data-initialized-hidden', selector.classList.contains('hidden'));
                
                // 为选择器内的点击添加阻止冒泡，防止点击选择器内部时关闭选择器
                selector.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });

            // 切换接收人选择器显示/隐藏
            toggleButtons.forEach(button => {
                // 清除之前的事件监听器，防止重复绑定
                button.removeEventListener('click', toggleRecipientSelector);
                button.addEventListener('click', function(e) {
                    e.stopPropagation(); // 阻止冒泡，防止立即被全局点击监听器关闭
                    toggleRecipientSelector.call(this);
                });
                
                // 同步按钮图标状态
                const type = button.getAttribute('data-type');
                const selector = document.getElementById(`selector-${type}`);
                const icon = button.querySelector('.toggle-icon');
                
                if (selector && icon) {
                    if (selector.classList.contains('hidden')) {
                        icon.style.transform = 'rotate(0deg)';
                    } else {
                        icon.style.transform = 'rotate(180deg)';
                    }
                }
            });

            // 全选按钮
            selectAllButtons.forEach(button => {
                button.removeEventListener('click', selectAllRecipients);
                button.addEventListener('click', selectAllRecipients);
            });

            // 取消全选按钮
            deselectAllButtons.forEach(button => {
                button.removeEventListener('click', deselectAllRecipients);
                button.addEventListener('click', deselectAllRecipients);
            });

            // 搜索输入框
            searchInputs.forEach(input => {
                input.removeEventListener('input', searchRecipients);
                input.addEventListener('input', searchRecipients);
            });

            // 单选按钮更改事件
            recipientRadios.forEach(radio => {
                radio.removeEventListener('change', updateSelectedTestEmailRecipient);
                radio.addEventListener('change', updateSelectedTestEmailRecipient);
            });

            // 复选框更改事件
            document.querySelectorAll('.user-item input[type="checkbox"]').forEach(checkbox => {
                checkbox.removeEventListener('change', checkboxChangeHandler);
                checkbox.addEventListener('change', checkboxChangeHandler);
            });

            // 初始更新已选接收人
            document.querySelectorAll('.user-list').forEach(list => {
                const type = list.getAttribute('data-type');
                if (type !== 'test_email_recipient') {
                    updateSelectedRecipients(type);
                }
            });

            // 初始更新测试邮件接收人
            updateSelectedTestEmailRecipient();
        }

        // 切换接收人选择器的处理函数
        function toggleRecipientSelector() {
            const type = this.getAttribute('data-type');
            const selector = document.getElementById(`selector-${type}`);
            const icon = this.querySelector('.toggle-icon');

            if (!selector) {
                console.warn(`Selector for type ${type} not found`);
                return;
            }

            try {
                // 获取当前可见状态 - 使用offsetParent来检查实际可见性
                // 如果元素不可见，offsetParent通常为null
                const isCurrentlyVisible = !!selector.offsetParent;
                
                console.log(`Toggle action for ${type}:`, {
                    isCurrentlyVisible: isCurrentlyVisible,
                    offsetParent: selector.offsetParent,
                    currentDisplay: selector.style.display,
                    currentVisibility: selector.style.visibility,
                    computedDisplay: window.getComputedStyle(selector).display,
                    hasHiddenClass: selector.classList.contains('hidden')
                });

                // 完全重置所有可能影响显示状态的属性
                if (isCurrentlyVisible) {
                    // 当前可见，需要隐藏
                    selector.style.display = 'none';
                    selector.style.visibility = 'hidden';
                    selector.classList.add('hidden');
                    selector.setAttribute('aria-hidden', 'true');
                    selector.setAttribute('data-state', 'closed');
                    
                    // 更新图标
                    if (icon) {
                        icon.style.transform = 'rotate(0deg)';
                    }
                    
                    console.log(`Hiding selector ${type}`);
                } else {
                    // 当前隐藏，需要显示
                    selector.style.removeProperty('display');
                    selector.style.display = 'block';
                    selector.style.visibility = 'visible';
                    selector.classList.remove('hidden');
                    selector.setAttribute('aria-hidden', 'false');
                    selector.setAttribute('data-state', 'open');
                    
                    // 更新图标
                    if (icon) {
                        icon.style.transform = 'rotate(180deg)';
                    }
                    
                    console.log(`Showing selector ${type}`);
                }
                
                // 强制重绘
                void selector.offsetHeight;
                
                // 检查切换后的状态
                setTimeout(() => {
                    const afterToggleVisible = !!selector.offsetParent;
                    console.log(`After toggle for ${type}:`, {
                        isVisible: afterToggleVisible,
                        display: selector.style.display,
                        computedDisplay: window.getComputedStyle(selector).display,
                        hasHiddenClass: selector.classList.contains('hidden')
                    });
                    
                    // 如果状态仍不正确，尝试最后的强制修正
                    if (isCurrentlyVisible === afterToggleVisible) {
                        console.warn(`Toggle failed for ${type}, applying force fix`);
                        selector.style.display = isCurrentlyVisible ? 'none !important' : 'block !important';
                        // 最后的手段：直接操作innerHTML添加行内样式
                        if (isCurrentlyVisible) {
                            document.querySelector(`#selector-${type}`).setAttribute('style', 'display: none !important; visibility: hidden !important;');
                        } else {
                            document.querySelector(`#selector-${type}`).setAttribute('style', 'display: block !important; visibility: visible !important;');
                        }
                    }
                }, 50);
            } catch (error) {
                console.error(`Error toggling recipient selector: ${error.message}`);
            }
        }

        // 全选处理函数
        function selectAllRecipients() {
            const type = this.getAttribute('data-type');
            const checkboxes = document.querySelectorAll(`input[name="receivers[${type}][]"]`);

            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });

            updateSelectedRecipients(type);
        }

        // 取消全选处理函数
        function deselectAllRecipients() {
            const type = this.getAttribute('data-type');
            const checkboxes = document.querySelectorAll(`input[name="receivers[${type}][]"]`);

            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            updateSelectedRecipients(type);
        }

        // 搜索处理函数
        function searchRecipients() {
            const type = this.getAttribute('data-type');
            const searchValue = this.value.toLowerCase();
            const userItems = document.querySelectorAll(`.user-list[data-type="${type}"] .user-item`);

            userItems.forEach(item => {
                const userName = item.querySelector('.text-sm').textContent.toLowerCase();
                const userEmail = item.querySelector('.text-xs').textContent.toLowerCase();

                if (userName.includes(searchValue) || userEmail.includes(searchValue)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // 复选框变化处理函数
        function checkboxChangeHandler() {
            const name = this.name;
            const type = name.match(/receivers\[(.*?)\]/)[1];
            updateSelectedRecipients(type);
        }

        // 更新已选接收人显示
        function updateSelectedRecipients(type) {
            const selectedContainer = document.getElementById(`selected-${type}`);
            const checkboxes = document.querySelectorAll(`input[name="receivers[${type}][]"]:checked`);

            if (selectedContainer) {
                if (checkboxes.length === 0) {
                selectedContainer.innerHTML = '<div class="text-sm text-gray-500 italic">No recipients selected</div>';
                } else {
                    let html = '<div class="flex flex-wrap gap-2">';

                    checkboxes.forEach(checkbox => {
                        const label = checkbox.closest('label');
                        const userName = label.querySelector('.text-sm').textContent;
                        const userEmail = label.querySelector('.text-xs').textContent;

                        html += `
                        <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <span>${userName}</span>
                            <button type="button" class="ml-1 text-blue-500 hover:text-blue-700 focus:outline-none" onclick="removeRecipient('${type}', '${userEmail}')">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                    });

                    html += '</div>';
                    selectedContainer.innerHTML = html;
                }
            }
        }

        // 更新测试邮件接收人显示
        function updateSelectedTestEmailRecipient() {
            const selectedContainer = document.getElementById('selected-test_email_recipient');
            const selectedRadio = document.querySelector('input[name="recipient"]:checked');

            if (selectedContainer) {
                if (!selectedRadio) {
                    selectedContainer.innerHTML = '<div class="text-sm text-gray-500 italic">No recipient selected</div>';
                } else {
                    const label = selectedRadio.closest('label');
                    const userName = label.querySelector('.text-sm').textContent;
                    const userEmail = label.querySelector('.text-xs').textContent;

                    selectedContainer.innerHTML = `
                    <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <span>${userName}</span>
                        <span class="ml-1 text-blue-600">(${userEmail})</span>
                    </div>
                `;
                }
            }
        }

        // 移除接收人
    function removeRecipient(type, email) {
            const checkbox = document.querySelector(`input[name="receivers[${type}][]"][value="${email}"]`);

            if (checkbox) {
                checkbox.checked = false;
                updateSelectedRecipients(type);
            }
    }

        // 加载模板
    function loadTemplates(selectedType) {
            if (!selectedType) return;

            const templateSelect = document.getElementById('message_template_id');
            const subjectInput = document.getElementById('subject');
            const contentTextarea = document.getElementById('content');

            if (!templateSelect) {
                console.error('Template select element not found!');
                return;
            }

            console.log('Loading templates for type:', selectedType);

            // 清空选择框
            templateSelect.innerHTML = '<option value="">Loading templates...</option>';

            // 获取模板
            fetch(`/api/message-templates?type=${selectedType}`)
                .then(response => {
                    console.log('API response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('API error response:', text);
                            throw new Error(`Failed to load templates: ${response.status} ${response.statusText}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API response data:', data);

                    if (data.success && data.templates) {
                        templateSelect.innerHTML = '<option value="">Custom (No Template)</option>';

                        if (data.templates.length === 0) {
                            console.log('No templates found for type:', selectedType);
                            templateSelect.innerHTML = '<option value="">No templates available</option>';
                        } else {
                            let defaultTemplateId = null;

                            data.templates.forEach(template => {
                                const option = document.createElement('option');
                                option.value = template.id;
                                option.textContent = template.subject || `Template #${template.id}`;
                                option.dataset.subject = template.subject || '';
                                option.dataset.content = template.content || '';
                                templateSelect.appendChild(option);

                                // 记录默认模板ID
                                if (template.is_default) {
                                    defaultTemplateId = template.id;
                                }
                            });

                            // 如果有默认模板，自动选择它
                            if (defaultTemplateId) {
                                templateSelect.value = defaultTemplateId;

                                // 获取选中的选项
                                const selectedOption = templateSelect.options[templateSelect.selectedIndex];

                                // 更新subject和content
                                if (subjectInput && contentTextarea) {
                                    subjectInput.value = selectedOption.dataset.subject || '';
                                    contentTextarea.value = selectedOption.dataset.content || '';
                                }

                                // 显示模板变量
                                document.getElementById('template-variables-container').classList.remove('hidden');
                                loadTemplateVariables(defaultTemplateId);
                            }
                        }
                    } else {
                        console.error('Invalid API response format:', data);
                        templateSelect.innerHTML = '<option value="">No templates available</option>';
                    }
                })
                .catch(error => {
                    console.error('Failed to load templates:', error);
                    templateSelect.innerHTML = '<option value="">Failed to load templates</option>';

                    // 显示错误消息
                    const resultDiv = document.getElementById('result');
                    if (resultDiv) {
                        resultDiv.classList.remove('hidden');
                        resultDiv.classList.add('bg-red-50', 'text-red-700', 'p-4', 'rounded-md');
                        resultDiv.innerHTML = `<p>Error loading templates: ${error.message}</p>`;

                        // 5秒后隐藏错误消息
                        setTimeout(() => {
                            resultDiv.classList.add('hidden');
                        }, 5000);
                    }
                });
    }

        // 加载模板变量
    function loadTemplateVariables(templateId) {
            if (!templateId) return;

            const variablesContainer = document.getElementById('template-variables-list');
            if (!variablesContainer) {
                console.error('Template variables container not found!');
                return;
            }

            console.log('Loading variables for template ID:', templateId);

            // 显示加载中
            variablesContainer.innerHTML = '<div class="text-sm text-gray-500">Loading variables...</div>';

            // 获取模板变量
            fetch(`/api/message-templates/${templateId}/variables`)
                .then(response => {
                    console.log('Variables API response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Variables API error response:', text);
                            throw new Error(`Failed to load template variables: ${response.status} ${response.statusText}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Variables API response data:', data);

                    if (data.success && data.variables) {
                        let html = '';

                        Object.entries(data.variables).forEach(([variable, description]) => {
                            html += `
                            <div class="p-1.5 border border-gray-200 rounded bg-white">
                                <div class="font-mono text-xs text-blue-600 font-semibold">${variable}</div>
                                <div class="text-xs text-gray-500 truncate" title="${description}">${description}</div>
                            </div>
                        `;
                        });

                        variablesContainer.innerHTML = html;
                    } else {
                        console.error('Invalid variables API response format:', data);
                        variablesContainer.innerHTML = '<div class="text-sm text-gray-500">No variables available</div>';
                    }
                })
                .catch(error => {
                    console.error('Failed to load template variables:', error);
                    variablesContainer.innerHTML = '<div class="text-sm text-red-500">Failed to load variables: ' + error.message + '</div>';
                });
    }

        // 发送测试邮件
        function sendTestEmail() {
            const form = document.getElementById('test-email-form');
            const resultDiv = document.getElementById('result');

            if (!form || !resultDiv) return;

            // 获取表单数据
            const formData = new FormData(form);
            const recipient = formData.get('recipient');

            if (!recipient) {
                resultDiv.classList.remove('hidden');
                resultDiv.classList.add('bg-red-50', 'text-red-700', 'p-4', 'rounded-md');
                resultDiv.innerHTML = '<p>Please select a recipient</p>';
                return;
            }

            // 显示加载中
            resultDiv.classList.remove('hidden', 'bg-red-50', 'bg-green-50', 'text-red-700', 'text-green-700');
            resultDiv.classList.add('bg-blue-50', 'text-blue-700', 'p-4', 'rounded-md');
            resultDiv.innerHTML = '<p>Sending test email...</p>';

            // 准备发送数据
            const data = {
                recipient: recipient,
                type: formData.get('template_type'),
                subject: formData.get('subject'),
                content: formData.get('content'),
                message_template_id: formData.get('message_template_id') || null,
                test_data: JSON.stringify({}),
                user_id: getUserIdByEmail(recipient)
            };

            console.log('Sending test email with data:', data);
            
            // 先创建一条待发送状态的通知记录
            createNotificationLog(data, 'sending')
                .then(logId => {
                    console.log('Created notification log with ID:', logId);
                    
                    // 发送邮件请求
                    return fetch('/test-email', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({...data, log_id: logId})
                    })
                    .then(response => {
                        console.log('Test email API response status:', response.status);
                        return response.json().then(responseData => ({response, data: responseData, logId}));
                    });
                })
                .then(({response, data, logId}) => {
                    console.log('Test email API response data:', data);
                    
                    // 更新通知记录状态（只有当logId存在且有效时）
                    if (logId && logId !== 'undefined' && !isNaN(parseInt(logId))) {
                        const status = data.success ? 'sent' : 'failed';
                        const errorMessage = data.success ? null : data.message;
                        
                        updateNotificationLog(logId, status, errorMessage)
                            .then(() => {
                                // 使用API请求更新列表，而不是刷新页面
                                fetchNotificationLogs();
                            })
                            .catch(error => {
                                console.error('Error updating notification log:', error);
                                // 即使更新日志失败，也尝试更新列表
                                fetchNotificationLogs();
                            });
                    } else {
                        console.log('No valid logId to update, skipping updateNotificationLog');
                        // 仍然尝试更新列表
                        fetchNotificationLogs();
                    }

                    if (data.success) {
                        resultDiv.classList.remove('bg-blue-50', 'text-blue-700', 'bg-red-50', 'text-red-700');
                        resultDiv.classList.add('bg-green-50', 'text-green-700');
                        resultDiv.innerHTML = '<p>Test email sent successfully!</p>';
                    } else {
                        resultDiv.classList.remove('bg-blue-50', 'text-blue-700', 'bg-green-50', 'text-green-700');
                        resultDiv.classList.add('bg-red-50', 'text-red-700');
                        resultDiv.innerHTML = `<p>Failed to send test email: ${data.message}</p>`;
                    }

                    // 5秒后隐藏结果
                    setTimeout(() => {
                        resultDiv.classList.add('hidden');
                    }, 5000);
                })
                .catch(error => {
                    console.error('Error sending test email:', error);

                    resultDiv.classList.remove('bg-blue-50', 'text-blue-700', 'bg-green-50', 'text-green-700');
                    resultDiv.classList.add('bg-red-50', 'text-red-700');
                    resultDiv.innerHTML = `<p>Error sending test email: ${error.message}</p>`;

                    // 5秒后隐藏结果
                    setTimeout(() => {
                        resultDiv.classList.add('hidden');
                    }, 5000);
                });
        }
        
        // 创建通知日志记录
        function createNotificationLog(data, status = 'sending') {
            // 获取用户ID，如果没有则使用系统用户
            const userEmail = data.recipient;
            const userId = getUserIdByEmail(userEmail) || 1; // 默认使用ID为1的系统用户
            
            const logData = {
                type: data.type,
                user_id: userId, // 使用用户ID而不是邮箱
                subject: data.subject,
                content: data.content,
                status: status,
                message_template_id: data.message_template_id
            };
            
            return fetch('/api/notification-logs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(logData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to create notification log');
                }
                return response.json();
            })
            .then(data => {
                console.log('Notification log created:', data);
                return data.log_id; // 修改这里，使用data.log_id而不是data.id
            });
        }
        
        // 更新通知日志记录
        function updateNotificationLog(id, status, error = null) {
            const updateData = {
                status: status,
                error_message: error
            };
            
            return fetch(`/api/notification-logs/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(updateData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to update notification log');
                }
                return response.json();
            })
            .then(data => {
                console.log('Notification log updated:', data);
                return data;
            });
        }

        // 预览邮件
        function previewEmail() {
            const form = document.getElementById('test-email-form');
            const previewModal = document.getElementById('preview-modal');
            const previewSubject = document.getElementById('preview-subject');
            const previewContent = document.getElementById('preview-content');

            if (!form || !previewModal || !previewSubject || !previewContent) return;

            // 获取表单数据
            const formData = new FormData(form);
            const subject = formData.get('subject');
            const content = formData.get('content');

            // 显示预览
            previewSubject.textContent = subject;
            previewContent.innerHTML = content;
            previewModal.classList.remove('hidden');
        }

        // 添加多重事件监听，确保在各种加载情况下都能正确初始化
        document.addEventListener('DOMContentLoaded', initializeElements);
        document.addEventListener('turbolinks:load', initializeElements);

        // 如果页面已经加载完成，立即初始化
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            initializeElements();
        }

        // 解码HTML实体
        function decodeHTMLEntities(text) {
            if (!text) return '';

            const textArea = document.createElement('textarea');
            textArea.innerHTML = text
                .replace(/&nbsp;/g, ' ')
                .replace(/&amp;/g, '&')
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .replace(/&quot;/g, '"')
                .replace(/&#039;/g, "'");

            return textArea.value;
        }

        // 根据邮箱获取用户ID
        function getUserIdByEmail(email) {
            // 首先尝试从选中的单选按钮获取用户ID
            const selectedRadio = document.querySelector(`input[type="radio"][name="recipient"][value="${email}"]:checked`);
            if (selectedRadio && selectedRadio.dataset.userId) {
                return selectedRadio.dataset.userId;
            }
            
            // 如果没有选中的单选按钮，尝试从所有单选按钮中查找
            const radioButton = document.querySelector(`input[type="radio"][name="recipient"][value="${email}"]`);
            if (radioButton && radioButton.dataset.userId) {
                return radioButton.dataset.userId;
            }
            
            // 如果仍然找不到，尝试从复选框中查找
            const checkbox = document.querySelector(`input[type="checkbox"][value="${email}"]`);
            if (checkbox && checkbox.dataset.userId) {
                return checkbox.dataset.userId;
            }
            
            // 从页面上获取用户数据
            const userItems = document.querySelectorAll('.user-item');
            for (const userItem of userItems) {
                const emailElement = userItem.querySelector('.text-xs.text-gray-500');
                const userEmail = emailElement ? emailElement.textContent.trim() : '';
                
                if (userEmail === email) {
                    const input = userItem.querySelector('input[type="radio"], input[type="checkbox"]');
                    if (input && input.dataset.userId) {
                        return input.dataset.userId;
                    }
                }
            }
            
            // 如果找不到用户，返回系统用户ID（通常为1）
            return 1;
        }

    // 显示Toast通知
    function showToast(message, type = 'info') {
        const alertId = type === 'success' ? 'success-alert' : 'error-alert';
        const alert = document.getElementById(alertId);
        
        if (alert) {
            // 更新消息内容
            const messageElement = alert.querySelector('.text-sm');
            if (messageElement) {
                messageElement.textContent = message;
            }
            
            // 显示通知
            alert.classList.remove('hidden');
            
            // 3秒后自动隐藏
            setTimeout(() => {
                alert.classList.add('hidden');
            }, 3000);
        } else {
            // 如果找不到预定义的警告框，创建一个临时的提示框
            const tempAlert = document.createElement('div');
            tempAlert.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg ${type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'} z-50`;
            tempAlert.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        ${type === 'success' 
                            ? '<svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
                            : '<svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>'
                        }
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                </div>
            `;
            document.body.appendChild(tempAlert);
            
            // 3秒后移除临时提示框
            setTimeout(() => {
                tempAlert.remove();
            }, 3000);
        }
    }

        // 获取通知日志列表
        function fetchNotificationLogs() {
            // 获取当前筛选条件
            const type = document.getElementById('notification_type')?.value || '';
            const status = document.getElementById('status')?.value || '';
            
            // 构建查询参数
            const params = new URLSearchParams();
            if (type) params.append('type', type);
            if (status) params.append('status', status);
            
            // 获取选中的收件人
            const selectedRecipients = Array.from(document.querySelectorAll('input[name="receivers[recipient_filter][]"]:checked')).map(el => el.value);
            if (selectedRecipients.length > 0) {
                selectedRecipients.forEach(recipient => params.append('recipient', recipient));
            }
            
            // 发送API请求
            return fetch(`/api/notification-logs?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch notification logs');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateNotificationTable(data.data);
                } else {
                    console.error('Failed to fetch notification logs:', data.message);
                }
                return data;
            });
        }
        
        // 更新通知表格
        function updateNotificationTable(data) {
            const tableBody = document.querySelector('table tbody');
            if (!tableBody) return;
            
            // 清空表格
            tableBody.innerHTML = '';
            
            // 如果没有数据
            if (!data.data || data.data.length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No notification logs found
                    </td>
                `;
                tableBody.appendChild(emptyRow);
                return;
            }
            
            // 添加数据行
            data.data.forEach(log => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                // 格式化日期
                const date = new Date(log.created_at);
                const formattedDate = date.toISOString().replace('T', ' ').substring(0, 19);
                
                // 设置状态样式
                let statusClass = '';
                let statusText = log.status;
                
                switch (log.status) {
                    case 'sent':
                        statusClass = 'bg-green-100 text-green-800';
                        statusText = 'Sent';
                        break;
                    case 'failed':
                        statusClass = 'bg-red-100 text-red-800';
                        statusText = 'Failed';
                        break;
                    case 'sending':
                        statusClass = 'bg-blue-100 text-blue-800';
                        statusText = 'Sending';
                        break;
                    case 'pending':
                        statusClass = 'bg-yellow-100 text-yellow-800';
                        statusText = 'Pending';
                        break;
                }
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${formattedDate}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${log.type}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${log.recipient}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-[300px] truncate">
                        ${log.subject}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button type="button" class="text-blue-600 hover:text-blue-900 view-notification" data-id="${log.id}">
                            View
                        </button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
            
            // 重新绑定查看详情按钮事件
            document.querySelectorAll('.view-notification').forEach(button => {
                button.addEventListener('click', function() {
                    const logId = this.getAttribute('data-id');
                    viewNotificationDetails(logId);
                });
            });
        }

        // 查看通知详情
        function viewNotificationDetails(id) {
            // 获取模态框元素
            const modal = document.getElementById('notification-modal');
            const modalContent = document.getElementById('modal-content');
            const modalSubject = document.getElementById('modal-subject');
            const modalRecipient = document.getElementById('modal-recipient');
            const modalStatus = document.getElementById('modal-status');
            const modalTime = document.getElementById('modal-time');
            const modalType = document.getElementById('modal-type');
            const modalError = document.getElementById('modal-error');
            const modalErrorContainer = document.getElementById('modal-error-container');
            
            if (!modal || !modalContent) {
                console.error('Modal elements not found');
                return;
            }
            
            // 显示加载中
            modalContent.innerHTML = `
                <div class="flex justify-center items-center p-8">
                    <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            `;
            
            // 显示模态框
            modal.classList.remove('hidden');
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            
            // 获取通知详情
            fetch(`/api/notification-logs/${id}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch notification details');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const log = data.data;
                    
                    // 更新模态框内容
                    if (modalSubject) modalSubject.textContent = decodeHTMLEntities(log.subject);
                    if (modalRecipient) modalRecipient.textContent = log.recipient;
                    if (modalStatus) {
                        let statusText = log.status;
                        let statusClass = '';
                        
                        switch (log.status) {
                            case 'sent':
                                statusText = 'Sent';
                                statusClass = 'text-green-600';
                                break;
                            case 'failed':
                                statusText = 'Failed';
                                statusClass = 'text-red-600';
                                break;
                            case 'sending':
                                statusText = 'Sending';
                                statusClass = 'text-blue-600';
                                break;
                            case 'pending':
                                statusText = 'Pending';
                                statusClass = 'text-yellow-600';
                                break;
                        }
                        
                        modalStatus.textContent = statusText;
                        modalStatus.className = `text-base mt-1 ${statusClass}`;
                    }
                    
                    if (modalTime) {
                        const date = new Date(log.created_at);
                        modalTime.textContent = date.toLocaleString();
                    }
                    
                    if (modalType) modalType.textContent = log.type;
                    
                    // 显示错误信息（如果有）
                    if (modalError && modalErrorContainer) {
                        if (log.error_message) {
                            modalError.textContent = log.error_message;
                            modalErrorContainer.classList.remove('hidden');
                        } else {
                            modalErrorContainer.classList.add('hidden');
                        }
                    }
                    
                    // 更新内容区域
                    modalContent.innerHTML = decodeHTMLEntities(log.content);
                } else {
                    modalContent.innerHTML = `
                        <div class="p-4 bg-red-50 text-red-700 rounded-md">
                            <p>Failed to load notification details: ${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching notification details:', error);
                modalContent.innerHTML = `
                    <div class="p-4 bg-red-50 text-red-700 rounded-md">
                        <p>Error loading notification details: ${error.message}</p>
                    </div>
                `;
            });
        }

    // 特殊函数：强制初始化所有选择器的显示状态
    function forceInitializeSelectors() {
        try {
            console.log('Force initializing all recipient selectors');
            document.querySelectorAll('.recipient-selector').forEach(selector => {
                // 完全重置所有可能影响显示状态的属性
                selector.style.display = 'none';
                selector.style.visibility = 'hidden';
                selector.classList.add('hidden');
                selector.setAttribute('aria-hidden', 'true');
                
                // 确保行内样式优先级最高
                selector.setAttribute('style', 'display: none !important; visibility: hidden !important;');
                
                // 强制重绘
                void selector.offsetHeight;
                
                console.log(`Initialized selector ${selector.id}: hidden=${selector.classList.contains('hidden')}, display=${selector.style.display}, offsetParent=${!!selector.offsetParent}`);
            });

            // 确保所有切换图标默认状态正确
            document.querySelectorAll('.toggle-recipients .toggle-icon').forEach(icon => {
                icon.style.transform = 'rotate(0deg)';
            });
            
            // 添加全局点击监听器，处理任何打开的选择器在点击外部时关闭
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.recipient-selector') && !e.target.closest('.toggle-recipients')) {
                    document.querySelectorAll('.recipient-selector').forEach(selector => {
                        if (!!selector.offsetParent) {
                            // 如果选择器可见但点击发生在外部，则强制隐藏
                            selector.style.display = 'none';
                            selector.style.visibility = 'hidden';
                            selector.classList.add('hidden');
                            selector.setAttribute('aria-hidden', 'true');
                            selector.setAttribute('data-state', 'closed');
                            selector.setAttribute('style', 'display: none !important; visibility: hidden !important;');
                            
                            // 同时重置对应的图标
                            const type = selector.id.replace('selector-', '');
                            const btn = document.querySelector(`.toggle-recipients[data-type="${type}"]`);
                            if (btn) {
                                const icon = btn.querySelector('.toggle-icon');
                                if (icon) {
                                    icon.style.transform = 'rotate(0deg)';
                                }
                            }
                        }
                    });
                }
            });
        } catch (error) {
            console.error('Error during force initialization:', error);
        }
    }
</script>
@endpush 

