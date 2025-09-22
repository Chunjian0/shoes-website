@extends('layouts.app')

@section('title', __('Edit Purchase Order'))

@push('styles')
<style>
/* Basic style */
.page-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 2rem;
}

/* Table style optimization */
.order-items-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.order-items-table th {
    background-color: #f3f4f6;
    padding: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    color: #374151;
    text-align: left;
    white-space: nowrap;
}

.order-items-table td {
    padding: 1rem;
    vertical-align: top;
    border-bottom: 1px solid #e5e7eb;
}

.order-items-table tbody tr:hover {
    background-color: #f9fafb;
}

/* Input box style optimization */
.table-input {
    width: 120px;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.table-input:focus {
    outline: none;
    border-color: #6366f1;
    ring: 2px;
    ring-color: #e0e7ff;
}

/* Modal box style optimization */
.modal {
    transition: opacity 0.25s ease;
}

.modal-backdrop {
    transition: opacity 0.25s ease;
    background-color: rgba(0, 0, 0, 0.75);
}

.modal-content {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-body {
    max-height: calc(90vh - 200px);
    overflow-y: auto;
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
    padding: 1rem 1.5rem;
    background: #f9fafb;
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

/* Card style optimization */
.card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    margin-bottom: 1.5rem;
}

.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background-color: #f9fafb;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* Button style optimization */
.btn-primary {
    background-color: #6366f1;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary:hover {
    background-color: #4f46e5;
}

.btn-secondary {
    background-color: white;
    color: #374151;
    border: 1px solid #d1d5db;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background-color: #f9fafb;
}

/* Table container style */
.table-container {
    overflow-x: auto;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    margin: 1rem 0;
}

/* Order summary style */
.order-summary {
    position: sticky;
    top: 1rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    padding: 1.5rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.summary-value {
    font-weight: 500;
    color: #111827;
}

/* Responsive Optimization */
@media (max-width: 1024px) {
    .page-container {
        padding: 1rem;
    }
    
    .table-input {
        width: 100px;
    }
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
    }
    
    .table-input {
        width: 80px;
    }
}
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Edit Purchase Order') }}</h1>
        <a href="{{ route('purchases.index') }}" class="btn-secondary">
            <span class="mr-2">←</span>{{ __('Back') }}
        </a>
    </div>

    <form action="{{ route('purchases.update', $purchase) }}" method="POST" id="purchaseForm">
            @csrf
            @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Basic information -->
            <div class="lg:col-span-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-lg font-medium">{{ __('Basic Information') }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Supplier selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required">{{ __('Supplier') }}</label>
                                <div class="mt-1 flex">
                                    <input type="text" 
                                        class="flex-1 rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                        id="supplierInput" 
                                        value="{{ $purchase->supplier->name }} ({{ $purchase->supplier->code }})"
                                        readonly required>
                                    <input type="hidden" name="supplier_id" id="supplierId" value="{{ $purchase->supplier_id }}">
                                    <button type="button" id="selectSupplierBtn" class="btn-secondary rounded-l-none border-l-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                        </button>
                    </div>
                </div>

                    <!-- Warehouse selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required">{{ __('Warehouse') }}</label>
                                <div class="mt-1 flex">
                                    <input type="text" 
                                        class="flex-1 rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                        id="warehouseInput" 
                                        value="{{ $purchase->warehouse->name }} ({{ $purchase->warehouse->code }})"
                                        readonly required>
                                    <input type="hidden" name="warehouse_id" id="warehouseId" value="{{ $purchase->warehouse_id }}">
                                    <button type="button" id="selectWarehouseBtn" class="btn-secondary rounded-l-none border-l-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </button>
                        </div>
                    </div>

                            <!-- Purchase date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 required">{{ __('Purchase Date') }}</label>
                                <input type="date" 
                                    name="purchase_date" 
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                            value="{{ $purchase->purchase_date->format('Y-m-d') }}"
                                    required>
                </div>

                            <!-- Shipping fee -->
                <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Shipping Fee') }}</label>
                                <input type="number" 
                                    name="shipping_fee" 
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                    min="0" 
                                    step="0.01" 
                                    value="{{ $purchase->shipping_fee }}">
            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
                                <textarea 
                                    name="notes" 
                                    rows="3" 
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ $purchase->notes }}</textarea>
                            </div>
                        </div>
                    </div>
                        </div>
                    </div>

            <!-- Right: Order Summary -->
            <div class="lg:col-span-4">
                <div class="order-summary">
                    <h2 class="text-lg font-medium mb-4">{{ __('Order Summary') }}</h2>
                    <div class="space-y-4">
                        <div class="summary-item">
                            <span class="summary-label">{{ __('Subtotal') }}</span>
                            <span class="summary-value" id="subtotal">{{ number_format($purchase->total_amount, 2) }}</span>
                            </div>
                        <div class="summary-item">
                            <span class="summary-label">{{ __('Tax') }}</span>
                            <span class="summary-value" id="total_tax">{{ number_format($purchase->tax_amount, 2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">{{ __('Shipping') }}</span>
                            <span class="summary-value" id="shipping">{{ number_format($purchase->shipping_fee, 2) }}</span>
                    </div>
                        <div class="summary-item">
                            <span class="summary-label">{{ __('Total Discount') }}</span>
                            <span class="summary-value" id="total_discount">{{ number_format($purchase->discount_amount, 2) }}</span>
                            </div>
                        <div class="summary-item">
                            <span class="summary-label font-medium text-lg">{{ __('Total') }}</span>
                            <span class="summary-value font-bold text-lg" id="total">{{ number_format($purchase->final_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="w-full btn-primary">
                            {{ __('Update Purchase Order') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Order items -->
            <div class="lg:col-span-12">
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <h2 class="text-lg font-medium">{{ __('Order Items') }}</h2>
                        <button type="button" id="addProductBtn" class="btn-primary">
                            <span class="mr-2">+</span>{{ __('Add Product') }}
                </button>
            </div>
                    <div class="card-body p-0">
                        <div class="table-container">
                            <table class="order-items-table">
                                <thead>
                                    <tr>
                                        <th class="w-1/4">{{ __('Product') }}</th>
                                        <th class="w-1/8 text-center">{{ __('Quantity') }}</th>
                                        <th class="w-1/8 text-right">{{ __('Unit Price') }}</th>
                                        <th class="w-1/8 text-right">{{ __('Tax Rate') }}</th>
                                        <th class="w-1/8 text-right">{{ __('Tax Amount') }}</th>
                                        <th class="w-1/8 text-right">{{ __('Discount') }}</th>
                                        <th class="w-1/8 text-right">{{ __('Subtotal') }}</th>
                                        <th class="w-16"></th>
                                    </tr>
                                </thead>
                                <tbody id="orderItemsContainer"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Supplier Select Modal Box -->
<div id="supplierModal" class="modal fixed inset-0 z-50 hidden">
    <div class="modal-backdrop fixed inset-0"></div>
    <div class="modal-content">
        <div class="border-b px-6 py-4">
            <h3 class="text-lg font-medium text-gray-900">{{ __('Select Supplier') }}</h3>
                        </div>
        <div class="modal-body">
            <input type="text" id="supplierSearch" class="w-full px-4 py-2 border rounded-md" placeholder="{{ __('Search supplier...') }}">
            <div class="mt-4 divide-y divide-gray-200" id="suppliersList"></div>
                            </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary modal-close">{{ __('Close') }}</button>
                        </div>
                    </div>
                </div>

<!-- Warehouse Select Modal Box -->
<div id="warehouseModal" class="modal fixed inset-0 z-50 hidden">
    <div class="modal-backdrop fixed inset-0"></div>
    <div class="modal-content">
        <div class="border-b px-6 py-4">
            <h3 class="text-lg font-medium text-gray-900">{{ __('Select Warehouse') }}</h3>
            </div>
        <div class="modal-body">
            <input type="text" id="warehouseSearch" class="w-full px-4 py-2 border rounded-md" placeholder="{{ __('Search warehouse...') }}">
            <div class="mt-4 divide-y divide-gray-200" id="warehousesList"></div>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary modal-close">{{ __('Close') }}</button>
        </div>
    </div>
</div>

<!-- Product Select Modal Box -->
<div id="productModal" class="modal fixed inset-0 z-50 hidden">
    <div class="modal-backdrop fixed inset-0"></div>
    <div class="modal-content">
        <div class="border-b px-6 py-4">
            <h3 class="text-lg font-medium text-gray-900">{{ __('Select Product') }}</h3>
            </div>
        <div class="modal-body">
            <input type="text" id="productSearch" class="w-full px-4 py-2 border rounded-md" placeholder="{{ __('Search product...') }}">
            <div class="mt-4 divide-y divide-gray-200" id="productsList"></div>
                </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary modal-close">{{ __('Close') }}</button>
                </div>
            </div>
            </div>

@endsection

@push('scripts')
<script>
    // Global variables
    let allSuppliers = @json($suppliers);
    let allWarehouses = @json($warehouses);
    let allProducts = [];
    let selectedProducts = new Set();
    let purchaseItems = @json($purchase->items);

    // Initialize form data
    document.addEventListener('DOMContentLoaded', async () => {
        console.log('Initialize form data');
        
        // Load supplier products
        if (supplierId.value) {
            console.log('Loading the supplier product:', supplierId.value);
            await loadSupplierProducts(supplierId.value);
            
            // Initialize purchase items
            console.log('Initialized procurement details:', purchaseItems);
    purchaseItems.forEach(item => {
                const product = {
                    id: item.product_id,
                    name: item.product.name,
                    code: item.product.code,
                    cost_price: item.unit_price,
                    tax_rate: item.tax_rate
                };
                addProduct(product, item);
            });
        }
    });

    // DOM Elements
    const supplierModal = document.getElementById('supplierModal');
    const warehouseModal = document.getElementById('warehouseModal');
    const productModal = document.getElementById('productModal');

    const suppliersList = document.getElementById('suppliersList');
    const warehousesList = document.getElementById('warehousesList');
    const productsList = document.getElementById('productsList');

    const supplierSearch = document.getElementById('supplierSearch');
    const warehouseSearch = document.getElementById('warehouseSearch');
    const productSearch = document.getElementById('productSearch');

    const selectSupplierBtn = document.getElementById('selectSupplierBtn');
    const selectWarehouseBtn = document.getElementById('selectWarehouseBtn');
    const addProductBtn = document.getElementById('addProductBtn');

    const supplierInput = document.getElementById('supplierInput');
    const supplierId = document.getElementById('supplierId');
    const warehouseInput = document.getElementById('warehouseInput');
    const warehouseId = document.getElementById('warehouseId');

    const orderItemsContainer = document.getElementById('orderItemsContainer');

    // Modal functions
    function showModal(modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function hideModal(modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-backdrop')) {
            hideModal(event.target.closest('.modal'));
        }
    }

    // Close modal when pressing ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                if (modal.style.display === 'block') {
                    hideModal(modal);
                }
            });
        }
    });

    // Add click event listeners to close buttons
    document.querySelectorAll('.modal-close').forEach(button => {
        button.addEventListener('click', () => {
            hideModal(button.closest('.modal'));
        });
    });

    // Event listeners
    selectSupplierBtn.addEventListener('click', () => {
        filterAndRenderSuppliers();
        showModal(supplierModal);
    });

    selectWarehouseBtn.addEventListener('click', () => {
        filterAndRenderWarehouses();
        showModal(warehouseModal);
    });

    addProductBtn.addEventListener('click', () => {
        filterAndRenderProducts();
        showModal(productModal);
    });

    // Supplier functions
    async function loadSuppliers() {
        try {
            const response = await fetch(`/api/suppliers-list`);
            if (!response.ok) throw new Error('Failed to load suppliers');
            const result = await response.json();
            if (result.status === 'success') {
                allSuppliers = result.data;
            } else {
                throw new Error(result.message || 'Failed to load suppliers');
            }
        } catch (error) {
            console.error('Error loading suppliers:', error);
            showNotification('error', '{{ __("Failed to load suppliers. Please try again.") }}');
        }
    }

    function filterAndRenderSuppliers(searchTerm = '') {
        const filtered = allSuppliers.filter(supplier => 
            supplier.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            supplier.code.toLowerCase().includes(searchTerm.toLowerCase()) ||
            (supplier.contact_person && supplier.contact_person.toLowerCase().includes(searchTerm.toLowerCase())) ||
            (supplier.phone && supplier.phone.toLowerCase().includes(searchTerm.toLowerCase()))
        );
        renderSuppliers(filtered);
    }

    function renderSuppliers(suppliers) {
        suppliersList.innerHTML = suppliers.map(supplier => `
            <button type="button" class="w-full px-4 py-3 text-left hover:bg-gray-50 focus:outline-none" data-id="${supplier.id}">
                <div class="flex justify-between items-start">
                <div>
                        <div class="font-medium text-gray-900">${supplier.name}</div>
                        <div class="text-sm text-gray-500">${supplier.code}</div>
                        ${supplier.contact_person ? `<div class="text-sm text-gray-500">Contact: ${supplier.contact_person}</div>` : ''}
                    </div>
                    ${supplier.phone ? `<div class="text-sm text-gray-500">${supplier.phone}</div>` : ''}
                </div>
            </button>
        `).join('');

        // Add click event listeners
        suppliersList.querySelectorAll('button').forEach(item => {
            item.addEventListener('click', () => {
                const supplier = allSuppliers.find(s => s.id === parseInt(item.dataset.id));
                selectSupplier(supplier);
            });
        });
    }

    function selectSupplier(supplier) {
        supplierInput.value = `${supplier.name} (${supplier.code})`;
        supplierId.value = supplier.id;
        hideModal(supplierModal);
        loadSupplierProducts(supplier.id);
        addProductBtn.disabled = false;
    }

    // Warehouse functions
    async function loadWarehouses() {
        try {
            const response = await fetch('/api/warehouses-list');
            if (!response.ok) throw new Error('Failed to load warehouses');
            const result = await response.json();
            if (result.status === 'success') {
                allWarehouses = result.data;
            } else {
                throw new Error(result.message || 'Failed to load warehouses');
            }
        } catch (error) {
            console.error('Error loading warehouses:', error);
            showNotification('error', '{{ __("Failed to load warehouses. Please try again.") }}');
        }
    }

    function filterAndRenderWarehouses(searchTerm = '') {
        const filtered = allWarehouses.filter(warehouse => 
            warehouse.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            warehouse.code.toLowerCase().includes(searchTerm.toLowerCase()) ||
            (warehouse.location && warehouse.location.toLowerCase().includes(searchTerm.toLowerCase()))
        );
        renderWarehouses(filtered);
    }

    function renderWarehouses(warehouses) {
        warehousesList.innerHTML = warehouses.map(warehouse => `
            <button type="button" class="w-full px-4 py-3 text-left hover:bg-gray-50 focus:outline-none" data-id="${warehouse.id}">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-medium text-gray-900">${warehouse.name}</div>
                        <div class="text-sm text-gray-500">${warehouse.code}</div>
                        ${warehouse.location ? `<div class="text-sm text-gray-500">Location: ${warehouse.location}</div>` : ''}
                    </div>
                </div>
            </button>
        `).join('');

        // Add click event listeners
        warehousesList.querySelectorAll('button').forEach(item => {
            item.addEventListener('click', () => {
                const warehouse = allWarehouses.find(w => w.id === parseInt(item.dataset.id));
                selectWarehouse(warehouse);
            });
        });
    }

    function selectWarehouse(warehouse) {
        warehouseInput.value = `${warehouse.name} (${warehouse.code})`;
        warehouseId.value = warehouse.id;
        hideModal(warehouseModal);
    }

    // Product functions
    async function loadSupplierProducts(supplierId) {
        try {
            console.log('Start loading the supplier product:', supplierId);
            const response = await fetch(`/api/suppliers/${supplierId}/products`);
            if (!response.ok) throw new Error('Failed to load the product');
            const result = await response.json();
            console.log('APIReturned product data:', result);
            
            if (result.status === 'success') {
                allProducts = result.data;
                console.log('Processed product data:', allProducts);
                filterAndRenderProducts();
            } else {
                throw new Error(result.message || 'Failed to load the product');
            }
        } catch (error) {
            console.error('An error occurred while loading the product:', error);
            showNotification('error', 'Failed to load the product, please try again');
        }
    }

    function filterAndRenderProducts(searchTerm = '') {
        if (!allProducts || !Array.isArray(allProducts)) {
            productsList.innerHTML = '<div class="p-4 text-center text-gray-500">No products available</div>';
            return;
        }

        const filtered = allProducts.filter(product => {
            if (!product) return false;
            
            const searchLower = (searchTerm || '').toLowerCase();
            const name = (product.name || '').toLowerCase();
            const code = (product.code || '').toLowerCase();
            const category = (product.category?.name || '').toLowerCase();
            
            return name.includes(searchLower) || 
                   code.includes(searchLower) || 
                   category.includes(searchLower);
        });
        
        renderProducts(filtered);
    }

    function renderProducts(products) {
        console.log('Start rendering product list:', products);
        if (!products || !Array.isArray(products) || products.length === 0) {
            productsList.innerHTML = '<div class="p-4 text-center text-gray-500">No related products were found</div>';
            return;
        }

        productsList.innerHTML = products.map(product => {
            console.log('Rendering the product:', product);
            if (!product) return '';
            
            const name = product.name || 'Unknown products';
            const code = product.code || 'No encoding';
            const sku = product.sku || 'noneSKU';
            const category = product.category?.name || 'Uncategorized';
            const purchasePrice = product.cost_price ? parseFloat(product.cost_price).toFixed(2) : '0.00';
            const sellingPrice = product.selling_price ? parseFloat(product.selling_price).toFixed(2) : '0.00';
            
            return `
                <button type="button" class="w-full px-4 py-3 text-left hover:bg-gray-50 focus:outline-none" data-id="${product.id}">
                        <div class="flex justify-between items-start">
                        <div>
                            <div class="font-medium text-gray-900">${name}</div>
                            <div class="text-sm text-gray-500">Product Code: ${code}</div>
                            <div class="text-sm text-gray-500">SKU: ${sku}</div>
                            <div class="text-sm text-gray-500">Classification: ${category}</div>
                            </div>
                            <div class="text-right">
                            <div class="text-sm font-medium text-red-600">Purchase price: RM ${purchasePrice}</div>
                            <div class="text-sm font-medium text-green-600">Sales price: RM ${sellingPrice}</div>
                        </div>
                        </div>
                </button>
            `;
        }).join('');

        // Add click event listeners
        productsList.querySelectorAll('button').forEach(item => {
            item.addEventListener('click', () => {
                const product = allProducts.find(p => p && p.id === parseInt(item.dataset.id));
                if (product) {
                    console.log('Selected product:', product);
                        selectProduct(product);
                }
            });
        });
    }

    function selectProduct(product) {
        console.log('Selected product:', product);
        
        // Get the current quantity
        const quantity = parseFloat($('#quantity').val()) || 1;
        
        // Initialize price variables
        let unitPrice = parseFloat(product.cost_price);
        let priceAgreementText = '';
        
        // Check if there's a valid price agreement
        if (product.price_agreement) {
            console.log('Found price agreement:', product.price_agreement);
            if (quantity >= product.price_agreement.min_quantity) {
                unitPrice = parseFloat(product.price_agreement.price);
                priceAgreementText = `Price agreement applied: ${product.price_agreement.min_quantity}+ units at RM${unitPrice}`;
            } else {
                console.log('Quantity does not meet minimum requirement for price agreement');
                priceAgreementText = 'No applicable price agreement was found';
            }
        } else {
            console.log('No price agreement found, using original price');
            priceAgreementText = 'No price agreement available';
        }
        
        // Update form fields
        $('#unit_price').val(unitPrice.toFixed(2));
        $('#tax_rate').val(product.tax_rate);
        $('#price_agreement_text').text(priceAgreementText);
        
        // Calculate amounts
        calculateAmounts();
        
        // Close modal
        $('#productModal').modal('hide');
    }

    function addProduct(product, existingItem = null) {
        console.log('Add products:', product);
        console.log('Already have details:', existingItem);
        
        selectedProducts.add(product.id);
        const rowId = `product-${product.id}`;
        
        const row = document.createElement('tr');
        row.id = rowId;
        row.innerHTML = `
            <td class="px-6 py-4">
                <input type="hidden" name="items[${rowId}][product_id]" value="${product.id}">
                <div>
                    <div class="font-medium text-gray-900">${product.name}</div>
                    <div class="text-sm text-gray-500">${product.code}</div>
                </div>
            </td>
            <td class="px-6 py-4">
                <input type="number" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center quantity-input" 
                    name="items[${rowId}][quantity]" 
                    value="${existingItem ? existingItem.quantity : (product.min_order_quantity || 1)}" 
                    min="${product.min_order_quantity || 1}" 
                    step="1" required
                    onchange="checkPriceAgreement('${rowId}', ${product.id}, this.value)">
            </td>
            <td class="px-6 py-4">
                <input type="number" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-right price-input" 
                    name="items[${rowId}][unit_price]" 
                    value="${existingItem ? existingItem.unit_price : (product.cost_price || 0)}" 
                    min="0" step="0.01" required>
            </td>
            <td class="px-6 py-4">
                <input type="number" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-right tax-rate-input" 
                    name="items[${rowId}][tax_rate]" 
                    value="${existingItem ? existingItem.tax_rate : (product.tax_rate || 0)}" 
                    min="0" max="100" step="0.01">
            </td>
            <td class="px-6 py-4">
                <input type="number" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-right bg-gray-50" 
                    name="items[${rowId}][tax_amount]" 
                    value="${existingItem ? existingItem.tax_amount : 0}" readonly>
            </td>
            <td class="px-6 py-4">
                <input type="number" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-right discount-input" 
                    name="items[${rowId}][discount_amount]" 
                    value="${existingItem ? existingItem.discount_amount : 0}" min="0" step="0.01">
            </td>
            <td class="px-6 py-4">
                <input type="number" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-right bg-gray-50" 
                    name="items[${rowId}][amount]" 
                    value="${existingItem ? existingItem.total_amount : 0}" readonly>
            </td>
            <td class="px-6 py-4">
                <button type="button" 
                    class="inline-flex items-center p-1.5 border border-transparent rounded-full text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" 
                    onclick="removeProduct('${rowId}')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </td>
        `;

        orderItemsContainer.appendChild(row);
        hideModal(productModal);
        
        // Add event listener
        const inputs = row.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            if (!input.readOnly) {
                input.addEventListener('input', () => calculateRowTotal(rowId));
            }
        });
        
        console.log('Check the price agreement');
        // Check price agreement
        const quantity = existingItem ? existingItem.quantity : (product.min_order_quantity || 1);
        checkPriceAgreement(rowId, product.id, quantity);
        calculateRowTotal(rowId);
    }

    // Check the price agreement
    async function checkPriceAgreement(rowId, productId, quantity) {
        try {
            const supplierId = document.getElementById('supplierId').value;
            if (!supplierId) {
                console.log('Supplier not selected, cannot check price agreement');
                return;
            }

            console.log('Start checking the price agreement:', {
                supplierId,
                productId,
                quantity,
                rowId
            });

            const response = await fetch(`/api/suppliers/${supplierId}/products/${productId}/agreements`);
            if (!response.ok) throw new Error('Failed to obtain price agreement');
            
            const result = await response.json();
            console.log('Price AgreementAPIReturn result:', result);

            const agreements = result.data;
            console.log('List of price agreements obtained:', agreements);

            if (!agreements || agreements.length === 0) {
                console.log('Price agreement not found, use original price');
                const product = allProducts.find(p => p.id === productId);
                if (product) {
                    const priceInput = document.querySelector(`#${rowId} .price-input`);
                    if (priceInput) {
                        priceInput.value = product.cost_price;
                        calculateRowTotal(rowId);
                    }
                }
                return;
            }

            // Sort by minimum number from large to small, select the maximum magnitude protocol that satisfies the current number
            const applicableAgreements = agreements
                .filter(a => a.min_quantity <= quantity)
                .sort((a, b) => b.min_quantity - a.min_quantity);
            
            console.log('Price agreement that meets quantity requirements:', applicableAgreements);

            const applicableAgreement = applicableAgreements[0];
            if (applicableAgreement) {
                console.log('The price agreement to be applied:', applicableAgreement);
                const priceInput = document.querySelector(`#${rowId} .price-input`);
                if (priceInput) {
                    priceInput.value = applicableAgreement.price;
                    calculateRowTotal(rowId);
                }
            } else {
                console.log('No applicable price agreement found, use original price');
                const product = allProducts.find(p => p.id === productId);
                if (product) {
                    const priceInput = document.querySelector(`#${rowId} .price-input`);
                    if (priceInput) {
                        priceInput.value = product.cost_price;
                        calculateRowTotal(rowId);
                    }
                }
            }
        } catch (error) {
            console.error('An error occurred while checking the price agreement:', error);
        }
    }

    // Add quantity change monitoring
    function addQuantityChangeListener(rowId) {
        const row = document.getElementById(rowId);
        const quantityInput = row.querySelector('.quantity-input');
        const productId = row.querySelector('input[name$="[product_id]"]').value;

        console.log('Add a quantity change listener:', {
            rowId,
            productId
        });

        quantityInput.addEventListener('change', function() {
            console.log('Number changes:', {
                newQuantity: this.value,
                productId
            });
            checkPriceAgreement(rowId, productId, this.value);
        });
    }

    function removeProduct(rowId) {
        const row = document.getElementById(rowId);
        const productId = parseInt(row.querySelector('input[name$="[product_id]"]').value);
        selectedProducts.delete(productId);
        row.remove();
        calculateOrderTotal();
    }

    function calculateRowTotal(rowId) {
        const row = document.getElementById(rowId);
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const taxRate = parseFloat(row.querySelector('.tax-rate-input').value) || 0;
        const manualDiscount = parseFloat(row.querySelector('.discount-input').value) || 0;
        
        // Get the original price (from product list)
        const productId = parseInt(row.querySelector('input[name$="[product_id]"]').value);
        const originalProduct = allProducts.find(p => p.id === productId);
        const originalPrice = originalProduct ? originalProduct.cost_price : price;
        
        // Calculate the difference between the original total price and the actual total price as a discount
        const originalSubtotal = quantity * originalPrice;
        const actualSubtotal = quantity * price;
        const priceDiscount = Math.max(0, originalSubtotal - actualSubtotal);
        
        // Update the discount input box to display only price discounts
        row.querySelector('.discount-input').value = priceDiscount.toFixed(2);
        
        // Calculate the total amount including tax (Calculate with actual price and no repeated deductions will be made)
        const subtotal = actualSubtotal; // Subtotal calculated directly using actual price
        const taxAmount = subtotal * (taxRate / 100);
        const total = subtotal + taxAmount;

        row.querySelector('input[name$="[tax_amount]"]').value = taxAmount.toFixed(2);
        row.querySelector('input[name$="[amount]"]').value = total.toFixed(2);

        calculateOrderTotal();
    }

    function calculateOrderTotal() {
        let subtotal = 0;
        let totalTax = 0;
        let totalDiscount = 0;
        const shipping = parseFloat(document.querySelector('input[name="shipping_fee"]').value) || 0;

        document.querySelectorAll('#orderItemsContainer tr').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const taxAmount = parseFloat(row.querySelector('input[name$="[tax_amount]"]').value) || 0;
            const discount = parseFloat(row.querySelector('.discount-input').value) || 0;

            // Calculate subtotal using actual price
            subtotal += quantity * price;
            totalTax += taxAmount;
            totalDiscount += discount; // HerediscountIt's already the price difference
        });

        const total = subtotal + totalTax + shipping;

        document.getElementById('subtotal').textContent = formatCurrency(subtotal);
        document.getElementById('total_tax').textContent = formatCurrency(totalTax);
        document.getElementById('shipping').textContent = formatCurrency(shipping);
        document.getElementById('total_discount').textContent = formatCurrency(totalDiscount);
        document.getElementById('total').textContent = formatCurrency(total);
    }

    // Event listeners for search inputs
    supplierSearch.addEventListener('input', debounce((e) => {
        filterAndRenderSuppliers(e.target.value.trim());
    }, 300));

    warehouseSearch.addEventListener('input', debounce((e) => {
        filterAndRenderWarehouses(e.target.value.trim());
    }, 300));

    productSearch.addEventListener('input', debounce((e) => {
        filterAndRenderProducts(e.target.value.trim());
    }, 300));

    document.querySelector('input[name="shipping_fee"]').addEventListener('input', calculateOrderTotal);

    // Utility functions
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('{{ app()->getLocale() }}', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }

    function showNotification(type, message) {
        // use Alpine.js global events to trigger notifications
        window.dispatchEvent(new CustomEvent('notify', {
            detail: {
                type: type,
                message: message
            }
        }));
    }
</script>
@endpush 