@extends('layouts.app')

@section('title', 'Purchase return details')

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
                <span class="text-gray-700">Purchase return details</span>
            </li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Purchase return details</h2>
            <div class="flex space-x-2">
                @if ($purchaseReturn->status === 'pending')
                    <a href="{{ route('purchase-returns.edit', $purchaseReturn) }}" 
                       class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        edit
                    </a>
                    <form action="{{ route('purchase-returns.approve', $purchaseReturn) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            approve
                        </button>
                    </form>
                    <form action="{{ route('purchase-returns.reject', $purchaseReturn) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                            reject
                        </button>
                    </form>
                @endif
                @if ($purchaseReturn->status === 'approved' && $purchaseReturn->refund_status !== 'completed')
                    <a href="{{ route('purchase-refunds.create', ['purchase_return_id' => $purchaseReturn->id]) }}" 
                       class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Refund
                    </a>
                @endif
            </div>
        </div>

        <div class="p-4">
            <!-- Basic information -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Basic information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Return number</span>
                        <p class="mt-1">{{ $purchaseReturn->return_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Purchase Order Number</span>
                        <p class="mt-1">{{ $purchaseReturn->purchase->purchase_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Return date</span>
                        <p class="mt-1">{{ $purchaseReturn->return_date->format('Y-m-d') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">state</span>
                        <p class="mt-1">
                            @switch($purchaseReturn->status)
                                @case('pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending review
                                    </span>
                                    @break
                                @case('approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                    @break
                                @case('rejected')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Rejected
                                    </span>
                                    @break
                                @case('completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Completed
                                    </span>
                                    @break
                            @endswitch
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Refund status</span>
                        <p class="mt-1">
                            @switch($purchaseReturn->refund_status)
                                @case('pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Waiting for refund
                                    </span>
                                    @break
                                @case('partial')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Partial refund
                                    </span>
                                    @break
                                @case('completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Refunded
                                    </span>
                                    @break
                            @endswitch
                        </p>
                    </div>
                </div>
            </div>

            <!-- Reason for return -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Reason for return</h3>
                <p class="text-sm text-gray-600">{{ $purchaseReturn->reason }}</p>
            </div>

            <!-- Return items -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Return items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Return quantity</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">unit price</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">tax rate</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">tax</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($purchaseReturn->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->purchaseItem->product->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->tax_rate }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($item->tax_amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                    total:
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($purchaseReturn->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($purchaseReturn->total_tax_amount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Refund history -->
            @if ($purchaseReturn->refunds->isNotEmpty())
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Refund history</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Refund number</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Refund date</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Refund amount</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment method</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference number</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($purchaseReturn->refunds as $refund)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $refund->refund_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $refund->refund_date->format('Y-m-d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($refund->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $refund->payment_method }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $refund->reference_number }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                        Refunded amount:
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($purchaseReturn->refunded_amount, 2) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                        Remaining refund amount:
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($purchaseReturn->remaining_amount, 2) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Remark -->
            @if ($purchaseReturn->notes)
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Remark</h3>
                    <p class="text-sm text-gray-600">{{ $purchaseReturn->notes }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection 