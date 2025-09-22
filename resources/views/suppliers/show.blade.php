@extends('layouts.app')

@section('title', 'Supplier Details')
@php
    
@endphp
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Top Navigation Bar -->
    <div class="bg-white shadow-sm rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ $supplier->name }}
                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $supplier->is_active ? 'Enabled' : 'Disabled' }}
                    </span>
                </h2>
                <p class="mt-1 text-sm text-gray-500">Supplier Code: {{ $supplier->code }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('suppliers.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to List
                </a>
                <a href="{{ route('suppliers.edit', $supplier) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Edit Supplier
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Side Basic Information -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Basic Information</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->contact_person }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->contact_phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->address }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Credit Limit</dt>
                            <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($supplier->credit_limit, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment Terms (Days)</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->payment_term }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supplier->notes }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="mt-6 bg-white shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Contact Information</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @if($supplier->contacts->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($supplier->contacts as $contact)
                                <div class="border rounded-lg p-4 {{ $contact->is_primary ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200' }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900">
                                                {{ $contact->name }}
                                                @if($contact->is_primary)
                                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Primary Contact</span>
                                                @endif
                                            </h4>
                                            @if($contact->position)
                                                <p class="mt-1 text-sm text-gray-500">{{ $contact->position }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-2 space-y-2">
                                        @if($contact->phone)
                                            <p class="text-sm text-gray-500">
                                                <span class="font-medium">Phone:</span> {{ $contact->phone }}
                                            </p>
                                        @endif
                                        @if($contact->email)
                                            <p class="text-sm text-gray-500">
                                                <span class="font-medium">Email:</span> {{ $contact->email }}
                                            </p>
                                        @endif
                                        @if($contact->remarks)
                                            <p class="text-sm text-gray-500">
                                                <span class="font-medium">Notes:</span> {{ $contact->remarks }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">No contact information available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Side Content Area -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Supplier Products -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Supplier Products</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">List of all products provided by this supplier</p>
                    </div>
                        <button type="button" 
                                id="addProductBtn"
                            onclick="showProductModal()"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                        Add products
                        </button>
                    </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <div id="supplierProductList" class="space-y-4 p-4">
                        @forelse($supplier->products as $product)
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-center justify-between p-4 bg-gray-50 border-b border-gray-200">
                                    <div class="flex items-center flex-1 min-w-0">
                                        @if($product->images && count($product->images) > 0)
                                            <div class="flex-shrink-0 h-16 w-16">
                                                <img class="h-16 w-16 rounded-lg object-cover"
                                                     src="{{ $product->images[0] }}"
                                                     alt="{{ $product->name }}">
                </div>
                                        @endif
                                        <div class="ml-4 flex-1 min-w-0">
                                            <h4 class="text-lg font-medium text-gray-900 truncate">{{ $product->name }}</h4>
                                            <div class="mt-1 flex items-center text-sm text-gray-500">
                                                <span class="truncate">
                                                    @if($product->sku)
                                                        SKU: {{ $product->sku }}
                                                    @endif
                                                    @if($product->barcode)
                                                        | Barcode: {{ $product->barcode }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" 
                                                onclick="editProduct({{ $product->id }})" 
                                                title="edit"
                                                class="p-1.5 rounded-md hover:bg-gray-100 transition-colors duration-200">
                                            <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        <button type="button" 
                                                onclick="deleteProduct({{ $product->id }})" 
                                                title="delete"
                                                class="p-1.5 rounded-md hover:bg-gray-100 transition-colors duration-200">
                                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-gray-500">Classification</div>
                                            <div class="mt-1 text-sm text-gray-900">{{ $product->category ? $product->category->name : 'Uncategorized' }}</div>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-gray-500">Supplier code</div>
                                            <div class="mt-1 text-sm text-gray-900">{{ $product->pivot->supplier_product_code ?? '-' }}</div>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-gray-500">Purchase price</div>
                                            <div class="mt-1 text-sm font-semibold text-indigo-600">RM {{ number_format($product->pivot->purchase_price ?? 0, 2) }}</div>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-gray-500">Sales price</div>
                                            <div class="mt-1 text-sm font-semibold text-green-600">RM {{ number_format($product->selling_price ?? 0, 2) }}</div>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-gray-500">tax rate</div>
                                            <div class="mt-1 text-sm text-gray-900">{{ $product->pivot->tax_rate ?? 0 }}%</div>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-gray-500">Minimum order quantity</div>
                                            <div class="mt-1 text-sm text-gray-900">{{ $product->pivot->min_order_quantity ?? '-' }}</div>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-gray-500">Delivery cycle</div>
                                            <div class="mt-1 text-sm text-gray-900">{{ $product->pivot->lead_time ?? '-' }} day</div>
                                        </div>
                                    </div>

                                    <!-- Price Agreement Section -->
                                    <div class="mt-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <h5 class="text-sm font-medium text-gray-900">Price Agreement</h5>
                                            <button type="button" 
                                                    onclick="showAgreementForm('{{ $product->id }}')"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Add a protocol
                                            </button>
                                        </div>
                                        <div class="space-y-2 agreement-list-{{ $product->id }}">
                                            @foreach($product->priceAgreements->where('supplier_id', $supplier->id) as $agreement)
                                                <div class="bg-gray-50 rounded-lg p-3 flex justify-between items-center">
                                                    <div>
                                                        <div class="text-sm text-gray-900">
                                                            @if($agreement->discount_type === 'fixed_price')
                                                                Fixed price: RM {{ number_format($agreement->price, 2) }}
                                                            @else
                                                                Discount rate: {{ $agreement->discount_rate }}%
                                                            @endif
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            Minimum quantity: {{ $agreement->min_quantity }} | 
                                                            Validity period: {{ $agreement->start_date }} to {{ $agreement->end_date }}
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                            onclick="deleteAgreement('{{ $agreement->id }}')"
                                                            class="text-red-600 hover:text-red-900">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    @if($product->pivot->remarks)
                                        <div class="mt-4 bg-yellow-50 rounded-lg p-3">
                                            <div class="text-sm font-medium text-gray-500">Remark</div>
                                            <div class="mt-1 text-sm text-gray-900">{{ $product->pivot->remarks }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-8">
                                No added products yet
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div id="productModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <!-- Background Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <!-- Modal Content Container -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl flex flex-col max-h-[90vh]">
            <form id="productForm" onsubmit="return saveProduct(event)" class="flex flex-col h-full">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 overflow-y-auto flex-grow">
                    <div class="mb-4">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search Product</label>
                        <input type="text" id="searchProduct" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="Enter product name, SKU or barcode to search">
                    </div>
                    <div class="mb-4">
                        <label for="category_filter" class="block text-sm font-medium text-gray-700">Filter by Category</label>
                        <select id="categoryFilter"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">All Categories</option>
                        </select>
                    </div>

                    <!-- Product List -->
                    <div class="mt-4">
                        <div class="overflow-y-auto max-h-[300px]">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th scope="col" class="w-16 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Select</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Information</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    </tr>
                                </thead>
                                <tbody id="productList" class="bg-white divide-y divide-gray-200">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Product Form Fields -->
                    <div id="productFormFields" class="mt-4 hidden">
                        <div class="space-y-4">
                            <div>
                                <label for="supplier_product_code" class="block text-sm font-medium text-gray-700">Supplier Product Code</label>
                                <input type="text" id="supplier_product_code" name="supplier_product_code"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <div class="error-message text-sm text-red-600 hidden"></div>
                            </div>
                            <div>
                                <label for="purchase_price" class="block text-sm font-medium text-gray-700">Purchase Price</label>
                                <input type="number" id="purchase_price" name="purchase_price" step="0.01" min="0"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <div class="error-message text-sm text-red-600 hidden"></div>
                            </div>
                            <div>
                                <label for="tax_rate" class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                                <input type="number" id="tax_rate" name="tax_rate" step="0.01" min="0"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <div class="error-message text-sm text-red-600 hidden"></div>
                            </div>
                            <div>
                                <label for="min_order_quantity" class="block text-sm font-medium text-gray-700">Minimum Order Quantity</label>
                                <input type="number" id="min_order_quantity" name="min_order_quantity" min="1"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <div class="error-message text-sm text-red-600 hidden"></div>
                            </div>
                            <div>
                                <label for="lead_time" class="block text-sm font-medium text-gray-700">Lead Time (Days)</label>
                                <input type="number" id="lead_time" name="lead_time" min="0"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <div class="error-message text-sm text-red-600 hidden"></div>
                            </div>
                            <div>
                                <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                                <textarea id="remarks" name="remarks" rows="3"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                <div class="error-message text-sm text-red-600 hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse flex-shrink-0 sticky bottom-0">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button"
                            onclick="closeProductModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Price Agreement Modal -->
<div id="agreementFormModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg flex flex-col max-h-[90vh]">
                <div class="absolute right-0 top-0 pr-4 pt-4 z-10">
                    <button type="button" onclick="closeAgreementForm()" class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                        <span class="sr-only">closure</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="agreementForm" onsubmit="return saveAgreement(event)" class="flex flex-col h-full">
                    <input type="hidden" id="agreement_product_id" name="product_id">
                    <div class="px-4 pt-5 pb-4 sm:p-6 overflow-y-auto flex-grow">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Add a price agreement</h3>
                                <div class="text-sm text-gray-500 mb-4" id="selectedProductInfo"></div>
                            </div>
                            <div>
                                <label for="discount_type" class="block text-sm font-medium text-gray-700">Discount Type</label>
                                <select id="discount_type" name="discount_type" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        onchange="handleDiscountTypeChange()">
                                    <option value="fixed_price">Fixed price</option>
                                    <option value="discount_rate">Discount rate</option>
                                </select>
                            </div>

                            <div id="fixedPriceField">
                                <label for="price" class="block text-sm font-medium text-gray-700">Fixed price (RM)</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">RM</span>
                                    </div>
                                    <input type="number" id="price" name="price" step="0.01" min="0"
                                           class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div id="discountRateField" class="hidden">
                                <label for="discount_rate" class="block text-sm font-medium text-gray-700">Discount rate (%)</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" id="discount_rate" name="discount_rate" step="0.1" min="0" max="100"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="min_quantity" class="block text-sm font-medium text-gray-700">Minimum quantity</label>
                                <input type="number" id="min_quantity" name="min_quantity" min="1"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">start date</label>
                                    <input type="date" id="start_date" name="start_date"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">End date</label>
                                    <input type="date" id="end_date" name="end_date"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse flex-shrink-0 sticky bottom-0">
                        <button type="submit" 
                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">
                            Save
                        </button>
                        <button type="button" onclick="closeAgreementForm()"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-card {
    @apply bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200;
}

.product-header {
    @apply flex items-center p-4 bg-gray-50 border-b border-gray-200;
}

.product-content {
    @apply p-4;
}

.product-info-grid {
    @apply grid grid-cols-2 md:grid-cols-3 gap-4;
}

.info-group {
    @apply flex flex-col space-y-1;
}

.info-label {
    @apply text-sm font-medium text-gray-500;
}

.info-value {
    @apply text-sm text-gray-900;
}

.product-actions {
    @apply flex justify-end space-x-2 mt-4 pt-4 border-t border-gray-200;
}

.action-button {
    @apply p-1.5 rounded-md hover:bg-gray-100 transition-colors duration-200;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Global variable definition
window.productModal = null;
window.categories = {!! json_encode($categories) !!};
window.products = [];
window.supplierProducts = {!! json_encode($supplier->products) !!};
window.selectedProductId = null;
window.editingProduct = null;

// Reset product form
window.resetProductForm = function() {
    console.log('Reset product form');
    const form = document.getElementById('productForm');
    if (form) {
        form.reset();
    }

    window.selectedProductId = null;
    window.editingProduct = null;
    
    // Hide product form fields
    const formFields = document.getElementById('productFormFields');
    if (formFields) {
        formFields.classList.add('hidden');
    }

    // Clear search input
    const searchInput = document.getElementById('searchProduct');
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Reset the classification filter
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.value = '';
    }
};

// Save the product
window.saveProduct = function(event) {
    event.preventDefault();
    console.log('Save the product...');
    
    if (!window.selectedProductId) {
        console.error('Product not selected');
        alert('Please select a product');
            return false;
        }
        
        // Collect form data
        const formData = {
        product_id: window.selectedProductId,
        supplier_product_code: document.getElementById('supplier_product_code').value,
        purchase_price: parseFloat(document.getElementById('purchase_price').value),
            tax_rate: parseFloat(document.getElementById('tax_rate').value) || 0,
            min_order_quantity: parseInt(document.getElementById('min_order_quantity').value) || 1,
            lead_time: parseInt(document.getElementById('lead_time').value) || 0,
        remarks: document.getElementById('remarks').value
    };
    
    // Verify data
    if (formData.purchase_price <= 0) {
        alert('The purchase price must be greater than0');
        return false;
    }
    
    if (formData.tax_rate < 0 || formData.tax_rate > 100) {
        alert('The tax rate must be0-100between');
        return false;
    }
    
    if (formData.min_order_quantity < 1) {
        alert('The minimum order quantity must be greater than or equal to1');
        return false;
    }
    
    if (formData.lead_time < 0) {
        alert('The delivery cycle cannot be negative');
        return false;
    }
    
    console.log('Submitted form data:', formData);
    
    // Send a request
    const url = window.editingProduct 
        ? `/api/suppliers/{{ $supplier->id }}/products/${window.selectedProductId}`
        : `/api/suppliers/{{ $supplier->id }}/products`;
        
    const method = window.editingProduct ? 'PUT' : 'POST';
    
    fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        console.log('Save successfully:', data);
        window.closeProductModal();
        // Refresh the page to display new data
        window.location.reload();
    })
    .catch(error => {
        console.error('Saving failed:', error);
        alert(error.message || 'Saving failed, please try again');
    });
        
        return false;
};

// Edit Product
window.editProduct = function(productId) {
    console.log('Edit Product:', productId);
    const product = window.supplierProducts.find(p => p.id === productId);
    if (!product) {
        console.error('Product not found');
        return;
    }
    
    window.selectedProductId = productId;
    window.editingProduct = product;
    
    // 显示产品表单
    window.showProductModal();
    
    // 等待产品列表渲染完成
    setTimeout(() => {
        // 查找并选中对应产品的单选按钮
        const productRadio = document.querySelector(`input[name="product_id"][value="${productId}"]`);
        if (productRadio) {
            productRadio.checked = true;
            console.log('Product radio button selected');
        } else {
            // 如果找不到对应的单选按钮，可能需要先搜索产品
            const searchInput = document.getElementById('searchProduct');
            if (searchInput && product.name) {
                searchInput.value = product.name;
                // 触发搜索
                searchInput.dispatchEvent(new Event('input'));
                
                // 再次尝试选中单选按钮
                setTimeout(() => {
                    const productRadio = document.querySelector(`input[name="product_id"][value="${productId}"]`);
                    if (productRadio) {
                        productRadio.checked = true;
                        console.log('Product radio button selected after search');
                    }
                }, 500);
            }
        }
    }, 500);
    
    // 填充表单
    document.getElementById('supplier_product_code').value = product.pivot.supplier_product_code || '';
    document.getElementById('purchase_price').value = product.pivot.purchase_price || '';
    document.getElementById('tax_rate').value = product.pivot.tax_rate || '0';
    document.getElementById('min_order_quantity').value = product.pivot.min_order_quantity || '1';
    document.getElementById('lead_time').value = product.pivot.lead_time || '0';
    document.getElementById('remarks').value = product.pivot.remarks || '';
    
    // 显示表单字段
    const formFields = document.getElementById('productFormFields');
    if (formFields) {
        formFields.classList.remove('hidden');
    }
};

// Delete the product
window.deleteProduct = function(productId) {
    Swal.fire({
        title: 'Confirm deletion',
        text: 'Are you sure you want to delete this product?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Confirm to delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/api/suppliers/{{ $supplier->id }}/products/${productId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                Swal.fire({
                    title: 'Deletion was successful!',
                    text: 'The product has been successfully deleted。',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch(error => {
                Swal.fire(
                    'Deletion failed',
                    error.message || 'Deletion failed, please try again',
                    'error'
                );
            });
        }
    });
};

// Load product data
window.loadProducts = async function() {
    console.log('Start loading product data...');
    try {
        // 使用供应商控制器的products方法获取产品数据
        const response = await fetch('/api/catalog/products');
        if (!response.ok) {
            throw new Error(`Failed to load product data: ${response.status}`);
        }
        const data = await response.json();
        console.log('Successfully obtained product data:', data);
        
        // make sure data It's an array, if not, try to get it data property
        window.products = Array.isArray(data) ? data : (data.data || []);
        
        // Initialize the classification filter
        initializeCategoryFilters();
        
        // Initial display of all products
        window.filterProducts();
    } catch (error) {
        console.error('Failed to load product data:', error);
        window.products = [];
        
        // 使用更友好的错误提示
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: `Failed to load product data: ${error.message}`,
            confirmButtonText: 'Try Again',
            showCancelButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.loadProducts();
            }
        });
    }
};

// Initialize the classification filter
function initializeCategoryFilters() {
    const categoryFilter = document.getElementById('categoryFilter');
    
    const categoryOptions = '<option value="">All categories</option>' + 
        window.categories.map(category => 
            `<option value="${category.id}">${category.name}</option>`
        ).join('');
    
    if (categoryFilter) {
        categoryFilter.innerHTML = categoryOptions;
    }
}

window.showProductModal = function() {
    console.log('Open the product modal box');
    const productModal = document.getElementById('productModal');
    if (!productModal) {
        console.error('Product modal box not found');
        return;
    }
    
    // Make sure product data is loaded
    if (!window.products.length) {
        console.log('Product data is not loading, loading...');
        window.loadProducts();
    }
    
    // Reset the form in non-edit mode
    if (!window.editingProduct) {
        window.resetProductForm();
    }
    
    // Display modal boxes and masks
    productModal.classList.remove('hidden');
    productModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    
    // Initialize data
    window.filterProducts();
};

window.closeProductModal = function() {
    console.log('Close the product modal box');
    const productModal = document.getElementById('productModal');
    if (!productModal) {
        console.error('Product modal box not found');
        return;
    }
    
    productModal.classList.add('hidden');
    productModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    
    window.resetProductForm();
};

window.filterProducts = function(query = '') {
    console.log('Start filtering products and searching for keywords:', query);
    
    const productList = document.getElementById('productList');
    const categoryFilter = document.getElementById('categoryFilter');
    
    if (!productList) {
        console.error('Product List Container Not Found');
        return;
    }

    // Clear the existing list
    productList.innerHTML = '';
    
    // make sure window.products It's an array
    if (!Array.isArray(window.products)) {
        console.error('Invalid product data');
        console.log('window.products:', window.products);
        return;
    }
    
    // Get the selected category
    const selectedCategory = categoryFilter ? categoryFilter.value : '';
    
    // Filter products
    const filteredProducts = window.products.filter(product => {
        if (!product) return false;
        
        const matchesSearch = !query || 
            (product.name && product.name.toLowerCase().includes(query.toLowerCase())) ||
            (product.code && product.code.toLowerCase().includes(query.toLowerCase()));
        
        const matchesCategory = !selectedCategory || 
            (product.category && product.category.id && product.category.id.toString() === selectedCategory);

        return matchesSearch && matchesCategory;
    });
    
    console.log('Filtered product quantity:', filteredProducts.length);
    
    if (filteredProducts.length === 0) {
        productList.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                    No matching product found
                </td>
            </tr>
        `;
        return;
    }
    
    // Display filtered products
    filteredProducts.forEach(product => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        
        // Select column
        const selectCell = document.createElement('td');
        selectCell.className = 'px-4 py-3 whitespace-nowrap';
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = 'product_id';
        radio.value = product.id;
        radio.className = 'focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300';
        radio.onclick = () => {
            console.log('Select a product:', product.id);
            window.selectedProductId = product.id;
            const formFields = document.getElementById('productFormFields');
            if (formFields) {
                formFields.classList.remove('hidden');
            }
        };
        selectCell.appendChild(radio);
        
        // Product Information List
        const infoCell = document.createElement('td');
        infoCell.className = 'px-4 py-3';
        infoCell.innerHTML = `
            <div>
                <div class="text-sm font-medium text-gray-900">${product.name || 'Unnamed products'}</div>
                <div class="text-sm text-gray-500">coding: ${product.code || 'No encoding'}</div>
            </div>
        `;
        
        // Category columns
        const categoryCell = document.createElement('td');
        categoryCell.className = 'px-4 py-3 whitespace-nowrap text-sm text-gray-500';
        categoryCell.textContent = product.category ? product.category.name : 'Uncategorized';
        
        // Price column
        const priceCell = document.createElement('td');
        priceCell.className = 'px-4 py-3 whitespace-nowrap text-sm text-gray-500';
        priceCell.textContent = `RM ${product.selling_price ? parseFloat(product.selling_price).toFixed(2) : '0.00'}`;
        
        // Add all columns to rows
        row.appendChild(selectCell);
        row.appendChild(infoCell);
        row.appendChild(categoryCell);
        row.appendChild(priceCell);
        
        productList.appendChild(row);
    });
};

// Revise handleDiscountTypeChange function
window.handleDiscountTypeChange = function() {
    console.log('Switch discount type...');
    const discountType = document.getElementById('discount_type').value;
    const fixedPriceField = document.getElementById('fixedPriceField');
    const discountRateField = document.getElementById('discountRateField');
    const priceInput = document.getElementById('price');
    const discountRateInput = document.getElementById('discount_rate');
    
    console.log('Current discount type:', discountType);
    console.log('Fixed price field:', fixedPriceField);
    console.log('Discount rate field:', discountRateField);
    
    if (discountType === 'fixed_price') {
        fixedPriceField.classList.remove('hidden');
        discountRateField.classList.add('hidden');
        priceInput.setAttribute('required', 'required');
        discountRateInput.removeAttribute('required');
        discountRateInput.value = '';
    } else if (discountType === 'discount_rate') {
        fixedPriceField.classList.add('hidden');
        discountRateField.classList.remove('hidden');
        discountRateInput.setAttribute('required', 'required');
        priceInput.removeAttribute('required');
        priceInput.value = '';
    }
};

// Revise showAgreementForm function
window.showAgreementForm = function(productId) {
    console.log('Display price agreement form, productID:', productId);
    const modal = document.getElementById('agreementFormModal');
    const form = document.getElementById('agreementForm');
    
    // Reset the form
    form.reset();
    
    // Set up productsID
    document.getElementById('agreement_product_id').value = productId;
    
    // Find and display product information
    const product = window.products.find(p => p.id.toString() === productId.toString());
    if (product) {
        document.getElementById('selectedProductInfo').innerHTML = `
            <div class="flex items-center space-x-2">
                <span class="font-medium">${product.name}</span>
                <span class="text-gray-400">|</span>
                <span>SKU: ${product.code || 'none'}</span>
            </div>
        `;
    }
    
    // Set the default date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    
    const nextYear = new Date();
    nextYear.setFullYear(nextYear.getFullYear() + 1);
    document.getElementById('end_date').value = nextYear.toISOString().split('T')[0];
    
    // Set the default discount type and trigger the toggle event
    const discountTypeSelect = document.getElementById('discount_type');
    discountTypeSelect.value = 'fixed_price';
    handleDiscountTypeChange();
    
    // Display modal box
    modal.classList.remove('hidden');
};

// Revise deleteAgreement Function usage SweetAlert2
window.deleteAgreement = function(agreementId) {
    Swal.fire({
        title: 'Confirm deletion',
        text: 'Are you sure you want to delete this price agreement?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Confirm to delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/api/suppliers/{{ $supplier->id }}/agreements/${agreementId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                Swal.fire({
                    title: 'Deletion was successful!',
                    text: 'The price agreement has been successfully deleted。',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch(error => {
                Swal.fire(
                    'Deletion failed',
                    error.message || 'Deletion failed, please try again',
                    'error'
                );
            });
        }
    });
};

// Add a close modal box function
window.closeAgreementForm = function() {
    console.log('Close the price agreement modal box...');
    const modal = document.getElementById('agreementFormModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
};

// Modify the save price protocol function
window.saveAgreement = function(event) {
    event.preventDefault();
    console.log('Start saving the price agreement...');
    
    const productId = document.getElementById('agreement_product_id').value;
    console.log('productID:', productId);
    
    if (!productId) {
        Swal.fire('mistake', 'Please select a product', 'error');
        return false;
    }
    
    const discountType = document.getElementById('discount_type').value;
    const price = document.getElementById('price').value;
    const discountRate = document.getElementById('discount_rate').value;
    const minQuantity = document.getElementById('min_quantity').value;
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    console.log('Form data:', {
        discountType,
        price,
        discountRate,
        minQuantity,
        startDate,
        endDate
    });
    
    // Collect form data
    const formData = {
        product_id: productId,
        supplier_id: {{ $supplier->id }},
        discount_type: discountType,
        min_quantity: parseInt(minQuantity) || 1,
        start_date: startDate,
        end_date: endDate || null
    };
    
    // Set data according to discount type
    if (discountType === 'fixed_price') {
        if (!price || parseFloat(price) <= 0) {
            Swal.fire('mistake', 'The fixed price must be greater than0', 'error');
            return false;
        }
        formData.price = parseFloat(price);
        formData.discount_rate = null;
    } else if (discountType === 'discount_rate') {
        if (!discountRate || parseFloat(discountRate) < 0 || parseFloat(discountRate) > 100) {
            Swal.fire('mistake', 'The discount rate must be0-100between', 'error');
            return false;
        }
        formData.discount_rate = parseFloat(discountRate);
        formData.price = null;
    }
    
    // Verify required fields
    if (!formData.start_date) {
        Swal.fire('mistake', 'Please select a start date', 'error');
        return false;
    }
    
    if (formData.min_quantity < 1) {
        Swal.fire('mistake', 'The minimum number must be greater than or equal to1', 'error');
        return false;
    }
    
    console.log('Data to be sent:', formData);
    
    // Send a request
    fetch('/api/suppliers/{{ $supplier->id }}/agreements', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(async response => {
        const responseData = await response.json();
        console.log('Server response:', responseData);
        
        if (!response.ok) {
            throw new Error(responseData.message || 'Saving failed');
        }
        return responseData;
    })
    .then(data => {
        console.log('Save successfully:', data);
        Swal.fire({
            title: 'Save successfully!',
            text: 'Price agreement has been successfully created。',
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            window.location.reload();
        });
    })
    .catch(error => {
        console.error('Saving failed:', error);
        Swal.fire('mistake', error.message || 'Saving failed, please try again', 'error');
    });
    
    return false;
};

// Initialize function
window.initialize = function() {
    console.log('Initialize the page...');
    
    // Get modal box reference
    window.productModal = document.getElementById('productModal');
    
    // Set up product search event listener
    const searchProduct = document.getElementById('searchProduct');
    if (searchProduct) {
        searchProduct.addEventListener('input', (e) => window.filterProducts(e.target.value));
    }
    
    // Setting up classification filter event listener
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', () => {
            const searchQuery = document.getElementById('searchProduct')?.value || '';
            window.filterProducts(searchQuery);
        });
    }
    
    // Initial loading of product data
    window.loadProducts();
};

// set up Livewire Navigation hook
if (typeof window.Livewire !== 'undefined') {
    console.log('set up Livewire Navigation hook...');
    window.Livewire.on('navigated', () => {
        console.log('Livewire Navigation is completed, reinitialize...');
        setTimeout(window.initialize, 100);
    });
}

// Initialize now
window.initialize();
</script>
@endpush
@endsection 