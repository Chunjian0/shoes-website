@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('title', 'Create a new purchase order')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between mb-4">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Create a new purchase order
            </h2>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
        @csrf
            <div class="px-4 py-5 sm:p-6">
                <!-- Basic information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700">storehouse</label>
                        <select name="warehouse_id" id="warehouse_id" required
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Please select a warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700">Shipping Address</label>
                            <textarea name="delivery_address" id="delivery_address" rows="2" readonly
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-50 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                    </div>

                    <div>
                        <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase date</label>
                        <input type="datetime-local" 
                               name="purchase_date" 
                               id="purchase_date" 
                               value="{{ old('purchase_date', now()->format('Y-m-d\TH:i')) }}"
                               required
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('purchase_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Product List -->
                <div class="mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Product List</h3>
                        <button type="button" id="addProduct"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Add products
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="productTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">merchandise</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">supplier</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">quantity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">unit price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">tax rate(%)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">operate</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="productRows">
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Supplier Summary -->
                <div class="mt-6" id="supplierSummaryContainer">
                </div>

                <!-- Remark -->
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Remark</label>
                    <textarea name="notes" id="notes" rows="3"
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <a href="{{ route('purchases.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    keep
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Product selection modal box -->
<div id="productModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Select a product
                        </h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="productSearch" class="block text-sm font-medium text-gray-700">Search for products</label>
                                <input type="text" id="productSearch" 
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter a product name、Encoding or barcode search">
                            </div>
                            <div>
                                <label for="categoryFilter" class="block text-sm font-medium text-gray-700">Product Category</label>
                                <select id="categoryFilter" 
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">All categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Add parameter display area -->
                        <div id="categoryParameters" class="mt-4 hidden">
                            <h4 class="text-lg font-medium text-gray-900 mb-2">Classification parameters</h4>
                            <div id="parameterFields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">coding</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classification</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">operate</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productTableBody" class="bg-white divide-y divide-gray-200">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="closeModalBtn"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    closure
                </button>
            </div>
        </div>
    </div>
</div>
@endsection 

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
// Change the global variable to window Object properties
if (!window.purchaseState) {
    window.purchaseState = {
        items: [],
        supplierSummaries: {},
        allProducts: @json($products),
        parameters: [],
        // DOM Element reference
        elements: {}
    };
}

// initialization DOM Element reference
function initializeElements() {
    window.purchaseState.elements = {
        modal: document.getElementById('productModal'),
        productSearch: document.getElementById('productSearch'),
        categoryFilter: document.getElementById('categoryFilter'),
        closeModalBtn: document.getElementById('closeModalBtn'),
        addProductTrigger: document.getElementById('addProduct'),
        warehouseSelect: document.getElementById('warehouse_id'),
        deliveryAddressTextarea: document.getElementById('delivery_address'),
        productRows: document.getElementById('productRows'),
        supplierSummaryContainer: document.getElementById('supplierSummaryContainer'),
        productTableBody: document.getElementById('productTableBody'),
        categoryParameters: document.getElementById('categoryParameters'),
        parameterFields: document.getElementById('parameterFields'),
        purchaseForm: document.getElementById('purchaseForm'),
        purchaseDateInput: document.getElementById('purchase_date'),
        notesTextarea: document.getElementById('notes')
    };
}

// Initialize elements when page loads
document.addEventListener('turbolinks:load', initializeElements);
initializeElements();

// usewindow.purchaseState.elementsReplace direct accessDOMelement
document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    console.group('Purchase Order Form Submission');
    console.log('Start processing form submission events');
    
    // Collect form data
    const formData = {
        warehouse_id: window.purchaseState.elements.warehouseSelect.value,
        purchase_date: window.purchaseState.elements.purchaseDateInput.value,
        notes: window.purchaseState.elements.notesTextarea.value,
        items: window.purchaseState.items.map(item => {
            console.log('Processing item:', item);
            return {
                product_id: item.product_id,
                supplier_id: item.supplier_id,
                quantity: item.quantity,
                unit_price: item.unit_price,
                tax_rate: item.tax_rate,
                total_amount: item.total_amount,
                tax_amount: item.tax_amount
            };
        }),
        supplier_shipping_fee: {},
        supplier_notes: {}
    };

    // Add supplier shipping and notes
    Object.entries(window.purchaseState.supplierSummaries).forEach(([supplierId, summary]) => {
        formData.supplier_shipping_fee[supplierId] = summary.shipping_fee;
        formData.supplier_notes[supplierId] = summary.notes;
        console.log(`supplier ${supplierId} freight:`, summary.shipping_fee);
        console.log(`supplier ${supplierId} Remark:`, summary.notes);
    });
    
    console.log('Prepare to submit complete form data:', {
        formData: formData,
        items: window.purchaseState.items,
        supplierSummaries: window.purchaseState.supplierSummaries
    });

    // Form Verification
    let validationErrors = [];
    
    // Verify warehouse selection
    if(!formData.warehouse_id) {
        console.error('Verification failed: Repository not selected');
        validationErrors.push('Please select a warehouse');
    }

    // Verify product list
    if(window.purchaseState.items.length === 0) {
        console.error('Verification failed: Product list is empty');
        validationErrors.push('Please add at least one product');
    }

    // Verify data for each product
    const invalidItems = window.purchaseState.items.filter(item => {
        const issues = [];
        if(!item.quantity) issues.push('Quantity is empty');
        if(item.quantity < item.min_order_quantity) issues.push('Quantity is less than the minimum order quantity');
        if(!item.unit_price) issues.push('The unit price is short');
        if(item.unit_price <= 0) issues.push('The unit price must be greater than0');
        
        if(issues.length > 0) {
            console.error(`merchandise ${item.product_name} Invalid data:`, issues);
        }
        return issues.length > 0;
    });

    if(invalidItems.length > 0) {
        validationErrors.push('The quantity or unit price of some products is invalid');
    }

    // If there is a verification error
    if(validationErrors.length > 0) {
        console.error('Form verification failed:', validationErrors);
        Swal.fire({
            title: 'Verification error',
            html: validationErrors.join('<br>'),
            icon: 'error'
        });
        console.groupEnd();
        return;
    }

    console.log('The form verification is passed, and the confirmation dialog is ready to be displayed.');

    // Show the confirmation dialog box
    Swal.fire({
        title: 'Confirm Submission',
        text: 'Are you sure you want to submit a purchase order?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sure',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('User confirms submission and prepares to send a form');
            
            // Add before submitting the formCSRFToken
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = document.querySelector('meta[name="csrf-token"]').content;
            this.appendChild(tokenInput);
            
            console.log('AddedCSRFToken:', tokenInput.value);
            console.log('The final submitted form element:', this);
            
            // Submit a form
            this.submit();
        } else {
            console.log('User Cancel Submission');
        }
    });
    
    console.groupEnd();
});

// Initialize event listening
window.purchaseState.elements.warehouseSelect.addEventListener('change', async function(event) {
    console.log('Warehouse selection:', event.target.value);
    await loadWarehouseAddress(event.target.value);
});

window.purchaseState.elements.addProductTrigger.addEventListener('click', function() {
    showModal();
    loadProducts();
});

window.purchaseState.elements.closeModalBtn.addEventListener('click', hideModal);

window.purchaseState.elements.productSearch.addEventListener('input', debounce(function(e) {
    console.log('Search Query:', e.target.value);
    filterProducts();
}, 300));

window.purchaseState.elements.categoryFilter.addEventListener('change', async function() {
    console.log('Category selection:', this.value);
    await loadCategoryParameters(this.value);
    filterProducts();
});

// Load product data
async function loadProducts() {
    try {
        console.log('Start loading product data');
        const response = await fetch('/api/products', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        });
        
        console.log('API Response status:', response.status);
        
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Unauthorized: Please log in again to continue. Your session may have expired.');
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Load product data results:', result);
        
        if (Array.isArray(result)) {
            console.log('Response is an array of products');
            window.purchaseState.allProducts = result;
            filterProducts();
        } else if (result.data && Array.isArray(result.data)) {
            console.log('Response has data array property');
            window.purchaseState.allProducts = result.data;
            filterProducts();
        } else if (result.status === 'success' && result.data) {
            console.log('Response has success status and data property');
            window.purchaseState.allProducts = result.data;
            filterProducts();
        } else {
            console.warn('Unexpected API response format:', result);
            Swal.fire({
                title: 'Data Format Warning',
                text: 'Unexpected response format from server',
                icon: 'warning',
                timer: 2000
            });
            window.purchaseState.allProducts = [];
            filterProducts();
        }
    } catch (error) {
        console.error('Failed to load the product:', error);
        Swal.fire({
            title: 'Error Loading Products',
            text: `Failed to load products: ${error.message}`,
            icon: 'error',
            showConfirmButton: true,
            confirmButtonText: 'Try Again',
            showCancelButton: true,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                loadProducts();
            }
        });
    }
}

// Loading classification parameters
async function loadCategoryParameters(categoryId) {
    if (!categoryId) {
        window.purchaseState.parameters = [];
        renderParameterFields([]);
        return;
    }

    try {
        const response = await fetch(`/api/product-categories/${categoryId}/parameters`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Unauthorized: Please log in again to continue');
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.status === 'success') {
            window.purchaseState.parameters = result.data;
            renderParameterFields(window.purchaseState.parameters);
        }
    } catch (error) {
        console.error('Error loading category parameters:', error);
        window.purchaseState.parameters = [];
        renderParameterFields([]);
        
        // 显示错误提示但不阻断用户体验
        if (error.message.includes('Unauthorized')) {
            console.warn('Authentication issue detected');
        }
    }
}

// Anti-shake function
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

// Repository address loading
async function loadWarehouseAddress(warehouseId) {
    if (!warehouseId) {
        window.purchaseState.elements.deliveryAddressTextarea.value = '';
        return;
    }
    try {
        console.log('Loading warehouse address for ID:', warehouseId);
        const response = await fetch(`/api/warehouses/${warehouseId}/address`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin' // 确保发送认证信息和Cookie
        });
        
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Unauthorized: Please log in again');
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Warehouse address response:', result);
        
        // 检查API响应结构并提取地址
        if (result.status === 'success' && result.data && result.data.address) {
            window.purchaseState.elements.deliveryAddressTextarea.value = result.data.address;
        } else if (result.address) {
            // 兼容旧格式的API响应
            window.purchaseState.elements.deliveryAddressTextarea.value = result.address;
        } else {
            console.warn('Unexpected API response format:', result);
            window.purchaseState.elements.deliveryAddressTextarea.value = 'Address not available';
        }
    } catch (error) {
        console.error('Error loading warehouse address:', error);
        window.purchaseState.elements.deliveryAddressTextarea.value = '';
        
        // 显示错误提示但不阻断用户体验
        if (error.message.includes('Unauthorized')) {
            Swal.fire({
                title: 'Session Warning',
                text: 'Your session may have expired. Please refresh the page.',
                icon: 'warning',
                showConfirmButton: true,
                confirmButtonText: 'Refresh Page',
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        }
    }
}

// 获取参数过滤器
function getParameterFilters() {
    const parameterFilters = [];
    
    // 获取所有参数输入
    const parameterInputs = document.querySelectorAll('#parameterFields input, #parameterFields select');
    parameterInputs.forEach(input => {
        if (input.name && input.name.startsWith('parameters[') && input.value) {
            const paramName = input.name.match(/parameters\[(.*?)\]/)[1];
            parameterFilters.push({
                key: paramName,
                value: input.value
            });
        }
    });
    
    return parameterFilters;
}

// Filter and display products
function filterProducts() {
    console.log('Start filter products');
    const searchQuery = window.purchaseState.elements.productSearch.value.toLowerCase();
    const categoryId = window.purchaseState.elements.categoryFilter.value;
    const parameterFilters = getParameterFilters();
    
    console.log('Filter conditions:', {
        searchQuery,
        categoryId,
        parameterFilters
    });
    
    const filteredProducts = window.purchaseState.allProducts.filter(product => {
        // 过滤没有供应商的产品
        if (!product.suppliers || product.suppliers.length === 0) {
            return false;
        }
        
        // 搜索条件
        const matchesSearch = searchQuery === '' || 
            product.name.toLowerCase().includes(searchQuery) ||
            (product.sku && product.sku.toLowerCase().includes(searchQuery)) ||
            (product.barcode && product.barcode.toLowerCase().includes(searchQuery));
        
        // 分类筛选
        const matchesCategory = categoryId === '' || product.category_id.toString() === categoryId;
        
        // 参数筛选
        const matchesParameters = parameterFilters.length === 0 || parameterFilters.every(filter => {
            const productParamValue = product.parameters && product.parameters[filter.key];
            return productParamValue !== undefined && productParamValue === filter.value;
        });
        
        return matchesSearch && matchesCategory && matchesParameters;
    });

    console.log('Filtered products:', filteredProducts);
    displayProducts(filteredProducts);
}

// Show product list
function displayProducts(products) {
    console.log('Displaying products:', products);
    window.purchaseState.elements.productTableBody.innerHTML = products.map(product => {
        const supplierInfo = product.suppliers.map(supplier => 
            `<div class="text-xs text-gray-500">
                ${supplier.name} - ¥${supplier.purchase_price}
            </div>`
        ).join('');

        return `
            <tr>
                <td class="px-6 py-4 text-sm text-gray-900">
            <div class="font-medium">${product.name}</div>
                    <div class="text-xs text-gray-500">SKU: ${product.sku}</div>
                    <div class="mt-1">${supplierInfo}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.sku}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.category ? product.category.name : '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button type="button" 
                        onclick="selectProduct(${product.id})"
                        class="text-indigo-600 hover:text-indigo-900">
                        choose
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// Modal box control
function showModal() {
    console.log('Opening product modal');
    window.purchaseState.elements.modal.classList.remove('hidden');
    filterProducts();
}

function hideModal() {
    console.log('Closing product modal');
    window.purchaseState.elements.modal.classList.add('hidden');
    window.purchaseState.elements.productSearch.value = '';
    window.purchaseState.elements.categoryFilter.value = '';
}

// Select a product
window.selectProduct = async function(productId) {
    // Close the modal box now
    hideModal();
    
    try {
        console.group('Select products and obtain supplier information');
        console.log('Start getting product supplier information:', { productId });
        
        const response = await fetch(`/api/products/${productId}/suppliers`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Unauthorized: Please log in again to continue. Your session may have expired.');
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('API Response data:', result);
        
        if (result.status === 'success') {
            const productData = result.data;
            console.log('The obtained product data:', {
                product: productData,
                suppliers: productData.suppliers,
                firstSupplier: productData.suppliers[0]
            });
            
            // 检查产品是否有有效的selling_price
            if (!productData.selling_price || parseFloat(productData.selling_price) <= 0) {
                console.error('The product has no valid selling price:', productData);
                Swal.fire({
                    title: 'Error',
                    text: 'Cannot add this product because it has no valid selling price. Please update the product details first.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                console.groupEnd();
                return;
            }
            
            // Check if the same product already exists
            const existingItems = window.purchaseState.items.filter(item => item.product_id === productData.id);
            
            if (existingItems.length > 0) {
                console.log('The product already exists:', {
                    existingItems,
                    usedSupplierIds: existingItems.map(item => item.supplier_id)
                });
                
                // Get the used vendorIDList
                const usedSupplierIds = existingItems.map(item => item.supplier_id);
                
                // Check if there are still unused suppliers
                const availableSuppliers = productData.suppliers.filter(
                    supplier => !usedSupplierIds.includes(supplier.id)
                );

                console.log('Available suppliers:', {
                    availableSuppliers,
                    count: availableSuppliers.length
                });

                if (availableSuppliers.length === 0) {
                    Swal.fire({
                        title: 'Unable to add',
                        text: 'All suppliers of this item have been added',
                        icon: 'warning',
                        confirmButtonText: 'Sure'
                    });
                    return;
                }

                const result = await Swal.fire({
                    title: 'The product already exists',
                    text: 'Do you continue to add the same product (using different suppliers)?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Continue to add',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33'
                });

                if (result.isConfirmed) {
                    console.log('Users choose to continue adding, use available vendors');
                    addNewProduct(productData, availableSuppliers);
                }
                return;
            }

            console.log('Add new products');
            addNewProduct(productData);
        }
        console.groupEnd();
    } catch (error) {
        console.error('An error occurred while adding a product:', error);
        Swal.fire({
            title: 'mistake',
            text: 'Adding the product failed, please try again',
            icon: 'error',
            confirmButtonText: 'Sure'
        });
    }
};

// Add new products
function addNewProduct(productData, availableSuppliers = null) {
    console.group('Add new products');
    console.log('Product Data:', {
        productData: productData,
        availableSuppliers: availableSuppliers,
        currentItemsCount: window.purchaseState.items.length
    });

    // 检查产品是否有selling_price
    if (!productData.selling_price || parseFloat(productData.selling_price) <= 0) {
        console.error('This product has no valid selling price:', productData);
        Swal.fire({
            title: 'Error',
            text: 'Cannot add this product because it has no valid selling price. Please update the product details first.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        console.groupEnd();
        return;
    }

    const suppliers = availableSuppliers || productData.suppliers;
    const lowestPriceSupplier = findLowestPriceSupplier(suppliers);
    
    console.log('Choose the lowest price supplier:', {
        supplier: lowestPriceSupplier,
        basePrice: lowestPriceSupplier.purchase_price,
        minOrderQuantity: lowestPriceSupplier.min_order_quantity,
        priceAgreements: lowestPriceSupplier.price_agreements,
        lead_time: lowestPriceSupplier.lead_time
    });

    const newItem = {
        product_id: productData.id,
        product_name: productData.name,
        suppliers: productData.suppliers,
        supplier_id: lowestPriceSupplier.id,
        quantity: lowestPriceSupplier.min_order_quantity,
        unit_price: lowestPriceSupplier.purchase_price,
        tax_rate: lowestPriceSupplier.tax_rate,
        min_order_quantity: lowestPriceSupplier.min_order_quantity,
        lead_time: lowestPriceSupplier.lead_time || 0,
        total_amount: 0,
        tax_amount: 0
    };

    console.log('Create a new product item:', newItem);

    // Apply the most suitable price agreement
    const effectivePrice = applyBestPriceAgreement(lowestPriceSupplier, lowestPriceSupplier.min_order_quantity);
    console.log('Final price after applying the price agreement:', {
        originalPrice: lowestPriceSupplier.purchase_price,
        effectivePrice: effectivePrice,
        difference: effectivePrice - lowestPriceSupplier.purchase_price
    });

    window.purchaseState.items.push(newItem);
    console.log('Updated product list status:', {
        itemsCount: window.purchaseState.items.length,
        newItemIndex: window.purchaseState.items.length - 1,
        allItems: window.purchaseState.items
    });

    calculateTotal(window.purchaseState.items.length - 1);
    updateItemsTable();
    updateSupplierSummary();
    
    console.groupEnd();
}

// Helper functions
function findLowestPriceSupplier(suppliers, quantity = null) {
    console.log('Start finding the lowest price supplier:', {
        quantity: quantity,
        suppliersCount: suppliers.length
    });
    
    return suppliers.reduce((lowest, current) => {
        const currentPrice = parseFloat(calculateEffectivePrice(current, quantity));
        const lowestPrice = parseFloat(calculateEffectivePrice(lowest, quantity));
        
        console.log('Supplier price comparison:', {
            currentSupplier: current.name,
            currentPrice: currentPrice,
            currentBasePrice: current.purchase_price,
            lowestSupplier: lowest.name,
            lowestPrice: lowestPrice,
            lowestBasePrice: lowest.purchase_price,
            comparison: currentPrice < lowestPrice ? 'Current suppliers are more favorable' : 'The lowest price suppliers are more favorable'
        });
        
        // Make sure to choose a lower-priced supplier
        return currentPrice < lowestPrice ? current : lowest;
    }, suppliers[0]);
}

// Apply the best price agreement
function applyBestPriceAgreement(supplier, quantity) {
    console.log(`Applying best price agreement for supplier ${supplier.name} with quantity ${quantity}`);
    const effectivePrice = calculateEffectivePrice(supplier, quantity);
    console.log(`Effective price for supplier ${supplier.name}: ${effectivePrice}`);
    
    // Find the corresponding oneitemAnd update the unit price
    const itemIndex = window.purchaseState.items.findIndex(item => item.supplier_id === supplier.id);
    if (itemIndex !== -1) {
        window.purchaseState.items[itemIndex].unit_price = effectivePrice;
        calculateTotal(itemIndex);
    }
    
    return effectivePrice;
}

// RevisecalculateEffectivePricefunction
function calculateEffectivePrice(supplier, quantity = null) {
    if (!supplier) return Infinity;
    
    const qtty = quantity || supplier.min_order_quantity;
    console.log(`Computing suppliers ${supplier.name} Valid price:`, {
        quantity: qtty,
        basePrice: supplier.purchase_price,
        minOrderQuantity: supplier.min_order_quantity,
        agreements: supplier.price_agreements
    });
    
    // If there is no price agreement, return to the base price
    if (!supplier.price_agreements || !supplier.price_agreements.length) {
        console.log(`supplier ${supplier.name} No price agreement, use the basic price:`, supplier.purchase_price);
        return parseFloat(supplier.purchase_price);
    }

    // Sort price agreements by minimum quantity requirements descending order
    const validAgreements = supplier.price_agreements
        .filter(agreement => isAgreementValid(agreement))
        .sort((a, b) => b.min_quantity - a.min_quantity);
        
    console.log(`supplier ${supplier.name} Valid price agreement:`, validAgreements);

    // Find all protocols that meet the current quantity requirements
    const applicableAgreements = validAgreements.filter(agreement => 
        qtty >= agreement.min_quantity
    );

    console.log(`supplier ${supplier.name} Applicable price agreements:`, applicableAgreements);

    if (applicableAgreements.length > 0) {
        // Choose the best deal
        const bestAgreement = applicableAgreements[0];
        console.log(`supplier ${supplier.name} Use the best price agreement:`, bestAgreement);
        
        if (bestAgreement.discount_type === 'fixed_price') {
            console.log(`supplier ${supplier.name} Use fixed price:`, bestAgreement.price);
            return parseFloat(bestAgreement.price);
        } else if (bestAgreement.discount_type === 'discount_rate') {
            const discountedPrice = supplier.purchase_price * (1 - bestAgreement.discount_rate / 100);
            console.log(`supplier ${supplier.name} Apply discount rate ${bestAgreement.discount_rate}%, final price:`, discountedPrice);
            return parseFloat(discountedPrice);
        }
    }
    
    // If there is no applicable price agreement, return to the base price
    console.log(`supplier ${supplier.name} No applicable price agreement, use the base price:`, supplier.purchase_price);
    return parseFloat(supplier.purchase_price);
}

function isAgreementValid(agreement) {
    if (!agreement) return false;
    
    const now = new Date();
    const startDate = new Date(agreement.start_date);
    const endDate = agreement.end_date ? new Date(agreement.end_date) : null;
    
    const isValid = startDate <= now && (!endDate || endDate >= now);
    console.log('Check the validity of the price agreement:', {
        agreement: agreement,
        startDate: startDate,
        endDate: endDate,
        isValid: isValid
    });
    
    return isValid;
}

function formatCurrency(value) {
    return new Intl.NumberFormat('zh-CN', {
        style: 'currency',
        currency: 'CNY'
    }).format(value);
}

// Update product table display
function updateItemsTable() {
    window.purchaseState.elements.productRows.innerHTML = window.purchaseState.items.map((item, index) => {
        const supplierOptions = item.suppliers.map(supplier => 
            `<option value="${supplier.id}" ${supplier.id === item.supplier_id ? 'selected' : ''}>
                ${supplier.name}
            </option>`
        ).join('');

        console.log('Rendering product line:', {
            index: index,
            product_name: item.product_name,
            lead_time: item.lead_time,
            supplier_id: item.supplier_id
        });

        return `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${item.product_name}
                    <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                    <div class="text-xs text-gray-500">
                        Delivery cycle: ${item.lead_time || 0} day
                    </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <select name="items[${index}][supplier_id]" 
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        onchange="handleSupplierChange(${index}, this.value)">
                        ${supplierOptions}
                    </select>
            </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <input type="number" 
                        name="items[${index}][quantity]" 
                        value="${item.quantity}"
                        min="${item.min_order_quantity}"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        onchange="handleQuantityChange(${index}, this.value)">
                    <span class="text-xs text-red-500">
                        Minimum order quantity: ${item.min_order_quantity}
                    </span>
            </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <input type="number" 
                        name="items[${index}][unit_price]" 
                        value="${item.unit_price}"
                        step="0.01"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        onchange="handlePriceChange(${index}, this.value)">
            </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <input type="number" 
                        name="items[${index}][tax_rate]" 
                        value="${item.tax_rate}"
                        step="0.01"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        onchange="handleTaxRateChange(${index}, this.value)">
            </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${formatCurrency(item.total_amount)}
                    <input type="hidden" name="items[${index}][total_amount]" value="${item.total_amount}">
                    <input type="hidden" name="items[${index}][tax_amount]" value="${item.tax_amount}">
                    <input type="hidden" name="items[${index}][lead_time]" value="${item.lead_time || 0}">
            </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <button type="button" 
                        class="text-red-600 hover:text-red-900"
                        onclick="handleRemoveItem(${index})">delete</button>
            </td>
            </tr>
        `;
    }).join('');
}

// Event handling function
window.handleSupplierChange = function(index, supplierId) {
    window.updateSupplier(index, supplierId);
};

window.handleQuantityChange = function(index, value) {
    window.updateQuantity(index, value);
};

window.handlePriceChange = function(index, value) {
    window.updatePrice(index, value);
};

window.handleTaxRateChange = function(index, value) {
    window.calculateTotal(index);
};

window.handleRemoveItem = function(index) {
    window.removeItem(index);
};

window.handleShippingFeeChange = function(supplierId, value) {
    window.updateShippingFee(supplierId, value);
};

window.handleSupplierNotesChange = function(supplierId, value) {
    window.updateSupplierNotes(supplierId, value);
};

// Update supplier summary display
function updateSupplierSummaryDisplay() {
    window.purchaseState.elements.supplierSummaryContainer.innerHTML = Object.entries(window.purchaseState.supplierSummaries).map(([supplierId, summary]) => `
        <div class="bg-gray-50 p-4 rounded-lg mb-4">
            <h4 class="text-lg font-medium text-gray-900 mb-2">${summary.supplier_name}</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="text-sm text-gray-500">Total product:</span>
                    <span class="text-sm text-gray-900">${formatCurrency(summary.total_amount)}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-500">tax:</span>
                    <span class="text-sm text-gray-900">${formatCurrency(summary.tax_amount)}</span>
                </div>
                <div>
                    <label class="block text-sm text-gray-500">freight:</label>
                    <input type="number" 
                        name="supplier_shipping_fee[${supplierId}]"
                        value="${summary.shipping_fee}"
                        step="0.01"
                        onchange="handleShippingFeeChange('${supplierId}', this.value)"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>
            <div class="mt-2">
                <label class="block text-sm text-gray-500">Remark:</label>
                <textarea name="supplier_notes[${supplierId}]"
                    rows="2"
                    onchange="handleSupplierNotesChange('${supplierId}', this.value)"
                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">${summary.notes}</textarea>
            </div>
        </div>
    `).join('');
}

// Other auxiliary functions
function updateSupplierSummary() {
    console.group('Update supplier summary');
    const summaries = {};
    
    window.purchaseState.items.forEach((item, index) => {
        console.log(`Handle goods ${index + 1}/${window.purchaseState.items.length}:`, {
            productName: item.product_name,
            supplierId: item.supplier_id,
            totalAmount: item.total_amount,
            taxAmount: item.tax_amount
        });
        
        const supplier = item.suppliers.find(s => s.id === parseInt(item.supplier_id));
        if (!supplier) {
            console.error('No supplier found:', {
                supplierId: item.supplier_id,
                availableSuppliers: item.suppliers
            });
            return;
        }
        
        if (!summaries[item.supplier_id]) {
            summaries[item.supplier_id] = {
                supplier_name: supplier.name,
                total_amount: 0,
                tax_amount: 0,
                shipping_fee: window.purchaseState.supplierSummaries[item.supplier_id]?.shipping_fee || 0,
                notes: window.purchaseState.supplierSummaries[item.supplier_id]?.notes || ''
            };
            console.log(`Create a new vendor summary: ${supplier.name}`);
        }
        
        summaries[item.supplier_id].total_amount += item.total_amount;
        summaries[item.supplier_id].tax_amount += item.tax_amount;
        
        console.log(`supplier ${supplier.name} Current Summary:`, {
            totalAmount: summaries[item.supplier_id].total_amount,
            taxAmount: summaries[item.supplier_id].tax_amount,
            shippingFee: summaries[item.supplier_id].shipping_fee
        });
    });
    
    console.log('Final Supplier Summary Results:', summaries);
    
    window.purchaseState.supplierSummaries = summaries;
    updateSupplierSummaryDisplay();
    
    console.groupEnd();
}

// Update suppliers
function updateSupplier(index, supplierId) {
    console.group('Update supplier information');
    const item = window.purchaseState.items[index];
    console.log('Current product information:', {
        index: index,
        item: item,
        currentSupplierId: item.supplier_id,
        newSupplierId: supplierId
    });

    const supplier = item.suppliers.find(s => s.id === parseInt(supplierId));
    console.log('Supplier information found:', {
        supplier: supplier,
        allSuppliers: item.suppliers
    });
            
        if (supplier) {
        console.log('Before the update supplier information:', {
            index: index,
            supplierId: supplierId,
            supplier: supplier,
            currentLeadTime: supplier.lead_time,
            allSupplierFields: Object.keys(supplier)
        });

        // Check if the same product and supplier combination already exists
        const existingItem = window.purchaseState.items.find((otherItem, otherIndex) => 
            otherIndex !== index && 
            otherItem.product_id === item.product_id && 
            otherItem.supplier_id === parseInt(supplierId)
        );
        
        if (existingItem) {
            console.log('Discover duplicate supplier product portfolios:', {
                existingItem: existingItem,
                currentItem: item
            });

            Swal.fire({
                title: 'hint',
                text: 'This item is already in the current supplier list',
                icon: 'warning',
                confirmButtonText: 'Sure'
            });
            // Reset the selection box to the original vendor
            updateItemsTable();
            console.groupEnd();
            return;
        }

        item.supplier_id = supplier.id;
        item.unit_price = supplier.purchase_price;
        item.tax_rate = supplier.tax_rate;
        item.lead_time = supplier.lead_time || 0;
        
        console.log('Updated product information:', {
            supplier_id: item.supplier_id,
            unit_price: item.unit_price,
            tax_rate: item.tax_rate,
            lead_time: item.lead_time,
            allItemFields: Object.keys(item)
        });
        
        // Check and update the minimum order quantity
        const newMinOrderQuantity = supplier.min_order_quantity;
        if (item.quantity < newMinOrderQuantity) {
            console.log('Update minimum order quantity:', {
                oldQuantity: item.quantity,
                newQuantity: newMinOrderQuantity
            });
            item.quantity = newMinOrderQuantity;
        }
        item.min_order_quantity = newMinOrderQuantity;
        
        // Apply the best price agreement
        const newPrice = applyBestPriceAgreement(supplier, item.quantity);
        console.log('Results after applying the price agreement:', {
            originalPrice: supplier.purchase_price,
            newPrice: newPrice,
            quantity: item.quantity
        });
        
        calculateTotal(index);
        updateItemsTable();
        updateSupplierSummary();
    } else {
        console.error('No supplier found:', {
            supplierId: supplierId,
            availableSuppliers: item.suppliers
        });
    }
    console.groupEnd();
}

// Update quantity
function updateQuantity(index, value) {
    console.log('Number of updates:', {
        index: index,
        newValue: value,
        currentItem: window.purchaseState.items[index]
    });

    const item = window.purchaseState.items[index];
    const newQuantity = parseFloat(value);
    const supplier = item.suppliers.find(s => s.id === parseInt(item.supplier_id));
    
    if (!supplier) {
        console.error('No supplier found:', {
            supplierId: item.supplier_id,
            availableSuppliers: item.suppliers
        });
        return;
    }

    // Ensure quantity is not less than the minimum order quantity
    if (newQuantity < item.min_order_quantity) {
        console.log('Quantity is less than the minimum order quantity:', {
            newQuantity: newQuantity,
            minOrderQuantity: item.min_order_quantity
        });
        
        item.quantity = item.min_order_quantity;
        Swal.fire({
            title: 'hint',
            text: `This quantity cannot be less than the minimum order quantity ${item.min_order_quantity}`,
            icon: 'info',
            confirmButtonText: 'Sure'
        });
    } else {
        item.quantity = newQuantity;
    }

    // Check prices for all suppliers
    const allPrices = item.suppliers.map(s => ({
        supplier: s,
        price: calculateEffectivePrice(s, item.quantity)
    }));

    console.log('All supplier price comparison:', allPrices);

    // Find the lowest price supplier
    const bestOption = allPrices.reduce((best, current) => 
        current.price < best.price ? current : best
    );

    console.log('Best price options:', {
        supplier: bestOption.supplier.name,
        price: bestOption.price
    });

    // Current supplier prices
    const currentPrice = calculateEffectivePrice(supplier, item.quantity);

    console.log('Current supplier price:', {
        supplier: supplier.name,
        price: currentPrice
    });

    // If you find a better price and not the current supplier
    if (bestOption.supplier.id !== supplier.id && bestOption.price < currentPrice) {
        console.log('Discover more favorable prices:', {
            currentSupplier: supplier.name,
            currentPrice: currentPrice,
            bestSupplier: bestOption.supplier.name,
            bestPrice: bestOption.price,
            priceDifference: currentPrice - bestOption.price
        });

        Swal.fire({
            title: 'Discover more favorable suppliers',
            text: `Current supplier ${supplier.name} The price is ${formatCurrency(currentPrice)},supplier ${bestOption.supplier.name} The price is ${formatCurrency(bestOption.price)}, Switch to a lower priced supplier?`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Switch to lower prices',
            cancelButtonText: 'Stay current supplier'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Users choose to switch suppliers');
                updateSupplier(index, bestOption.supplier.id);
                return;
            }
            
            console.log('Users choose to keep current supplier');
            // If you do not switch suppliers, update the current supplier's price
            item.unit_price = currentPrice;
            calculateTotal(index);
            updateItemsTable();
            updateSupplierSummary();
        });
    } else {
        // Update the current supplier's price
        console.log('Use current supplier\'s price:', currentPrice);
        item.unit_price = currentPrice;
        calculateTotal(index);
        updateItemsTable();
        updateSupplierSummary();
    }
}

// Update price
function updatePrice(index, value) {
    const item = window.purchaseState.items[index];
    item.unit_price = parseFloat(value);
    calculateTotal(index);
    updateSupplierSummary();
    updateItemsTable();
    updateSupplierSummaryDisplay();
}

// Calculate total amount
function calculateTotal(index) {
    console.group('Calculate the total amount of goods');
    const item = window.purchaseState.items[index];
    
    console.log('Product status before calculation:', {
        index: index,
        item: item,
        beforeCalculation: {
            quantity: item.quantity,
            unitPrice: item.unit_price,
            totalAmount: item.total_amount,
            taxAmount: item.tax_amount,
            taxRate: item.tax_rate
        }
    });

    // Calculate total amount and tax amount
    const previousTotal = item.total_amount;
    const previousTax = item.tax_amount;
    
    item.total_amount = parseFloat((item.quantity * item.unit_price).toFixed(2));
    item.tax_amount = parseFloat((item.total_amount * (item.tax_rate / 100)).toFixed(2));

    console.log('Calculation results:', {
        newTotalAmount: item.total_amount,
        newTaxAmount: item.tax_amount,
        difference: {
            total: item.total_amount - previousTotal,
            tax: item.tax_amount - previousTax
        }
    });

    updateSupplierSummary();
    updateItemsTable();
    updateSupplierSummaryDisplay();
    
    console.groupEnd();
}

// Delete the product
function removeItem(index) {
    window.purchaseState.items.splice(index, 1);
    updateSupplierSummary();
    updateItemsTable();
    updateSupplierSummaryDisplay();
}

// Update shipping charges
function updateShippingFee(supplierId, value) {
    if (window.purchaseState.supplierSummaries[supplierId]) {
        window.purchaseState.supplierSummaries[supplierId].shipping_fee = parseFloat(value) || 0;
        updateSupplierSummaryDisplay();
    }
}

// Update supplier notes
function updateSupplierNotes(supplierId, value) {
    if (window.purchaseState.supplierSummaries[supplierId]) {
        window.purchaseState.supplierSummaries[supplierId].notes = value;
        updateSupplierSummaryDisplay();
    }
}

// Render parameter fields
function renderParameterFields(parameters) {
    if (!parameters || parameters.length === 0) {
        window.purchaseState.elements.categoryParameters.classList.add('hidden');
        return;
    }

    window.purchaseState.elements.categoryParameters.classList.remove('hidden');
    window.purchaseState.elements.parameterFields.innerHTML = parameters.map(param => {
        let inputHtml = '';
        if (param.type === 'select' && param.options) {
            inputHtml = `
                <select class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        name="parameters[${param.code}]"
                        ${param.is_required ? 'required' : ''}>
                    <option value="">Please select</option>
                    ${param.options.map(option => `<option value="${option}">${option}</option>`).join('')}
                </select>`;
        } else {
            inputHtml = `
                <input type="${param.type}"
                       class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       name="parameters[${param.code}]"
                       ${param.is_required ? 'required' : ''}
                       ${param.validation_rules.includes('max:') ? `max="${param.validation_rules.match(/max:(\d+)/)[1]}"` : ''}
                       ${param.validation_rules.includes('min:') ? `min="${param.validation_rules.match(/min:(\d+)/)[1]}"` : ''}>`;
        }

        return `
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    ${param.name}
                    ${param.is_required ? '<span class="text-red-500">*</span>' : ''}
                </label>
                ${inputHtml}
            </div>`;
    }).join('');
}

// 会话保活函数 - 确保用户不会由于会话超时而遇到401错误
async function keepSessionAlive() {
    try {
        // 每5分钟发送一个请求到后端以保持会话活跃
        const response = await fetch('/api/csrf-cookie', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        console.log('Session keep-alive ping sent');
        
        if (!response.ok) {
            console.warn('Session ping failed, status:', response.status);
        }
    } catch (error) {
        console.error('Error in keep-alive ping:', error);
    }
}

// 设置定时器，每5分钟触发一次保活请求
setInterval(keepSessionAlive, 5 * 60 * 1000);

// 页面加载时立即发送一次保活请求
document.addEventListener('DOMContentLoaded', keepSessionAlive);

// Add function towindowObject
Object.assign(window, {
    selectProduct,
    updateSupplier,
    updateQuantity,
    updatePrice,
    calculateTotal,
    removeItem,
    updateShippingFee,
    updateSupplierNotes,
    showModal,
    hideModal,
    loadProducts,
    filterProducts,
    getParameterFilters,
    loadWarehouseAddress,
    loadCategoryParameters,
    renderParameterFields,
    findLowestPriceSupplier,
    calculateEffectivePrice,
    isAgreementValid,
    applyBestPriceAgreement,
    formatCurrency,
    updateItemsTable,
    updateSupplierSummary,
    updateSupplierSummaryDisplay
});

// Trigger filter when parameter fields change
document.addEventListener('change', function(e) {
    if (e.target.closest('#parameterFields')) {
        filterProducts();
    }
});

console.log('The initialization of the procurement creation page is completed');
</script>
@endpush 