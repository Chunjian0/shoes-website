@extends('layouts.app')

@section('title', 'Create quality inspection')

@push('styles')
<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.modal-content {
    position: relative;
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    width: 90%;
    max-width: 800px;
    border-radius: 12px;
    transform: scale(0.7);
    opacity: 0;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.modal.show {
    display: block;
}

.modal.show .modal-content {
    transform: scale(1);
    opacity: 1;
}

.inspection-item {
    opacity: 0;
    transform: translateY(20px);
    animation: slideIn 0.3s ease forwards;
    background: linear-gradient(to right, #f8fafc, #f1f5f9);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.inspection-item:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border-color: #cbd5e1;
}

@keyframes slideIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.error-message {
    display: none;
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem;
    border-radius: 0.5rem;
    background-color: #fee2e2;
    border: 1px solid #fecaca;
    color: #dc2626;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    z-index: 1100;
    max-width: 400px;
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.card-header {
    padding: 1.25rem;
    background: linear-gradient(to right, #f8fafc, #f1f5f9);
    border-bottom: 1px solid #e2e8f0;
}

.card-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.btn-primary {
    background-color: #4f46e5;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary:hover {
    background-color: #4338ca;
}

.btn-secondary {
    background-color: #9ca3af;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background-color: #6b7280;
}
</style>
@endpush

@section('breadcrumb')
    <nav class="text-sm">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li class="flex items-center">
                <a href="{{ route('quality-inspections.index') }}" class="text-gray-500 hover:text-gray-700">Quality Inspection List</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li>
                <span class="text-gray-700">Create quality inspection</span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- 调试工具链接 - 仅在开发环境中显示 -->
    @if(config('app.debug'))
    <div class="mb-4 p-3 bg-gray-100 border border-gray-300 rounded-md">
        <p class="text-sm text-gray-700 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Debugging mode:</span>
            <a href="/debug-quality-inspection.php" target="_blank" class="ml-2 text-indigo-600 hover:text-indigo-900 underline">View submission logs</a>
        </p>
    </div>
    @endif

    <!-- Error message box -->
    <div id="errorMessage" class="error-message"></div>

    <div class="card">
        <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-800">Create quality inspection</h2>
        </div>

        <form id="inspectionForm" action="{{ route('quality-inspections.store') }}" method="POST" class="card-body space-y-6">
            @csrf

            <!-- Basic information card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Basic information</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="purchase_display" class="block text-sm font-medium text-gray-700 mb-2">Purchase Order</label>
                            <div class="relative">
                                <input type="hidden" name="purchase_id" id="purchase_id" value="{{ old('purchase_id') }}">
                                <input type="text" 
                                       id="purchase_display" 
                                       class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer"
                                       placeholder="Click to select a purchase order"
                                       readonly
                                       onclick="openPurchaseModal()">
                                <button type="button" 
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        onclick="openPurchaseModal()">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </button>
                            </div>
                    @error('purchase_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                        <div class="form-group">
                            <label for="inspection_date" class="block text-sm font-medium text-gray-700 mb-2">Inspection date</label>
                            <input type="date" 
                                   name="inspection_date" 
                                   id="inspection_date" 
                                   class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                           value="{{ old('inspection_date', date('Y-m-d')) }}">
                    @error('inspection_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inspection item card -->
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Inspection items</h3>
                    <button type="button" 
                            id="addItemBtn"
                            class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                            onclick="openItemModal()"
                            disabled>
                        Add a project
                    </button>
                </div>
                <div class="card-body">
                    <div id="itemsContainer" class="space-y-6">
                        <!-- Inspection items will be added dynamically here -->
                            </div>

                    <div id="noItemsMessage" class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-2">Please select the purchase order and add inspection items first</p>
                    </div>

                    @error('items')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Notes card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Remark</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remark details</label>
                        <textarea name="remarks" 
                                  id="remarks" 
                                  rows="3" 
                                  class="form-textarea w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Enter inspection remarks here">{{ old('remarks') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Inspection Images -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Inspection Images</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Inspection Images</label>
                        <p class="text-sm text-gray-500 mb-4">Upload images of quality inspection details, defects or product conditions.</p>
                        
                        @php
                            $tempId = Str::random(20);
                        @endphp
                        <input type="hidden" name="temp_id" value="{{ $tempId }}">
                        <x-quality-inspection-image-uploader
                            :temp-id="$tempId"
                            model-type="quality_inspections"
                            :max-files="5"
                            :images="[]"
                        />
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                <a href="{{ route('quality-inspections.index') }}" 
                   class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    keep
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Purchase Order Select Modal Box -->
<div id="purchaseModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Select a purchase order</h3>
            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closePurchaseModal()">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
    </div>
        
        <div class="mb-4">
            <input type="text" 
                   id="purchaseSearch" 
                   class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="Search for purchase order number or supplier..."
                   oninput="filterPurchases()">
        </div>

        <div class="max-h-96 overflow-y-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inspection status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">operate</th>
                    </tr>
                </thead>
                <tbody id="purchaseList" class="bg-white divide-y divide-gray-200">
                    @foreach ($purchases as $purchase)
                        <tr class="purchase-row hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $purchase->purchase_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($purchase->supplierItems->isNotEmpty())
                                    {{ $purchase->supplierItems->pluck('supplier.name')->unique()->join(', ') }}
                                @else
                                    <span class="text-gray-500">No supplier specified</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $purchase->received_at?->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <dt class="text-sm font-medium text-gray-500">Inspection status</dt>
                                <dd class="mt-1">
                                    @if($purchase->inspection_status)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $purchase->inspection_status->color() }}-100 text-{{ $purchase->inspection_status->color() }}-800">
                                            {{ $purchase->inspection_status->label() }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Not checked
                                        </span>
                                    @endif
                                </dd>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <button type="button"
                                        class="text-indigo-600 hover:text-indigo-900"
                                        onclick="selectPurchase('{{ $purchase->id }}', '{{ $purchase->purchase_number }}')">
                                    choose
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Check item selection modal box -->
<div id="itemModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Add inspection items</h3>
            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeItemModal()">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div id="itemsTableContainer" class="max-h-96 overflow-y-auto rounded-lg border border-gray-200">
            <!-- The procurement project list will be loaded dynamically here -->
            <div id="loadingItems" class="text-center py-8 hidden">
                <div class="loading-spinner"></div>
                <p class="mt-2 text-gray-600">loading...</p>
            </div>
        </div>
    </div>
</div>
@endsection

    @push('scripts')
    <script>
// Global variables and function declarations
const purchaseModal = document.getElementById('purchaseModal');
const itemModal = document.getElementById('itemModal');
const errorMessage = document.getElementById('errorMessage');
const addItemBtn = document.getElementById('addItemBtn');
const itemsContainer = document.getElementById('itemsContainer');
let selectedItems = new Set();

// Show error message
function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    // 滚动到错误消息
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000); // 增加显示时间
}

// Modal control functions
function openPurchaseModal() {
    document.getElementById('purchaseModal').classList.add('show');
}

function closePurchaseModal() {
    document.getElementById('purchaseModal').classList.remove('show');
}

function openItemModal() {
    document.getElementById('itemModal').classList.add('show');
    loadPurchaseItems();
}

function closeItemModal() {
    document.getElementById('itemModal').classList.remove('show');
}

// Select purchase order
function selectPurchase(id, number) {
    document.getElementById('purchase_id').value = id;
    document.getElementById('purchase_display').value = number;
    document.getElementById('addItemBtn').disabled = false;
    selectedItems.clear();
    document.getElementById('itemsContainer').innerHTML = '';
    document.getElementById('noItemsMessage').style.display = 'block';
    closePurchaseModal();
}

// Delete inspection item
function removeInspectionItem(button, itemId) {
    selectedItems.delete(itemId);
    const itemElement = button.closest('.inspection-item');
    itemElement.style.opacity = '0';
    itemElement.style.transform = 'translateY(20px)';
    setTimeout(() => {
        itemElement.remove();
    }, 300); // Add animation duration
}

// Loading the procurement project
function loadPurchaseItems() {
    const purchaseId = document.getElementById('purchase_id').value;
    if (!purchaseId) return;

    const loadingElement = document.getElementById('loadingItems');
    const tableContainer = document.getElementById('itemsTableContainer');
    
    loadingElement.classList.remove('hidden');
    tableContainer.innerHTML = '';

    fetch(`/purchases/${purchaseId}/items`)
        .then(response => response.json())
        .then(data => {
            loadingElement.classList.add('hidden');
            console.log('Server response:', data); // Add debug log

            // Check for error messages
            if (data.error) {
                throw new Error(data.message || 'Loading failed');
            }

            // Make sure the data is an array
            const items = Array.isArray(data) ? data : (data.data || []);
            renderItemsTable(items);
        })
        .catch(error => {
            console.error('Error loading items:', error); // Add error log
            loadingElement.classList.add('hidden');
            tableContainer.innerHTML = `
                <div class="text-center py-8 text-red-600">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="mt-2">Loading failed: ${error.message}</p>
                </div>
            `;
        });
}

// Rendering procurement project form
function renderItemsTable(items) {
    const tableContainer = document.getElementById('itemsTableContainer');
    
    // make sure items It's an array
    if (!Array.isArray(items)) {
        console.error('Invalid items data:', items);
        tableContainer.innerHTML = `
            <div class="text-center py-8 text-red-600">
                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="mt-2">Data format error</p>
            </div>
        `;
        return;
    }
    
    if (items.length === 0) {
        tableContainer.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="mt-2">No verifiable items</p>
            </div>
        `;
        return;
    }

    try {
        const table = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Information</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qualified quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">operate</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${items.map(item => {
                        // Verify the necessary properties
                        if (!item || !item.product_name) {
                            console.error('Invalid item data:', item);
                            return '';
                        }
                        
                        return `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="font-medium">${item.product_name}</div>
                                    <div class="text-xs text-gray-500">
                                        Remaining quantity: ${item.remaining_quantity || 0}
                                        ${item.received_quantity ? `(Received: ${item.received_quantity})` : ''}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.remaining_quantity || 0}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.product_sku || '-'}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <button type="button"
                                            class="text-indigo-600 hover:text-indigo-900"
                                            onclick='addInspectionItem(${JSON.stringify(item)})'>
                                        choose
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;

        tableContainer.innerHTML = table;
    } catch (error) {
        console.error('Error rendering table:', error);
        tableContainer.innerHTML = `
            <div class="text-center py-8 text-red-600">
                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="mt-2">An error occurred while rendering data: ${error.message}</p>
            </div>
        `;
    }
}

// Add inspection items
function addInspectionItem(item) {
    if (selectedItems.has(item.id)) {
        showError('This project has been added');
                        return;
                    }

    selectedItems.add(item.id);
    document.getElementById('noItemsMessage').style.display = 'none';

    const itemElement = document.createElement('div');
    itemElement.className = 'inspection-item p-6 rounded-lg';
    itemElement.innerHTML = `
        <input type="hidden" name="items[${item.id}][purchase_item_id]" value="${item.id}">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded border border-gray-200">${item.product_name}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Number of inspections</label>
                <input type="number"
                       name="items[${item.id}][inspected_quantity]"
                       class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                       min="1"
                       max="${item.remaining_quantity}"
                       value="${item.remaining_quantity}"
                       oninput="updateQuantities(this)"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Qualified quantity</label>
                <input type="number"
                       name="items[${item.id}][passed_quantity]"
                       class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                       min="0"
                       value="${item.remaining_quantity}"
                       oninput="updateFailedQuantity(this)"
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Unqualified quantity</label>
                <input type="number"
                       name="items[${item.id}][failed_quantity]"
                       class="form-input w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                       min="0"
                       value="0"
                       oninput="updatePassedQuantity(this)"
                       required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Defect description</label>
                <textarea name="items[${item.id}][defect_description]"
                          class="form-textarea w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                          rows="2"
                          placeholder="Please describe product defects (if any)"></textarea>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button type="button"
                    class="inline-flex items-center text-red-600 hover:text-red-900"
                    onclick="removeInspectionItem(this, ${item.id})">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                delete
            </button>
        </div>
    `;

    document.getElementById('itemsContainer').appendChild(itemElement);
    closeItemModal();
}

// Processing when updating the inspection quantity
function updateQuantities(input) {
    const itemContainer = input.closest('.inspection-item');
    const inspectedQuantity = parseInt(input.value) || 0;
    const passedInput = itemContainer.querySelector('input[name$="[passed_quantity]"]');
    const failedInput = itemContainer.querySelector('input[name$="[failed_quantity]"]');
    
    // If the inspection quantity is less than the sum of the current qualified quantity and the unqualified quantity, the qualified quantity will be reduced first.
    const currentFailed = parseInt(failedInput.value) || 0;
    const maxPassed = Math.max(0, inspectedQuantity - currentFailed);
    passedInput.value = maxPassed;
    
    // Update input limits
    passedInput.max = inspectedQuantity;
    failedInput.max = inspectedQuantity;
}

// Processing when updating qualified quantity
function updateFailedQuantity(input) {
    const itemContainer = input.closest('.inspection-item');
    const inspectedInput = itemContainer.querySelector('input[name$="[inspected_quantity]"]');
    const failedInput = itemContainer.querySelector('input[name$="[failed_quantity]"]');
    
    const inspectedQuantity = parseInt(inspectedInput.value) || 0;
    const passedQuantity = parseInt(input.value) || 0;
    
    // Automatically calculate the number of unqualified
    failedInput.value = Math.max(0, inspectedQuantity - passedQuantity);
}

// Processing when updating unqualified quantities
function updatePassedQuantity(input) {
    const itemContainer = input.closest('.inspection-item');
    const inspectedInput = itemContainer.querySelector('input[name$="[inspected_quantity]"]');
    const passedInput = itemContainer.querySelector('input[name$="[passed_quantity]"]');
    
    const inspectedQuantity = parseInt(inspectedInput.value) || 0;
    const failedQuantity = parseInt(input.value) || 0;
    
    // Automatically calculate the qualified quantity
    passedInput.value = Math.max(0, inspectedQuantity - failedQuantity);
}

// Bind all functions to window Object
Object.assign(window, {
    openPurchaseModal,
    closePurchaseModal,
    openItemModal,
    closeItemModal,
    selectPurchase,
    removeInspectionItem,
    showError,
    loadPurchaseItems,
    renderItemsTable,
    addInspectionItem,
    updateQuantities,
    updateFailedQuantity,
    updatePassedQuantity
});

// Event Listener
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    document.getElementById('inspectionForm')?.addEventListener('submit', function(event) {
        event.preventDefault();
        
        if (selectedItems.size === 0) {
            showError('Please add at least one inspection item');
            return;
        }
        
        let hasError = false;
        document.querySelectorAll('.inspection-item').forEach(item => {
            const inspectedQty = parseInt(item.querySelector('input[name$="[inspected_quantity]"]').value) || 0;
            const passedQty = parseInt(item.querySelector('input[name$="[passed_quantity]"]').value) || 0;
            const failedQty = parseInt(item.querySelector('input[name$="[failed_quantity]"]').value) || 0;
            
            if (passedQty + failedQty !== inspectedQty) {
                showError('The sum of passed and failed quantities must equal the inspection quantity');
                hasError = true;
            }
        });
        
        if (!hasError) {
            // 添加提交状态指示
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading-spinner mr-2"></span> Submitting...';
            
            // 显示表单数据用于调试
            console.log('Form data being submitted:', {
                purchase_id: document.getElementById('purchase_id').value,
                inspection_date: document.getElementById('inspection_date').value,
                remarks: document.getElementById('remarks').value,
                items: Array.from(selectedItems),
                temp_id: document.querySelector('input[name="temp_id"]').value
            });
            
            // 使用fetch API提交
            console.log('Submitting form to:', this.action);
            
            // 先发送一份到调试脚本
            const formData = new FormData(this);
            fetch('/debug-quality-inspection.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Debug data logged:', data);
                
                // 然后发送到实际处理脚本
                return fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Form submission failed');
                    });
                }
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.json();
                }
            })
            .then(data => {
                console.log('Submission success:', data);
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    // 在不重定向的情况下显示成功消息
                    showSuccess('Quality inspection record has been created successfully');
                    // 3秒后重定向到列表页面
                    setTimeout(() => {
                        window.location.href = '{{ route('quality-inspections.index') }}';
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Submission error:', error);
                showError('Error submitting form: ' + error.message);
                // 恢复提交按钮状态
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        }
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === purchaseModal) {
            closePurchaseModal();
        }
        if (event.target === itemModal) {
            closeItemModal();
        }
    };

    // Close modal on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePurchaseModal();
            closeItemModal();
        }
    });
});

// 添加一个成功消息显示函数
function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'fixed top-5 right-5 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg shadow-md z-50';
    successDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(successDiv);
    setTimeout(() => {
        successDiv.remove();
    }, 5000);
}
    </script>
    @endpush