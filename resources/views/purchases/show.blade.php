@extends('layouts.app')

@section('title', 'Purchase Order Details')

@push('styles')
    <!-- Add to SweetAlert2 style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css">
@endpush

@push('scripts')
    <!-- Add to jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Add to SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Page title and operation button -->
    <div class="md:flex md:items-center md:justify-between mb-4">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Purchase Order Details
            </h2>
        </div>
        <div class="mt-4 flex justify-end space-x-3 md:mt-0 md:ml-4">
            @if($purchase->purchase_status->value === 'approved' || $purchase->purchase_status->value === 'received' || $purchase->purchase_status->value === 'partially_received')
                <button type="button"
                    onclick="showPdfModal()"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    ExportPDF
                </button>
            @endif
            @can('update', $purchase)
                <a href="{{ route('purchases.edit', $purchase) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Edit order
                </a>
            @endcan

            @if($purchase->purchase_status->value === 'pending')
                @can('approve', $purchase)
                    <form action="{{ route('purchases.approve', $purchase) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Approve
                        </button>
                    </form>
                @endcan

                @can('reject', $purchase)
                    <button type="button" 
                        onclick="rejectPurchase({{ $purchase->id }})" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Reject
                    </button>
                @endcan
            @endif

            @if($purchase->purchase_status->value === 'approved' || $purchase->purchase_status->value === 'partially_received')
                @can('confirmReceived', $purchase)
                    <button type="button" 
                        onclick="showReceiveModal()"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Receipt Goods
                    </button>
                @endcan
            @endif
            
            @if($purchase->purchase_status->value === 'approved')
                <button type="button" 
                    onclick="showSendModal()"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Send orders to suppliers
                </button>
            @endif

            @can('cancel', $purchase)
                <button type="button" 
                    onclick="showConfirmDialog('Cancel order', 'Are you sure you want to cancel this purchase order?', () => document.getElementById('cancel-form-{{ $purchase->id }}').submit())" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Cancel order
                </button>
                <form id="cancel-form-{{ $purchase->id }}" action="{{ route('purchases.cancel', $purchase) }}" method="POST" class="hidden">
                    @csrf
                </form>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-md bg-green-50 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-md bg-red-50 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Order information card -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Basic information card -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Basic information</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-2 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Purchase order number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $purchase->purchase_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">supplier</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $purchase->supplierItems->pluck('supplier.name')->join(', ') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">storehouse</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $purchase->warehouse?->name ?? 'not specified' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Purchase date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $purchase->purchase_date->format('Y-m-d H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Inspection status</dt>
                        <dd class="mt-1">
                            @if($purchase->inspection_status)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    bg-{{ $purchase->inspection_status->color() }}-100 
                                    text-{{ $purchase->inspection_status->color() }}-800">
                                    {{ $purchase->inspection_status->label() }}
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Not tested
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Order Status</dt></dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $purchase->purchase_status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $purchase->purchase_status->value === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $purchase->purchase_status->value === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $purchase->purchase_status->value === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}">
                                {{ $purchase->purchase_status->label() }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Amount information card -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Amount information</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <dl class="grid grid-cols-2 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total product</dt>
                        <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($purchase->total_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">tax</dt>
                        <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($purchase->tax_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">freight</dt>
                        <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($purchase->shipping_fee, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Final amount</dt>
                        <dd class="mt-1 text-lg font-semibold text-indigo-600">RM {{ number_format($purchase->final_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Payment status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $purchase->payment_status->value === 'unpaid' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $purchase->payment_status->value === 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $purchase->payment_status->value === 'paid' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ $purchase->payment_status->label() }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Product list card -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Product list</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">merchandise</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">quantity</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">unit price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">tax rate</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">tax</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        @if($purchase->purchase_status->value === 'approved')
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected to arrive</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM {{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->tax_rate, 2) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM {{ number_format($item->tax_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM {{ number_format($item->discount_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM {{ number_format($item->total_amount, 2) }}</td>
                            @if($purchase->purchase_status->value === 'approved')
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($item->expected_delivery_at)
                                    {{ $item->expected_delivery_at->format('Y-m-d H:i:s') }}
                                    <div class="text-xs text-gray-500">
                                        Delivery cycle: {{ $item->lead_time }} day
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Total commodity:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM {{ number_format($purchase->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">tax:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM {{ number_format($purchase->tax_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">freight:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM {{ number_format($purchase->shipping_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Total amount:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">RM {{ number_format($purchase->final_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Payment document card -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Payment record</h3>
                @can('create', [App\Models\Payment::class, $purchase])
                    <a href="{{ route('purchases.payments.create', $purchase) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Add payment
                    </a>
                @endcan
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment order number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment method</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">operate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($purchase->payments as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->payment_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->payment_method->label() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM {{ number_format($payment->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->reference_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @can('view', $payment)
                                        <a href="{{ route('purchases.payments.show', [$purchase, $payment]) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Check</a>
                                    @endcan

                                    @can('delete', $payment)
                                        <form id="payment-delete-{{ $payment->id }}" action="{{ route('purchases.payments.destroy', [$purchase, $payment]) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="text-red-600 hover:text-red-900" onclick="confirmDeletePayment('{{ $payment->id }}')">delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No payment record</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Remark -->
    @if ($purchase->notes)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Remark</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $purchase->notes }}</p>
            </div>
        </div>
    @endif

    <!-- The bottom operation button -->
    <div class="mt-6 flex items-center justify-between">
        <a href="{{ route('purchases.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Return list
        </a>
    </div>
</div>

<!-- Send to Supplier Modal -->
<div id="sendModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Select Suppliers</h3>
                <form id="sendForm" action="{{ route('purchases.send-to-supplier', $purchase) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        @foreach($purchase->supplierItems as $supplierItem)
                        <div class="flex items-center">
                            <input type="checkbox" 
                                id="supplier_{{ $supplierItem->supplier_id }}" 
                                name="supplier_ids[]" 
                                value="{{ $supplierItem->supplier_id }}"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="supplier_{{ $supplierItem->supplier_id }}" class="ml-2 block text-sm text-gray-900">
                                {{ $supplierItem->supplier->name }}
                                @if($supplierItem->email_sent)
                                    <span class="ml-2 text-xs text-green-600">(Already sent)</span>
                                @endif
                            </label>
                        </div>
                        @endforeach
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="submitSendForm()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Send
                </button>
                <button type="button" onclick="hideSendModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- PDF Export Modal -->
<div id="pdfModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Select Suppliers for PDF Export</h3>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" 
                            id="all_suppliers" 
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            onchange="toggleAllSuppliers(this)">
                        <label for="all_suppliers" class="ml-2 block text-sm text-gray-900">
                            All Suppliers
                        </label>
                    </div>
                    <hr>
                    @foreach($purchase->supplierItems as $supplierItem)
                    <div class="flex items-center">
                        <input type="checkbox" 
                            id="pdf_supplier_{{ $supplierItem->supplier_id }}" 
                            name="pdf_supplier_ids[]" 
                            value="{{ $supplierItem->supplier_id }}"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded supplier-checkbox">
                        <label for="pdf_supplier_{{ $supplierItem->supplier_id }}" class="ml-2 block text-sm text-gray-900">
                            {{ $supplierItem->supplier->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="exportSelectedPdf()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Export
                </button>
                <button type="button" onclick="hidePdfModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm delivery mode box -->
<div id="receiveModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6">
                <div class="flex items-center justify-between mb-6 border-b border-gray-200 pb-3">
                    <h3 class="text-xl font-semibold text-gray-900">Confirm the receipt of goods</h3>
                    <button type="button" onclick="hideReceiveModal()" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form id="receiveForm" action="{{ route('purchases.confirm-received', $purchase) }}" method="POST">
                    @csrf
                    <div class="space-y-4 max-h-[calc(100vh-300px)] overflow-y-auto px-1">
                        @foreach ($purchase->items as $item)
                            @php
                                $remainingQuantity = $item->quantity - ($item->received_quantity ?? 0);
                            @endphp
                            @if($remainingQuantity > 0)
                                <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors duration-150">
                                    <div class="flex items-start space-x-4">
                                        <div class="flex-shrink-0">
                                            <input type="checkbox" 
                                                id="item_{{ $item->id }}"
                                                name="selected_items[]" 
                                                value="{{ $item->id }}"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                                onchange="handleItemSelection(this, {{ $remainingQuantity }})">
                                        </div>
                                        <div class="flex-grow">
                                            <label for="item_{{ $item->id }}" class="block text-sm">
                                                <span class="font-medium text-gray-900">{{ $item->product->name }}</span>
                                                <span class="text-gray-500 ml-2">(SKU: {{ $item->product->sku }})</span>
                                            </label>
                                            <div class="mt-2 grid grid-cols-3 gap-4">
                                                <div class="text-sm">
                                                    <span class="text-gray-500">Order quantity:</span>
                                                    <span class="font-medium text-gray-900">{{ $item->quantity }}</span>
                                                </div>
                                                <div class="text-sm">
                                                    <span class="text-gray-500">Received:</span>
                                                    <span class="font-medium text-gray-900">{{ $item->received_quantity ?? 0 }}</span>
                                                </div>
                                                <div class="text-sm">
                                                    <span class="text-gray-500">To be received:</span>
                                                    <span class="font-medium text-green-600">{{ $remainingQuantity }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <input type="number" 
                                                    name="received_quantities[{{ $item->id }}]" 
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    min="0"
                                                    max="{{ $remainingQuantity }}"
                                                    value="{{ $remainingQuantity }}"
                                                    disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="mt-6 bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                            onclick="submitReceiveForm()" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirm the receipt of goods
                        </button>
                        <button type="button" 
                            onclick="hideReceiveModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                    <div class="mt-3 px-6 py-2 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                    id="auto_create_inspection" 
                                    name="auto_create_inspection" 
                                    value="1"
                                    {{ config('settings.auto_create_inspection', true) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="auto_create_inspection" class="ml-2 block text-sm text-gray-900">
                                    Automatically create quality inspection record
                                </label>
                            </div>
                            <button type="button" 
                                onclick="resetAutoInspectionPreference()" 
                                class="text-xs text-indigo-600 hover:text-indigo-800">
                                Reset to default
                            </button>
                        </div>
                        <p class="mt-1 pl-6 text-xs text-gray-500">
                            When enabled, the system will automatically create quality inspection records for received items.
                            @if(config('settings.auto_approve_inspection', true))
                            <span class="text-indigo-600">Quality inspections will be automatically approved and items will be added to inventory.</span>
                            @else
                            <span>Quality inspections will be created but need to be manually approved before items are added to inventory.</span>
                            @endif
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// GM confirmation dialog box
function showConfirmDialog(title, text, callback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sure',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'text-white'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}

// Refusing to purchase orders
function rejectPurchase(purchaseId) {
    console.log('Start refusing to purchase orders:', purchaseId);
    
    showConfirmDialog('Reject Purchase Order', 'Are you sure you want to reject this purchase order?', function() {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch(`/purchases/${purchaseId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Refusal purchase order response:', data);
            if (data.status === 'success') {
                Swal.fire({
                    title: 'Success',
                    text: data.message || 'Purchase order rejected successfully',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: {
                        confirmButton: 'text-white'
                    }
                }).then(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Reject purchase error:', error);
            Swal.fire({
                title: 'Error',
                text: error.message || 'System error, please try again later',
                icon: 'error',
                customClass: {
                    confirmButton: 'text-white'
                }
            });
        });
    });
}

// Delete payment record confirmation
function confirmDeletePayment(paymentId) {
    showConfirmDialog('Delete payment record', 'Are you sure you want to delete the payment record?', () => {
        document.getElementById(`payment-delete-${paymentId}`).submit();
    });
}

function showSendModal() {
    document.getElementById('sendModal').classList.remove('hidden');
}

function hideSendModal() {
    document.getElementById('sendModal').classList.add('hidden');
}

function submitSendForm() {
    const form = document.getElementById('sendForm');
    const selectedSuppliers = form.querySelectorAll('input[name="supplier_ids[]"]:checked');
    
    if (selectedSuppliers.length === 0) {
        Swal.fire({
            title: 'Error',
            text: 'Please select at least one supplier',
            icon: 'error',
            customClass: {
                confirmButton: 'text-white'
            }
        });
        return;
    }
    
    form.submit();
}

function showPdfModal() {
    document.getElementById('pdfModal').classList.remove('hidden');
}

function hidePdfModal() {
    document.getElementById('pdfModal').classList.add('hidden');
}

function toggleAllSuppliers(checkbox) {
    const supplierCheckboxes = document.querySelectorAll('.supplier-checkbox');
    supplierCheckboxes.forEach(cb => cb.checked = checkbox.checked);
}

function exportSelectedPdf() {
    const selectedSuppliers = Array.from(document.querySelectorAll('.supplier-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selectedSuppliers.length === 0) {
        Swal.fire({
            title: 'Error',
            text: 'Please select at least one supplier',
            icon: 'error',
            customClass: {
                confirmButton: 'text-white'
            }
        });
        return;
    }
    
    const queryString = selectedSuppliers.map(id => `supplier_ids[]=${id}`).join('&');
    window.open(`{{ route('purchases.export-pdf', $purchase) }}?${queryString}`, '_blank');
}

// Add a function related to confirming receipt
function showReceiveModal() {
    console.log('Showing receive modal'); // Debug log
    document.getElementById('receiveModal').classList.remove('hidden');
}

function hideReceiveModal() {
    console.log('Hiding receive modal'); // Debug log
    document.getElementById('receiveModal').classList.add('hidden');
}

function handleItemSelection(checkbox, remainingQuantity) {
    // Disable all input boxes
    document.querySelectorAll('input[name^="received_quantities"]').forEach(input => {
        input.disabled = true;
        input.value = input.max; // Reset to maximum value
    });
    
    // Enable the selected input box
    const selectedInput = document.querySelector(`input[name="received_quantities[${checkbox.value}]"]`);
    if (selectedInput) {
        selectedInput.disabled = false;
        selectedInput.focus();
    }
}

function submitReceiveForm() {
    const form = document.getElementById('receiveForm');
    const selectedItems = form.querySelectorAll('input[name="selected_items[]"]:checked');
    
    if (selectedItems.length === 0) {
        Swal.fire({
            title: 'Error',
            text: 'Please select the product to receive',
            icon: 'error',
            customClass: {
                confirmButton: 'text-white'
            }
        });
        return;
    }
    
    // 确保自动创建质检选项的状态正确
    const autoInspectionCheckbox = document.getElementById('auto_create_inspection');
    if (autoInspectionCheckbox) {
        // 如果用户之前有设置过偏好，则使用用户的偏好
        const userPreference = localStorage.getItem('auto_create_inspection');
        if (userPreference !== null) {
            autoInspectionCheckbox.checked = userPreference === 'true';
        }
    }
    
    Swal.fire({
        title: 'Confirm Receipt',
        text: 'Are you sure you want to submit the delivery information?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sure',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'text-white'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}

// 添加新函数用于取消订单
function cancelPurchase(purchaseId) {
    showConfirmDialog('Cancel Order', 'Are you sure you want to cancel this purchase order?', function() {
        document.getElementById(`cancel-form-${purchaseId}`).submit();
    });
}

// 保存自动创建质量检验选项的状态到 localStorage
function saveAutoInspectionPreference() {
    const checkbox = document.getElementById('auto_create_inspection');
    if (checkbox) {
        localStorage.setItem('auto_create_inspection', checkbox.checked ? 'true' : 'false');
        console.log('Saved auto inspection preference:', checkbox.checked); // 添加调试日志
    }
}

// 在页面加载时，从 localStorage 读取用户的偏好设置
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('auto_create_inspection');
    if (checkbox) {
        // 如果用户之前有设置过偏好，则使用用户的偏好
        const userPreference = localStorage.getItem('auto_create_inspection');
        if (userPreference !== null) {
            checkbox.checked = userPreference === 'true';
            console.log('Loaded auto inspection preference:', checkbox.checked); // 添加调试日志
        } else {
            // 如果用户没有设置过偏好，则使用系统默认设置
            const defaultSetting = {{ config('settings.auto_create_inspection', true) ? 'true' : 'false' }};
            localStorage.setItem('auto_create_inspection', defaultSetting);
            console.log('Using default auto inspection setting:', defaultSetting); // 添加调试日志
        }
        
        // 监听复选框变化事件，保存用户的选择
        checkbox.addEventListener('change', saveAutoInspectionPreference);
    }
});

// 添加重置自动创建质检偏好的函数
function resetAutoInspectionPreference() {
    // 移除localStorage中的设置
    localStorage.removeItem('auto_create_inspection');
    
    // 重置为系统默认设置
    const checkbox = document.getElementById('auto_create_inspection');
    if (checkbox) {
        checkbox.checked = {{ config('settings.auto_create_inspection', true) ? 'true' : 'false' }};
        console.log('Reset auto inspection preference to default:', checkbox.checked);
    }
    
    // 显示提示
    Swal.fire({
        title: 'Reset Successful',
        text: 'The auto-create inspection setting has been reset to system default',
        icon: 'success',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        customClass: {
            popup: 'colored-toast'
        }
    });
}
</script>
@endpush 