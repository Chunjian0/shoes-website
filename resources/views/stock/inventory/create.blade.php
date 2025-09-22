@extends('layouts.app')

@section('title', 'Create a new inventory list')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                </svg>
                Home page
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
                <a href="{{ route('stock.inventory.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Inventory points</a>
            </div>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Create a new inventory</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm" x-data="inventoryCount()">
    <form action="{{ route('stock.inventory.store') }}" method="POST">
        @csrf
        <div class="p-6">
            <!-- Basic information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-2">storehouse</label>
                    <select name="warehouse_id" id="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
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
                </div>

                <div>
                    <label for="count_date" class="block text-sm font-medium text-gray-700 mb-2">Check out dates</label>
                    <input type="date" name="count_date" id="count_date" value="{{ old('count_date', date('Y-m-d')) }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    @error('count_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Remark</label>
                    <input type="text" name="notes" id="notes" value="{{ old('notes') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           maxlength="255">
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Inventory details -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Inventory details</h3>
                    <div class="flex space-x-2">
                        <button type="button" @click="loadProducts()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                            Loading the product
                        </button>
                        <button type="button" @click="addItem()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            Add products
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">merchandise</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System inventory</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual inventory</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number of differences</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch number</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remark</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">operate</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td class="px-6 py-4">
                                        <select x-model="item.product_id" :name="'items['+index+'][product_id]'" 
                                                @change="updateSystemStock(index)"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                            <option value="">Please select a product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-stock="{{ $stockService->getProductStock($product) }}">
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" x-model="item.system_stock" :name="'items['+index+'][system_stock]'" 
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" readonly>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" x-model="item.actual_stock" :name="'items['+index+'][actual_stock]'" 
                                               @input="updateDifference(index)"
                                               step="0.01" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" x-model="item.difference" :name="'items['+index+'][difference]'" 
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" readonly>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" x-model="item.batch_number" :name="'items['+index+'][batch_number]'" 
                                               maxlength="50" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" x-model="item.notes" :name="'items['+index+'][notes]'" 
                                               maxlength="255" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">delete</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                @error('items')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('stock.inventory.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">keep</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function inventoryCount() {
    return {
        items: [],
        addItem() {
            this.items.push({
                product_id: '',
                system_stock: 0,
                actual_stock: 0,
                difference: 0,
                batch_number: '',
                notes: ''
            });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        updateSystemStock(index) {
            const item = this.items[index];
            const select = document.querySelector(`select[name="items[${index}][product_id]"]`);
            const option = select.options[select.selectedIndex];
            if (option) {
                item.system_stock = parseFloat(option.dataset.stock) || 0;
                this.updateDifference(index);
            }
        },
        updateDifference(index) {
            const item = this.items[index];
            item.difference = (parseFloat(item.actual_stock) || 0) - (parseFloat(item.system_stock) || 0);
        },
        async loadProducts() {
            const warehouseId = document.getElementById('warehouse_id').value;
            if (!warehouseId) {
                alert('Please select the warehouse first');
                return;
            }

            try {
                const response = await fetch(`/api/stock/warehouse/${warehouseId}/products`);
                const data = await response.json();
                
                this.items = data.map(product => ({
                    product_id: product.id,
                    system_stock: product.stock,
                    actual_stock: product.stock,
                    difference: 0,
                    batch_number: '',
                    notes: ''
                }));
            } catch (error) {
                console.error('Failed to load the product:', error);
                alert('Failed to load the product,Please try again');
            }
        }
    }
}
</script>
@endpush
@endsection 