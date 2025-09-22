@extends('layouts.app')

@section('title', 'Newly built purchase returns')

@section('breadcrumb')
    <nav class="text-sm">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li class="flex items-center">
                <a href="{{ route('purchase-returns.index') }}" class="text-gray-500 hover:text-gray-700">Purchase return list</a>
                <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
            </li>
            <li>
                <span class="text-gray-700">Newly built purchase returns</span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm" x-data="returnForm()">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Newly built purchase returns</h2>
        </div>

        <form action="{{ route('purchase-returns.store') }}" method="POST" class="p-4">
            @csrf

            <!-- Basic information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="purchase_id" class="block text-sm font-medium text-gray-700 mb-1">Purchase Order</label>
                    <select name="purchase_id" id="purchase_id" class="form-select w-full rounded-md border-gray-300" 
                            x-on:change="loadPurchaseItems($event.target.value)">
                        <option value="">Please select a purchase order</option>
                        @foreach ($purchases as $purchase)
                            <option value="{{ $purchase->id }}" {{ old('purchase_id') == $purchase->id ? 'selected' : '' }}>
                                {{ $purchase->purchase_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('purchase_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="return_date" class="block text-sm font-medium text-gray-700 mb-1">Return date</label>
                    <input type="date" name="return_date" id="return_date" 
                           class="form-input w-full rounded-md border-gray-300"
                           value="{{ old('return_date', date('Y-m-d')) }}">
                    @error('return_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Reason for return -->
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for return</label>
                <textarea name="reason" id="reason" rows="3" 
                          class="form-textarea w-full rounded-md border-gray-300">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Return items -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-medium text-gray-700">Return items</h3>
                    <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800"
                            x-on:click="addItem()">
                        Add a project
                    </button>
                </div>

                <template x-if="items.length === 0">
                    <p class="text-sm text-gray-500">No returns yet</p>
                </template>

                <template x-for="(item, index) in items" :key="index">
                    <div class="border rounded-md p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label :for="'items['+index+'][purchase_item_id]'" class="block text-sm font-medium text-gray-700 mb-1">Procurement Projects</label>
                                <select :name="'items['+index+'][purchase_item_id]'" :id="'items['+index+'][purchase_item_id]'"
                                        class="form-select w-full rounded-md border-gray-300"
                                        x-model="item.purchase_item_id"
                                        x-on:change="updateItemDetails(index)">
                                    <option value="">Please select a procurement project</option>
                                    <template x-for="option in purchaseItems" :key="option.id">
                                        <option :value="option.id" x-text="option.product.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label :for="'items['+index+'][quantity]'" class="block text-sm font-medium text-gray-700 mb-1">Return quantity</label>
                                <input type="number" :name="'items['+index+'][quantity]'" :id="'items['+index+'][quantity]'"
                                       class="form-input w-full rounded-md border-gray-300"
                                       x-model="item.quantity"
                                       x-on:input="updateItemAmount(index)">
                            </div>

                            <div>
                                <label :for="'items['+index+'][unit_price]'" class="block text-sm font-medium text-gray-700 mb-1">unit price</label>
                                <input type="number" :name="'items['+index+'][unit_price]'" :id="'items['+index+'][unit_price]'"
                                       class="form-input w-full rounded-md border-gray-300"
                                       x-model="item.unit_price"
                                       step="0.01"
                                       x-on:input="updateItemAmount(index)">
                            </div>

                            <div>
                                <label :for="'items['+index+'][tax_rate]'" class="block text-sm font-medium text-gray-700 mb-1">tax rate (%)</label>
                                <input type="number" :name="'items['+index+'][tax_rate]'" :id="'items['+index+'][tax_rate]'"
                                       class="form-input w-full rounded-md border-gray-300"
                                       x-model="item.tax_rate"
                                       step="0.01"
                                       x-on:input="updateItemAmount(index)">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                <p class="text-gray-900" x-text="formatCurrency(item.amount)"></p>
                                <input type="hidden" :name="'items['+index+'][amount]'" x-model="item.amount">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">tax</label>
                                <p class="text-gray-900" x-text="formatCurrency(item.tax_amount)"></p>
                                <input type="hidden" :name="'items['+index+'][tax_amount]'" x-model="item.tax_amount">
                            </div>
                        </div>

                        <div class="mt-2 flex justify-end">
                            <button type="button" class="text-sm text-red-600 hover:text-red-800"
                                    x-on:click="removeItem(index)">
                                delete
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Total amount -->
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="text-right">
                            <span class="text-sm font-medium text-gray-700">Total amount:</span>
                            <span class="text-gray-900" x-text="formatCurrency(totalAmount)"></span>
                            <input type="hidden" name="total_amount" x-model="totalAmount">
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-medium text-gray-700">Total tax amount:</span>
                            <span class="text-gray-900" x-text="formatCurrency(totalTaxAmount)"></span>
                            <input type="hidden" name="total_tax_amount" x-model="totalTaxAmount">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remark -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Remark</label>
                <textarea name="notes" id="notes" rows="3" 
                          class="form-textarea w-full rounded-md border-gray-300">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    keep
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function returnForm() {
            return {
                items: [],
                purchaseItems: [],
                totalAmount: 0,
                totalTaxAmount: 0,

                async loadPurchaseItems(purchaseId) {
                    if (!purchaseId) {
                        this.purchaseItems = [];
                        this.items = [];
                        return;
                    }

                    try {
                        const response = await fetch(`/api/purchases/${purchaseId}/items`);
                        const data = await response.json();
                        this.purchaseItems = data;
                    } catch (error) {
                        console.error('Error loading purchase items:', error);
                    }
                },

                addItem() {
                    this.items.push({
                        purchase_item_id: '',
                        quantity: 0,
                        unit_price: 0,
                        tax_rate: 0,
                        amount: 0,
                        tax_amount: 0
                    });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                    this.calculateTotals();
                },

                updateItemDetails(index) {
                    const item = this.items[index];
                    const purchaseItem = this.purchaseItems.find(pi => pi.id == item.purchase_item_id);
                    if (purchaseItem) {
                        item.unit_price = purchaseItem.unit_price;
                        item.tax_rate = purchaseItem.tax_rate;
                        this.updateItemAmount(index);
                    }
                },

                updateItemAmount(index) {
                    const item = this.items[index];
                    item.amount = item.quantity * item.unit_price;
                    item.tax_amount = item.amount * (item.tax_rate / 100);
                    this.calculateTotals();
                },

                calculateTotals() {
                    this.totalAmount = this.items.reduce((sum, item) => sum + item.amount, 0);
                    this.totalTaxAmount = this.items.reduce((sum, item) => sum + item.tax_amount, 0);
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('zh-CN', {
                        style: 'currency',
                        currency: 'CNY'
                    }).format(value);
                }
            }
        }
    </script>
    @endpush
@endsection 