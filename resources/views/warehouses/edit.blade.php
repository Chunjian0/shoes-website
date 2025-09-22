@extends('layouts.app')

@section('title', 'Edit warehouse')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate flex items-center">
                <svg class="w-8 h-8 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Edit warehouse
            </h2>
            <p class="mt-1 text-lg text-gray-500 flex items-center">
                <span class="font-medium text-indigo-600">{{ $warehouse->name }}</span>
                <span class="ml-2 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $warehouse->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $warehouse->status ? 'Active' : 'Inactive' }}
                </span>
                <span class="ml-2 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $warehouse->is_store ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $warehouse->is_store ? 'Storefront' : 'Warehouse' }}
                </span>
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <a href="{{ route('warehouses.show', $warehouse) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <svg class="h-5 w-5 mr-2 -ml-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                View details
            </a>
            <a href="{{ route('warehouses.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <svg class="h-5 w-5 mr-2 -ml-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to list
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg animate__animated animate__fadeIn">
        <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-indigo-50 to-white border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Edit warehouse information
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Update the warehouse details below.
                    </p>
                </div>
            </div>
        </div>
        <form action="{{ route('warehouses.update', $warehouse) }}" method="POST" class="warehouse-form">
            @csrf
            @method('PUT')

            <div class="px-4 py-5 sm:p-6 space-y-6">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <!-- Warehouse number -->
                    <div class="sm:col-span-3">
                        <label for="code" class="block text-sm font-medium text-gray-700">Warehouse code <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                            </div>
                            <input type="text" name="code" id="code" value="{{ old('code', $warehouse->code) }}" required
                                class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('code') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror py-2.5 text-base">
                        </div>
                        @error('code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Warehouse name -->
                    <div class="sm:col-span-3">
                        <label for="name" class="block text-sm font-medium text-gray-700">Warehouse name <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name', $warehouse->name) }}" required
                                class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('name') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror py-2.5 text-base">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Warehouse address -->
                    <div class="sm:col-span-6">
                        <label for="address" class="block text-sm font-medium text-gray-700">Warehouse address</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="address" id="address" value="{{ old('address', $warehouse->address) }}"
                                class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('address') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror py-2.5 text-base">
                        </div>
                        @error('address')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact person -->
                    <div class="sm:col-span-3">
                        <label for="contact_person" class="block text-sm font-medium text-gray-700">Contact person</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $warehouse->contact_person) }}"
                                class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('contact_person') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror py-2.5 text-base">
                        </div>
                        @error('contact_person')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact phone -->
                    <div class="sm:col-span-3">
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700">Contact phone</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $warehouse->contact_phone) }}"
                                class="pl-10 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('contact_phone') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror py-2.5 text-base">
                        </div>
                        @error('contact_phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Warehouse status -->
                    <div class="sm:col-span-3">
                        <fieldset>
                            <legend class="block text-sm font-medium text-gray-700">Status</legend>
                            <div class="mt-4 space-y-4">
                                <div class="flex items-center">
                                    <input id="status-active" name="status" type="radio" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" {{ old('status', $warehouse->status) == '1' ? 'checked' : '' }}>
                                    <label for="status-active" class="ml-3 block text-sm font-medium text-gray-700">
                                        Active
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="status-inactive" name="status" type="radio" value="0" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" {{ old('status', $warehouse->status) == '0' ? 'checked' : '' }}>
                                    <label for="status-inactive" class="ml-3 block text-sm font-medium text-gray-700">
                                        Inactive
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Warehouse type -->
                    <div class="sm:col-span-3">
                        <fieldset>
                            <legend class="block text-sm font-medium text-gray-700">Type</legend>
                            <div class="mt-4 space-y-4">
                                <div class="flex items-center">
                                    <input id="type-warehouse" name="is_store" type="radio" value="0" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" {{ old('is_store', $warehouse->is_store) == '0' ? 'checked' : '' }}>
                                    <label for="type-warehouse" class="ml-3 block text-sm font-medium text-gray-700">
                                        Storage warehouse
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="type-store" name="is_store" type="radio" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" {{ old('is_store', $warehouse->is_store) == '1' ? 'checked' : '' }}>
                                    <label for="type-store" class="ml-3 block text-sm font-medium text-gray-700">
                                        Storefront
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Notes -->
                    <div class="sm:col-span-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <div class="mt-1">
                            <textarea id="notes" name="notes" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md py-2.5 text-base h-24">{{ old('notes', $warehouse->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 space-x-3 flex justify-end">
                <a href="{{ route('warehouses.show', $warehouse) }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                    Cancel
                </a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200 transform hover:scale-105">
                    <svg class="h-5 w-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    Update warehouse
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', initializeForm);
    document.addEventListener('turbolinks:load', initializeForm);
    
    function initializeForm() {
        // 防止重复初始化
        if (document.querySelector('.warehouse-form-initialized')) {
            return;
        }
        
        const formContainer = document.querySelector('.warehouse-form');
        if (!formContainer) return;
        
        formContainer.classList.add('warehouse-form-initialized');
        
        // Form fade-in animation
        const formElements = document.querySelectorAll('.warehouse-form .grid > div');
        formElements.forEach((element, index) => {
            element.classList.add('animate__animated', 'animate__fadeInUp');
            element.style.animationDelay = `${index * 0.05}s`;
        });
        
        // Form validation effect
        const requiredInputs = document.querySelectorAll('input[required]');
        requiredInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('border-red-300');
                    this.classList.add('animate__headShake');
                    this.parentElement.classList.add('animate__animated', 'animate__headShake');
                    setTimeout(() => {
                        this.parentElement.classList.remove('animate__animated', 'animate__headShake');
                    }, 1000);
                } else {
                    this.classList.remove('border-red-300');
                }
            });
        });
    }
</script>

<style>
    /* Adding animation CSS */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes headShake {
        0% { transform: translateX(0); }
        6.5% { transform: translateX(-6px) rotateY(-9deg); }
        18.5% { transform: translateX(5px) rotateY(7deg); }
        31.5% { transform: translateX(-3px) rotateY(-5deg); }
        43.5% { transform: translateX(2px) rotateY(3deg); }
        50% { transform: translateX(0); }
    }
    
    .animate__animated {
        animation-duration: 0.5s;
        animation-fill-mode: both;
    }
    
    .animate__fadeIn {
        animation-name: fadeIn;
    }
    
    .animate__fadeInUp {
        animation-name: fadeInUp;
    }
    
    .animate__headShake {
        animation-name: headShake;
        animation-timing-function: ease-in-out;
    }
    
    /* Input styling for better readability */
    input[type="text"], textarea {
        font-size: 0.95rem !important;
        line-height: 1.5 !important;
    }
    
    .form-checkbox {
        width: 1.2rem !important;
        height: 1.2rem !important;
    }
    
    /* Fix for icon visibility */
    .absolute.inset-y-0.left-0.pl-3.flex.items-center.pointer-events-none {
        z-index: 1;
    }
    
    .rounded-md.shadow-sm {
        position: relative;
    }
</style>
@endpush
@endsection 