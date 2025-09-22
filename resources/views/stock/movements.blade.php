@extends('layouts.app')

@section('title', 'Inventory record')

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
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Inventory record</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6">
        <!-- Product Information -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Product Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Product Name</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $product->name }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Product Category</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $product->category->name }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Current inventory</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $stockService->getProductStock($product) }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Minimum inventory</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $product->min_stock }}</div>
                </div>
            </div>
        </div>

        <!-- Filter criteria -->
        <div class="mb-6">
            <form action="{{ route('stock.movements', ['product_id' => $product->id]) }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-2">storehouse</label>
                    <select name="warehouse_id" id="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All warehouses</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="movement_type" class="block text-sm font-medium text-gray-700 mb-2">Type of change</label>
                    <select name="movement_type" id="movement_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All types</option>
                        <option value="purchase" {{ request('movement_type') === 'purchase' ? 'selected' : '' }}>Purchase and warehouse</option>
                        <option value="sale" {{ request('movement_type') === 'sale' ? 'selected' : '' }}>Sales out of warehouse</option>
                        <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }}>Inventory adjustment</option>
                        <option value="transfer_in" {{ request('movement_type') === 'transfer_in' ? 'selected' : '' }}>Transfer to</option>
                        <option value="transfer_out" {{ request('movement_type') === 'transfer_out' ? 'selected' : '' }}>Transfer out</option>
                    </select>
                </div>

                <div>
                    <label for="date_range" class="block text-sm font-medium text-gray-700 mb-2">Date range</label>
                    <input type="text" name="date_range" id="date_range" value="{{ request('date_range') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Select a date range">
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        filter
                    </button>
                    <a href="{{ route('stock.movements.export', array_merge(request()->query(), ['product_id' => $product->id])) }}" 
                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                        Export records
                    </a>
                </div>
            </form>
        </div>

        <!-- Data table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Change time
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            storehouse
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type of change
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Number of changes
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Unit cost
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total cost
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Batch number
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Related documents
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $movement->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $movement->warehouse->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($movement->quantity > 0)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ __('stock.movement_types.' . $movement->movement_type) }}
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ __('stock.movement_types.' . $movement->movement_type) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($movement->unit_cost, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($movement->total_cost, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $movement->batch_number ?: '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($movement->reference)
                                    @if($movement->reference_type === 'App\Models\Purchase')
                                        <a href="{{ route('purchases.show', $movement->reference) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $movement->reference->purchase_number }}
                                        </a>
                                    @elseif($movement->reference_type === 'App\Models\StockTransfer')
                                        <a href="{{ route('stock.transfers.show', $movement->reference) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $movement->reference->transfer_number }}
                                        </a>
                                    @else
                                        {{ $movement->reference->reference_number }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No inventory record yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $movements->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date range selector
    flatpickr("#date_range", {
        mode: "range",
        dateFormat: "Y-m-d",
        locale: "zh",
    });
});
</script>
@endpush 