@extends('layouts.app')

@section('title', 'Inventory details')

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
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Inventory details</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6">
        <!-- Head operation area -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900">Inventory details</h2>
            <div class="flex space-x-2">
                @if($inventory->status === 'pending')
                    <form action="{{ route('stock.inventory.complete', $inventory) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg" 
                                onclick="return confirm('Are you sure you want to complete this inventory?The inventory will be automatically adjusted after completion.')">
                            Complete inventory
                        </button>
                    </form>
                    <form action="{{ route('stock.inventory.cancel', $inventory) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg"
                                onclick="return confirm('Are you sure you want to cancel the inventory?')">
                            Cancel the inventory
                        </button>
                    </form>
                @endif
                <a href="{{ route('stock.inventory.export', $inventory) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                    Export details
                </a>
            </div>
        </div>

        <!-- Basic information -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic information</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Inventory order numbers</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $inventory->inventory_number }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">storehouse</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $inventory->warehouse->name }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Check out dates</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $inventory->count_date->format('Y-m-d') }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">state</label>
                    <div class="mt-1">
                        @if($inventory->status === 'pending')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        @elseif($inventory->status === 'completed')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Completed
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Canceled
                            </span>
                        @endif
                    </div>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-500">Remark</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $inventory->notes ?: 'none' }}</div>
                </div>
            </div>
        </div>

        <!-- Inventory details -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Inventory details</h3>
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
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($inventory->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->product->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->system_stock }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->actual_stock }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $item->difference != 0 ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $item->difference }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->batch_number ?: 'none' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->notes ?: 'none' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Operation record -->
        @if($inventory->logs->isNotEmpty())
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Operation record</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operation time</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operation content</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($inventory->logs as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->description }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Return button -->
        <div class="mt-6">
            <a href="{{ route('stock.inventory.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">
                Return to the list
            </a>
        </div>
    </div>
</div>
@endsection 