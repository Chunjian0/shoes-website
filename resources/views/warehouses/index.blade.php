@extends('layouts.app')

@section('title', 'Warehouse management')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page title and action buttons -->
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate flex items-center">
                <svg class="w-8 h-8 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Warehouse management
            </h2>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            @can('create', App\Models\Warehouse::class)
                <a href="{{ route('warehouses.create') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 transform hover:scale-105">
                    <svg class="h-5 w-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New warehouse
                </a>
            @endcan
        </div>
    </div>

    <!-- Successful news -->
    @if (session('success'))
        <div class="rounded-md bg-green-50 p-4 mb-6 animate__animated animate__fadeIn">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Search and filter section -->
    <div class="bg-white rounded-lg shadow-sm mb-6 p-4 border border-gray-200 transform transition-all duration-200 hover:shadow-md">
        <div class="flex flex-col sm:flex-row justify-between items-center">
            <div class="w-full sm:w-1/2 mb-4 sm:mb-0">
                <div class="relative">
                    <input type="text" id="warehouse-search" placeholder="Search warehouses..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="w-full sm:w-auto flex space-x-2">
                <select id="status-filter" class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="all">All statuses</option>
                    <option value="active">Active only</option>
                    <option value="inactive">Inactive only</option>
                </select>
                <select id="type-filter" class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="all">All types</option>
                    <option value="store">Storefront only</option>
                    <option value="warehouse">Warehouse only</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Warehouse list -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100 transition-all duration-300 hover:border-gray-200">
        <ul class="divide-y divide-gray-200" id="warehouse-list">
            @forelse ($warehouses as $warehouse)
                <li class="warehouse-item transform transition-all duration-300 hover:bg-gray-50" 
                    data-status="{{ $warehouse->status ? 'active' : 'inactive' }}"
                    data-type="{{ $warehouse->is_store ? 'store' : 'warehouse' }}"
                    data-name="{{ $warehouse->name }}"
                    data-code="{{ $warehouse->code }}">
                    <div class="px-4 py-5 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-lg font-medium text-indigo-600 truncate">
                                        {{ $warehouse->name }}
                                    </p>
                                    <span class="ml-2 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $warehouse->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $warehouse->status ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="ml-2 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $warehouse->is_store ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $warehouse->is_store ? 'Storefront' : 'Warehouse' }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex items-center space-x-2">
                                @can('view', $warehouse)
                                    <a href="{{ route('warehouses.show', $warehouse) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View
                                    </a>
                                @endcan
                                @can('update', $warehouse)
                                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </a>
                                @endcan
                                @can('delete', $warehouse)
                                    <button type="button" 
                                        class="delete-warehouse inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                        data-id="{{ $warehouse->id }}"
                                        data-name="{{ $warehouse->name }}">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                    <form id="delete-form-{{ $warehouse->id }}" action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endcan
                            </div>
                        </div>
                        <div class="mt-4 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-700">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="truncate font-medium">Code: {{ $warehouse->code }}</span>
                                </p>
                                @if($warehouse->contact_person)
                                    <p class="mt-2 flex items-center text-sm text-gray-700 sm:mt-0 sm:ml-6">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span class="truncate">{{ $warehouse->contact_person }}</span>
                                    </p>
                                @endif
                                @if($warehouse->contact_phone)
                                    <p class="mt-2 flex items-center text-sm text-gray-700 sm:mt-0 sm:ml-6">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span class="truncate">{{ $warehouse->contact_phone }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                        @if($warehouse->address)
                            <div class="mt-2">
                                <p class="flex items-center text-sm text-gray-700">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $warehouse->address }}
                                </p>
                            </div>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-4 py-12 sm:px-6 animate__animated animate__fadeIn">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No warehouse data yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new warehouse.</p>
                        @can('create', App\Models\Warehouse::class)
                            <div class="mt-6">
                                <a href="{{ route('warehouses.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    New warehouse
                                </a>
                            </div>
                        @endcan
                    </div>
                </li>
            @endforelse
        </ul>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $warehouses->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // SweetAlert2 delete confirmation
        const deleteButtons = document.querySelectorAll('.delete-warehouse');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const warehouseId = this.dataset.id;
                const warehouseName = this.dataset.name;
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete the warehouse "${warehouseName}". This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true,
                    customClass: {
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-form-${warehouseId}`).submit();
                    }
                });
            });
        });

        // Search and filter functionality
        const searchInput = document.getElementById('warehouse-search');
        const statusFilter = document.getElementById('status-filter');
        const typeFilter = document.getElementById('type-filter');
        const warehouseItems = document.querySelectorAll('.warehouse-item');

        const filterWarehouses = () => {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const typeValue = typeFilter.value;

            warehouseItems.forEach(item => {
                const warehouseName = item.dataset.name.toLowerCase();
                const warehouseCode = item.dataset.code.toLowerCase();
                const warehouseStatus = item.dataset.status;
                const warehouseType = item.dataset.type;

                const matchesSearch = warehouseName.includes(searchTerm) || warehouseCode.includes(searchTerm);
                const matchesStatus = statusValue === 'all' || warehouseStatus === statusValue;
                const matchesType = typeValue === 'all' || warehouseType === typeValue;

                if (matchesSearch && matchesStatus && matchesType) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        };

        searchInput.addEventListener('input', filterWarehouses);
        statusFilter.addEventListener('change', filterWarehouses);
        typeFilter.addEventListener('change', filterWarehouses);

        // Adding animation classes to rows
        warehouseItems.forEach((item, index) => {
            item.classList.add('animate__animated', 'animate__fadeIn');
            item.style.animationDelay = `${index * 0.05}s`;
        });
    });
</script>

<style>
    /* Adding animation CSS */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate__animated {
        animation-duration: 0.4s;
        animation-fill-mode: both;
    }
    
    .animate__fadeIn {
        animation-name: fadeIn;
    }
    
    /* Hover effects for items */
    .warehouse-item:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    /* SweetAlert2 custom styles */
    .swal2-confirm {
        padding-left: 1.5rem !important;
        padding-right: 1.5rem !important;
    }
    
    .swal2-cancel {
        padding-left: 1.5rem !important;
        padding-right: 1.5rem !important;
    }
</style>
@endpush
@endsection 