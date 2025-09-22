<!-- Supplier Product Form Model Frame -->
<div x-data="productForm()" 
    x-show="$store.modal.current === 'product-form'"
    class="modal">
    <div class="modal-content max-w-2xl">
        <div class="modal-header">
            <h3 class="text-lg font-medium text-gray-900" x-text="formTitle"></h3>
            <button type="button" @click="closeModal" class="modal-close">
                <span class="sr-only">closure</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form @submit.prevent="submitForm">
            <div class="modal-body">
                <!-- Product choice -->
                <div class="mb-4">
                    <label for="product_search" class="block text-sm font-medium text-gray-700">Search for products</label>
                    <div class="mt-1 relative">
                        <input type="text" 
                               id="product_search" 
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                               placeholder="Enter a product name、SKUor barcode search..."
                               oninput="filterProducts(this.value)">
                        <div id="productSearchResults" class="absolute z-10 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm hidden">
                            <!-- Search results will be displayed dynamically here -->
                        </div>
                    </div>
                </div>

                <!-- Selected product information -->
                <div id="selectedProductInfo" class="mb-4 p-4 border rounded-md hidden">
                    <h3 class="font-medium text-gray-900">Selected products:</h3>
                    <p id="selectedProductName" class="text-sm text-gray-500"></p>
                    <p id="selectedProductSku" class="text-sm text-gray-500"></p>
                </div>

                <!-- Supplier product code -->
                <div class="mb-4">
                    <label for="supplier_product_code" class="block text-sm font-medium text-gray-700">Supplier Product Code</label>
                    <input type="text" 
                           id="supplier_product_code" 
                           class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <!-- Purchase price -->
                <div class="mb-4">
                    <label for="purchase_price" class="block text-sm font-medium text-gray-700">Purchase price <span class="text-red-500">*</span></label>
                    <input type="number" 
                           id="purchase_price" 
                           class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                           step="0.01" 
                           required>
                </div>

                <!-- tax rate -->
                <div class="mb-4">
                    <label for="tax_rate" class="block text-sm font-medium text-gray-700">tax rate (%)</label>
                    <input type="number" 
                           id="tax_rate" 
                           class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                           step="0.01" 
                           min="0" 
                           max="100">
                </div>

                <!-- Minimum order quantity -->
                <div class="mb-4">
                    <label for="min_order_quantity" class="block text-sm font-medium text-gray-700">Minimum order quantity</label>
                    <input type="number" 
                           id="min_order_quantity" 
                           class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                           min="1" 
                           value="1">
                </div>

                <!-- Delivery cycle -->
                <div class="mb-4">
                    <label for="lead_time" class="block text-sm font-medium text-gray-700">Delivery cycle (days)</label>
                    <input type="number" 
                           id="lead_time" 
                           class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                           min="0" 
                           value="0">
                </div>

                <!-- Is the preferred supplier -->
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" 
                            x-model="form.is_preferred"
                            class="form-checkbox">
                        <span class="ml-2">Set as the preferred supplier</span>
                    </label>
                </div>

                <!-- Remark -->
                <div class="mb-4">
                    <label for="remarks" class="block text-sm font-medium text-gray-700">Remark</label>
                    <textarea id="remarks" 
                              class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                              rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="loading">
                    <span x-show="!loading">keep</span>
                    <span x-show="loading">Preservation...</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function productForm() {
    return {
        editMode: false,
        loading: false,
        form: {
            product_id: '',
            supplier_product_code: '',
            purchase_price: '',
            tax_rate: 0,
            min_order_quantity: 1,
            lead_time: 7,
            is_preferred: false,
            remarks: ''
        },
        availableProducts: [],

        async init() {
            // Get available product list
            try {
                const response = await fetch('/api/catalog/products');
                const data = await response.json();
                this.availableProducts = data.data;
            } catch (error) {
                console.error('List of product list failed:', error);
            }

            // Monitoring editor event
            this.$watch('$store.modal.current', (value) => {
                if (value === 'product-form') {
                    this.editMode = !!this.$store.modal.data;
                    if (this.editMode) {
                        this.form = { ...this.$store.modal.data };
                    } else {
                        this.resetForm();
                    }
                }
            });
        },

        get formTitle() {
            return this.editMode ? 'Editer Products Products' : 'Add supplier products';
        },

        async submitForm() {
            this.loading = true;
            try {
                const url = this.editMode 
                    ? `/api/suppliers/{{ $supplier->id }}/products/${this.form.product_id}`
                    : `/api/suppliers/{{ $supplier->id }}/products`;
                
                const response = await fetch(url, {
                    method: this.editMode ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Failed to operate');
                }

                // Close the modal box and refresh the page
                this.closeModal();
                window.location.reload();
            } catch (error) {
                console.error('Submit the form failed:', error);
                alert(error.message || 'Failed to operate,Please repeat');
            } finally {
                this.loading = false;
            }
        },

        resetForm() {
            this.form = {
                product_id: '',
                supplier_product_code: '',
                purchase_price: '',
                tax_rate: 0,
                min_order_quantity: 1,
                lead_time: 7,
                is_preferred: false,
                remarks: ''
            };
        },

        closeModal() {
            this.$store.modal.close();
            this.resetForm();
        }
    }
}
</script>
@endpush 