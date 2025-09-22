@extends('layouts.app')

@section('title', 'Edit Purchase Return')

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
                <span class="text-gray-700">Edit Purchase Return</span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm" x-data="returnForm()">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Edit Purchase Return</h2>
        </div>

        <form action="{{ route('purchase-returns.update', $purchaseReturn) }}" method="POST" class="p-4">
            @csrf
            @method('PUT')

            <!-- Basic information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Order</label>
                    <p class="text-gray-900">{{ $purchaseReturn->purchase->purchase_number }}</p>
                </div>

                <div>
                    <label for="return_date" class="block text-sm font-medium text-gray-700 mb-1">Return date</label>
                    <input type="date" name="return_date" id="return_date" 
                           class="form-input w-full rounded-md border-gray-300"
                           value="{{ old('return_date', $purchaseReturn->return_date->format('Y-m-d')) }}">
                    @error('return_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Reason for return -->
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for return</label>
                <textarea name="reason" id="reason" rows="3" 
                          class="form-textarea w-full rounded-md border-gray-300">{{ old('reason', $purchaseReturn->reason) }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Return items -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Return items</h3>
                @foreach ($purchaseReturn->items as $index => $item)
                    <div class="border rounded-md p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Procurement Projects</label>
                                <p class="text-gray-900">{{ $item->purchaseItem->product->name }}</p>
                                <input type="hidden" name="items[{{ $index }}][purchase_item_id]" value="{{ $item->purchase_item_id }}">
                            </div>

                            <div>
                                <label for="items[{{ $index }}][quantity]" class="block text-sm font-medium text-gray-700 mb-1">Return quantity</label>
                                <input type="number" name="items[{{ $index }}][quantity]" id="items[{{ $index }}][quantity]"
                                       class="form-input w-full rounded-md border-gray-300"
                                       value="{{ old("items.{$index}.quantity", $item->quantity) }}"
                                       x-model="items[{{ $index }}].quantity"
                                       x-on:input="updateItemAmount({{ $index }})">
                                @error("items.{$index}.quantity")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="items[{{ $index }}][unit_price]" class="block text-sm font-medium text-gray-700 mb-1">unit price</label>
                                <input type="number" name="items[{{ $index }}][unit_price]" id="items[{{ $index }}][unit_price]"
                                       class="form-input w-full rounded-md border-gray-300"
                                       value="{{ old("items.{$index}.unit_price", $item->unit_price) }}"
                                       step="0.01"
                                       x-model="items[{{ $index }}].unit_price"
                                       x-on:input="updateItemAmount({{ $index }})">
                                @error("items.{$index}.unit_price")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="items[{{ $index }}][tax_rate]" class="block text-sm font-medium text-gray-700 mb-1">tax rate (%)</label>
                                <input type="number" name="items[{{ $index }}][tax_rate]" id="items[{{ $index }}][tax_rate]"
                                       class="form-input w-full rounded-md border-gray-300"
                                       value="{{ old("items.{$index}.tax_rate", $item->tax_rate) }}"
                                       step="0.01"
                                       x-model="items[{{ $index }}].tax_rate"
                                       x-on:input="updateItemAmount({{ $index }})">
                                @error("items.{$index}.tax_rate")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                <p class="text-gray-900" x-text="formatCurrency(items[{{ $index }}].amount)"></p>
                                <input type="hidden" name="items[{{ $index }}][amount]" x-model="items[{{ $index }}].amount">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">tax</label>
                                <p class="text-gray-900" x-text="formatCurrency(items[{{ $index }}].tax_amount)"></p>
                                <input type="hidden" name="items[{{ $index }}][tax_amount]" x-model="items[{{ $index }}].tax_amount">
                            </div>
                        </div>
                    </div>
                @endforeach

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
                          class="form-textarea w-full rounded-md border-gray-300">{{ old('notes', $purchaseReturn->notes) }}</textarea>
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
                items: @json($purchaseReturn->items->map(function($item) {
                    return [
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_rate' => $item->tax_rate,
                        'amount' => $item->amount,
                        'tax_amount' => $item->tax_amount
                    ];
                })),
                totalAmount: {{ $purchaseReturn->total_amount }},
                totalTaxAmount: {{ $purchaseReturn->total_tax_amount }},

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