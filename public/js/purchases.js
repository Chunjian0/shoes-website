// Supplier-related functions
let suppliers = [];
let selectedSupplier = null;

// Initialize the supplier modal box
function initSupplierModal() {
    const modal = document.getElementById('supplierModal');
    const searchInput = document.getElementById('supplierSearch');
    const suppliersList = document.getElementById('suppliersList');
    const closeBtn = document.getElementById('closeSupplierModal');
    const selectBtn = document.getElementById('selectSupplierBtn');

    // Loading vendor data
    async function loadSuppliers() {
        try {
            const response = await fetch('/api/suppliers', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                throw new Error('Failed to load vendor data');
            }

            const result = await response.json();
            if (result.status === 'success' && Array.isArray(result.data)) {
                suppliers = result.data;
                renderSuppliers(suppliers);
            } else {
                throw new Error(result.message || 'Return data format error');
            }
        } catch (error) {
            console.error('Failed to load vendor data:', error);
            showToast('Failed to load vendor data: ' + error.message, 'error');
        }
    }

    // Rendering vendor list
    function renderSuppliers(suppliersToRender) {
        suppliersList.innerHTML = suppliersToRender.map(supplier => `
            <li class="supplier-item py-3 px-4 hover:bg-gray-50 cursor-pointer" data-id="${supplier.id}">
                <div class="flex items-center space-x-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${supplier.name}</p>
                        <p class="text-sm text-gray-500 truncate">${supplier.contact_person || ''}</p>
                    </div>
                    <div class="inline-flex items-center text-sm text-gray-500">
                        ${supplier.phone || ''}
                    </div>
                </div>
            </li>
        `).join('');

        // Add a click event
        document.querySelectorAll('.supplier-item').forEach(item => {
            item.addEventListener('click', () => {
                const supplierId = item.dataset.id;
                const supplier = suppliers.find(s => s.id == supplierId);
                selectSupplier(supplier);
                closeModal();
            });
        });
    }

    // Search for suppliers
    function searchSuppliers(query) {
        const filtered = suppliers.filter(supplier => 
            supplier.name.toLowerCase().includes(query.toLowerCase()) ||
            (supplier.contact_person && supplier.contact_person.toLowerCase().includes(query.toLowerCase())) ||
            (supplier.phone && supplier.phone.includes(query))
        );
        renderSuppliers(filtered);
    }

    // Select a supplier
    function selectSupplier(supplier) {
        selectedSupplier = supplier;
        document.getElementById('supplier_id').value = supplier.id;
        document.getElementById('supplier').value = supplier.name;
        
        // Loading supplier products and price agreements
        loadSupplierProducts(supplier.id);
    }

    // Display modal box
    function showModal() {
        modal.classList.remove('hidden');
        searchInput.focus();
        if (suppliers.length === 0) {
            loadSuppliers();
        }
    }

    // Close the modal box
    function closeModal() {
        modal.classList.add('hidden');
        searchInput.value = '';
        renderSuppliers(suppliers);
    }

    // Event listening
    selectBtn.addEventListener('click', showModal);
    closeBtn.addEventListener('click', closeModal);
    searchInput.addEventListener('input', (e) => searchSuppliers(e.target.value));

    // Click outside the modal box to close
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
}

// Product related functions
let supplierProducts = [];
let selectedProducts = new Map();

// Loading the supplier product
async function loadSupplierProducts(supplierId) {
    try {
        const response = await fetch(`/api/suppliers/${supplierId}/products`);
        const data = await response.json();
        supplierProducts = data;
        
        // Update product selector
        updateProductSelector();
        
        // Calculate the maximum lead time
        calculateExpectedDeliveryDate();
    } catch (error) {
        console.error('Failed to load the vendor product:', error);
        showToast('Failed to load the vendor product', 'error');
    }
}

// Update product selector
function updateProductSelector() {
    const container = document.getElementById('itemsContainer');
    // Clear existing products
    container.innerHTML = '';
    selectedProducts.clear();
    
    // Recalculate the total amount
    calculateTotals();
}

// Add product line
function addProductRow(product) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="px-3 py-4">
            <div class="text-sm text-gray-900">${product.name}</div>
            <div class="text-sm text-gray-500">${product.sku || ''}</div>
        </td>
        <td class="px-3 py-4">
            <input type="number" min="1" value="1" 
                class="quantity-input block w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                data-product-id="${product.id}">
        </td>
        <td class="px-3 py-4">
            <input type="number" step="0.01" value="${product.cost_price}" 
                class="price-input block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                data-product-id="${product.id}">
        </td>
        <td class="px-3 py-4">
            <input type="number" step="0.01" value="${product.tax_rate || 0}" 
                class="tax-rate-input block w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                data-product-id="${product.id}">
        </td>
        <td class="px-3 py-4">
            <div class="text-sm text-gray-900 amount" data-product-id="${product.id}">0.00</div>
        </td>
        <td class="px-3 py-4">
            <div class="text-sm text-gray-900 tax-amount" data-product-id="${product.id}">0.00</div>
        </td>
        <td class="px-3 py-4">
            <input type="text" class="remark-input block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                data-product-id="${product.id}">
        </td>
        <td class="px-3 py-4 text-right">
            <button type="button" class="text-red-600 hover:text-red-900 delete-row"
                data-product-id="${product.id}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </td>
    `;
    
    document.getElementById('itemsContainer').appendChild(row);
    
    // Add event listening
    const quantityInput = row.querySelector('.quantity-input');
    const priceInput = row.querySelector('.price-input');
    const taxRateInput = row.querySelector('.tax-rate-input');
    const deleteBtn = row.querySelector('.delete-row');
    
    [quantityInput, priceInput, taxRateInput].forEach(input => {
        input.addEventListener('input', () => calculateRowAmount(product.id));
    });
    
    deleteBtn.addEventListener('click', () => {
        row.remove();
        selectedProducts.delete(product.id);
        calculateTotals();
    });
    
    // Initial calculation
    calculateRowAmount(product.id);
}

// Calculate the amount of the row
function calculateRowAmount(productId) {
    const row = document.querySelector(`tr:has([data-product-id="${productId}"])`);
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const taxRate = parseFloat(row.querySelector('.tax-rate-input').value) || 0;
    
    const amount = quantity * price;
    const taxAmount = amount * (taxRate / 100);
    
    row.querySelector('.amount').textContent = amount.toFixed(2);
    row.querySelector('.tax-amount').textContent = taxAmount.toFixed(2);
    
    calculateTotals();
}

// Calculate the total amount
function calculateTotals() {
    let subtotal = 0;
    let taxTotal = 0;
    
    document.querySelectorAll('.amount').forEach(el => {
        subtotal += parseFloat(el.textContent) || 0;
    });
    
    document.querySelectorAll('.tax-amount').forEach(el => {
        taxTotal += parseFloat(el.textContent) || 0;
    });
    
    const shippingFee = parseFloat(document.getElementById('shipping_fee').value) || 0;
    const total = subtotal + taxTotal + shippingFee;
    
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('tax_amount').value = taxTotal.toFixed(2);
    document.getElementById('total_amount').value = total.toFixed(2);
}

// Calculate estimated arrival date
function calculateExpectedDeliveryDate() {
    if (!selectedSupplier || supplierProducts.length === 0) return;
    
    // Get the maximum lead time
    const maxLeadTime = Math.max(...supplierProducts.map(p => p.lead_time || 0));
    
    if (maxLeadTime > 0) {
        const purchaseDate = document.getElementById('purchase_date').value;
        if (purchaseDate) {
            const expectedDate = new Date(purchaseDate);
            expectedDate.setDate(expectedDate.getDate() + maxLeadTime);
            document.getElementById('expected_delivery_date').value = expectedDate.toISOString().split('T')[0];
        }
    }
}

// Form submission processing
function initFormSubmit() {
    const form = document.getElementById('purchaseForm');
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        try {
            const formData = new FormData(form);
            const items = [];
            
            document.querySelectorAll('tr:has(.quantity-input)').forEach(row => {
                const productId = row.querySelector('.quantity-input').dataset.productId;
                items.push({
                    product_id: productId,
                    quantity: row.querySelector('.quantity-input').value,
                    unit_price: row.querySelector('.price-input').value,
                    tax_rate: row.querySelector('.tax-rate-input').value,
                    tax_amount: row.querySelector('.tax-amount').textContent,
                    amount: row.querySelector('.amount').textContent,
                    remarks: row.querySelector('.remark-input').value
                });
            });
            
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    supplier_id: formData.get('supplier_id'),
                    warehouse_id: formData.get('warehouse_id'),
                    purchase_date: formData.get('purchase_date'),
                    expected_delivery_date: formData.get('expected_delivery_date'),
                    shipping_fee: formData.get('shipping_fee'),
                    notes: formData.get('notes'),
                    items: items
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                showToast('Purchase order creation successfully', 'success');
                window.location.href = result.redirect;
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Submit purchase order failed:', error);
            showToast('Submit purchase order failed: ' + error.message, 'error');
        }
    });
}

// Tool function: Show prompt message
function showToast(message, type = 'info') {
    // Use your prompt box component or create a simple prompt
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        'bg-blue-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// initialization
document.addEventListener('DOMContentLoaded', () => {
    initSupplierModal();
    initFormSubmit();
    
    // Recalculate the total amount when the freight input box changes
    document.getElementById('shipping_fee').addEventListener('input', calculateTotals);
    
    // Recalculate the estimated arrival date when the purchase date changes
    document.getElementById('purchase_date').addEventListener('change', calculateExpectedDeliveryDate);
}); 