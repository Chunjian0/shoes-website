<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order Complete') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="text-center mb-8">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                            <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Thank You for Your Order!</h2>
                        <p class="text-gray-600">
                            Your order has been successfully placed and payment has been recorded.
                        </p>
                        <p class="text-gray-600 mt-1">
                            Order Number: <span class="font-medium">{{ $order->order_number }}</span>
                        </p>
                    </div>

                    <div class="border-t border-gray-200 pt-6 mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Order Details</h3>
                        
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
                                            Total
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
                                                        @if($item->specifications)
                                                            <div class="text-sm text-gray-500">
                                                                @foreach(json_decode($item->specifications, true) ?? [] as $key => $value)
                                                                    <span class="mr-2">{{ $key }}: {{ $value }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ number_format($item->price, 2) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $item->quantity }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ number_format($item->price * $item->quantity, 2) }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right font-medium">
                                            Subtotal:
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                                            {{ number_format($order->subtotal, 2) }}
                                        </td>
                                    </tr>
                                    @if($order->discount > 0)
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-right font-medium text-green-600">
                                                Discount:
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-green-600">
                                                -{{ number_format($order->discount, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right font-medium text-lg">
                                            Total:
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-lg">
                                            {{ number_format($order->total, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Customer Information</h3>
                            <div class="space-y-1">
                                <p><span class="font-medium">Name:</span> {{ $order->customer_name }}</p>
                                <p><span class="font-medium">Phone:</span> {{ $order->customer_phone ?? 'N/A' }}</p>
                                <p><span class="font-medium">Email:</span> {{ $order->customer_email ?? 'N/A' }}</p>
                                <p><span class="font-medium">Address:</span> {{ $order->customer_address ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Information</h3>
                            <div class="space-y-1">
                                <p><span class="font-medium">Payment Method:</span> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                                <p><span class="font-medium">Payment Status:</span> 
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Paid
                                    </span>
                                </p>
                                <p><span class="font-medium">Order Date:</span> {{ $order->created_at->format('M d, Y H:i') }}</p>
                                
                                @if($order->coupon_code)
                                    <p><span class="font-medium">Coupon Applied:</span> {{ $order->coupon_code }}</p>
                                @endif
                                
                                @if($order->notes)
                                    <p><span class="font-medium">Order Notes:</span> {{ $order->notes }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Return to Dashboard
                        </a>
                        <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            View Order Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 