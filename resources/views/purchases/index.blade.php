@extends('layouts.app')

@section('title', 'Purchasing Order Management')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page title and creation button -->
    <div class="md:flex md:items-center md:justify-between mb-4">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Purchasing Order Management
            </h2>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            @can('create', App\Models\Purchase::class)
            <a href="{{ route('purchases.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                New purchase order
            </a>
            @endcan
        </div>
    </div>

    <!-- Search and screen -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('purchases.index') }}" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">search</label>
                    <div class="relative rounded-md shadow-sm">
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                               placeholder="Search for purchase order number or supplier">
                    </div>
                </div>
                <div class="w-full sm:w-40">
                    <label for="purchase_status" class="sr-only">Procurement status</label>
                    <select name="purchase_status" id="purchase_status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All procurement status</option>
                        <option value="draft" {{ request('purchase_status') === 'draft' ? 'selected' : '' }}>draft</option>
                        <option value="pending" {{ request('purchase_status') === 'pending' ? 'selected' : '' }}>To be reviewed</option>
                        <option value="approved" {{ request('purchase_status') === 'approved' ? 'selected' : '' }}>Review</option>
                        <option value="rejected" {{ request('purchase_status') === 'rejected' ? 'selected' : '' }}>Refuse</option>
                        <option value="completed" {{ request('purchase_status') === 'completed' ? 'selected' : '' }}>Complete</option>
                        <option value="cancelled" {{ request('purchase_status') === 'cancelled' ? 'selected' : '' }}>Cancel</option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <label for="payment_status" class="sr-only">Payment status</label>
                    <select name="payment_status" id="payment_status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All payment status</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Not pay</option>
                        <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial payment</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <label for="date" class="sr-only">Date</label>
                    <input type="date" name="date" id="date" value="{{ request('date') }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        search
                    </button>
                    <a href="{{ route('purchases.index') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Repossess
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Purchasing order list -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="relative">
            <table class="min-w-full table-fixed divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="w-32 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase order number</th>
                        <th scope="col" class="w-40 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">supplier</th>
                        <th scope="col" class="w-28 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order date</th>
                        <th scope="col" class="w-24 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Procurement status</th>
                        <th scope="col" class="w-24 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment status</th>
                        <th scope="col" class="w-32 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">lump sum</th>
                        <th scope="col" class="w-32 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">storehouse</th>
                        <th scope="col" class="w-28 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">operate</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td class="px-3 py-4 text-sm font-medium text-gray-900 truncate" title="{{ $purchase->purchase_number }}">
                                {{ $purchase->purchase_number }}
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-900 truncate" title="{{ $purchase->supplierItems->pluck('supplier.name')->join(', ') }}">
                                {{ $purchase->supplierItems->pluck('supplier.name')->join(', ') }}
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500">
                                <div class="truncate">{{ $purchase->purchase_date->format('Y-m-d') }}</div>
                                <div class="text-xs text-gray-400 truncate">{{ $purchase->purchase_date->diffForHumans() }}</div>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $purchase->purchase_status->value === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $purchase->purchase_status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $purchase->purchase_status->value === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $purchase->purchase_status->value === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $purchase->purchase_status->value === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $purchase->purchase_status->value === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ $purchase->purchase_status->label() }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $purchase->payment_status->value === 'unpaid' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $purchase->payment_status->value === 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $purchase->payment_status->value === 'paid' ? 'bg-green-100 text-green-800' : '' }}">
                                    {{ $purchase->payment_status->label() }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-900">
                                <div class="truncate">RM {{ number_format($purchase->final_amount, 2) }}</div>
                                @if($purchase->tax_amount > 0)
                                    <div class="text-xs text-gray-500 truncate">Tax: RM {{ number_format($purchase->tax_amount, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500 truncate" title="{{ $purchase->warehouse?->name ?? 'not specified' }}">
                                {{ $purchase->warehouse?->name ?? 'not specified' }}
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    @can('view', $purchase)
                                        <a href="{{ route('purchases.show', $purchase) }}" class="text-indigo-600 hover:text-indigo-900">Check</a>
                                    @endcan
                                    @can('update', $purchase)
                                        <a href="{{ route('purchases.edit', $purchase) }}" class="text-yellow-600 hover:text-yellow-900">edit</a>
                                    @endcan
                                    @can('delete', $purchase)
                                        <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this purchase order?')">delete</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-4 text-sm text-gray-500 text-center">No purchase order data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $purchases->links() }}
        </div>
    </div>
</div>
@endsection 