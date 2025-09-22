<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Order Details') }} - {{ $order->order_number }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('orders.exportPdf', $order) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    {{ __('Export PDF') }}
                </a>
                
                @if($order->status == 'completed')
                <a href="{{ route('returns.create', $order) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path>
                    </svg>
                    {{ __('Create Return') }}
                </a>
                
                @if(!$order->eInvoice)
                <a href="{{ route('orders.createInvoice', $order) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ __('Create E-Invoice') }}
                </a>
                @else
                <a href="{{ route('invoices.show', $order->eInvoice) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ __('View E-Invoice') }}
                </a>
                @endif
                @endif
                
                <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-300 focus:outline-none focus:border-gray-300 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('Back to Orders') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Order Information</h3>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Order Number</p>
                                        <p class="mt-1">{{ $order->order_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Order Date</p>
                                        <p class="mt-1">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Status</p>
                                        <p class="mt-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Payment Status</p>
                                        <p class="mt-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $order->payment_status === 'unpaid' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $order->payment_status === 'partially_paid' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                                {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                            </span>
                                        </p>
                                    </div>
                                    @if($order->payment_method)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Payment Method</p>
                                            <p class="mt-1">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                                        </div>
                                    @endif
                                    @if($order->paid_at)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Paid At</p>
                                            <p class="mt-1">{{ $order->paid_at->format('M d, Y h:i A') }}</p>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Salesperson</p>
                                        <p class="mt-1">{{ $order->salesperson->name }}</p>
                                    </div>
                                </div>
                                
                                @if($order->remarks)
                                    <div class="mt-4">
                                        <p class="text-sm font-medium text-gray-500">Remarks</p>
                                        <p class="mt-1">{{ $order->remarks }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Customer Information</h3>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Name</p>
                                        <p class="mt-1">{{ $order->customer->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Phone</p>
                                        <p class="mt-1">{{ $order->customer->contact_number }}</p>
                                    </div>
                                    @if($order->customer->email)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Email</p>
                                            <p class="mt-1">{{ $order->customer->email }}</p>
                                        </div>
                                    @endif
                                    @if($order->customer->address)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Address</p>
                                            <p class="mt-1">{{ $order->customer->address }}</p>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Member Level</p>
                                        <p class="mt-1">{{ ucfirst($order->customer->member_level) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Order Items</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Price
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Discount
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subtotal
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($item->product && $item->product->getFirstMediaUrl('product_images'))
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <img class="h-10 w-10 rounded-full object-cover" 
                                                                src="{{ $item->product->getFirstMediaUrl('product_images') }}" 
                                                                alt="{{ $item->product_name }}">
                                                        </div>
                                                    @endif
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $item->product_name }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            SKU: {{ $item->product_sku }}
                                                        </div>
                                                        @if($item->specifications)
                                                            <div class="text-sm text-gray-500">
                                                                @foreach($item->specifications as $key => $value)
                                                                    <span class="mr-2">{{ $key }}: {{ $value }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ number_format($item->unit_price, 2) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $item->quantity }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ number_format($item->discount_amount, 2) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ number_format($item->subtotal, 2) }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-right font-medium">
                                            Subtotal:
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                                            {{ number_format($order->subtotal, 2) }}
                                        </td>
                                    </tr>
                                    @if($order->tax_amount > 0)
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-right font-medium">
                                                Tax:
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                                {{ number_format($order->tax_amount, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if($order->discount_amount > 0)
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-right font-medium text-green-600">
                                                Discount:
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-green-600">
                                                -{{ number_format($order->discount_amount, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-right font-medium text-lg">
                                            Total:
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-lg">
                                            {{ number_format($order->total_amount, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    @if($payments->count() > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Payment History</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Method
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Amount
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Reference
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($payments as $payment)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $payment->payment_date->format('M d, Y') }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $payment->payment_date->format('h:i A') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ number_format($payment->amount, 2) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $payment->reference_number ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                        {{ $payment->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                    
                    <div class="flex flex-col md:flex-row gap-4">
                        @if(!in_array($order->status, ['completed', 'cancelled']))
                            <div class="md:w-1/2">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Update Order Status</h3>
                                <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="bg-gray-50 p-4 rounded-md">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="status" id="status" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </div>
                                    <div>
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Update Status
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                        
                        @if($order->payment_status !== 'paid')
                            <div class="md:w-1/2">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Update Payment Status</h3>
                                <form action="{{ route('orders.updatePaymentStatus', $order) }}" method="POST" class="bg-gray-50 p-4 rounded-md">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                                            <select name="payment_status" id="payment_status" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                                <option value="partially_paid" {{ $order->payment_status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                            <select name="payment_method" id="payment_method" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                <option value="cash">Cash</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="check">Check</option>
                                                <option value="credit_card">Credit Card</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                            <input type="number" step="0.01" name="amount" id="amount" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ $order->total_amount - $payments->sum('amount') }}">
                                        </div>
                                        <div>
                                            <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
                                            <input type="date" name="payment_date" id="payment_date" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div>
                                            <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                                            <input type="text" name="reference_number" id="reference_number" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        <div>
                                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                            <input type="text" name="notes" id="notes" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Update Payment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                    
                    @if(!in_array($order->status, ['completed', 'cancelled']))
                        <div class="mt-6">
                            <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order? This will restore the stock.');">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel Order
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    <script src="{{ asset('js/orders.js') }}"></script>
@endpush 