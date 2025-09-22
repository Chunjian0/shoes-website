<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Confirm Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
                        
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
                                    @foreach($cart->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($item->product->getFirstMediaUrl('product_images'))
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <img class="h-10 w-10 rounded-full object-cover" 
                                                                src="{{ $item->product->getFirstMediaUrl('product_images') }}" 
                                                                alt="{{ $item->product->name }}">
                                                        </div>
                                                    @endif
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $item->product->name }}
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
                                            {{ number_format($subtotal, 2) }}
                                        </td>
                                    </tr>
                                    @if($discount > 0)
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-right font-medium text-green-600">
                                                Discount:
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-green-600">
                                                -{{ number_format($discount, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right font-medium text-lg">
                                            Total:
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-lg">
                                            {{ number_format($total, 2) }}
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
                                <p><span class="font-medium">Name:</span> {{ $customer->name }}</p>
                                <p><span class="font-medium">Phone:</span> {{ $customer->phone ?? 'N/A' }}</p>
                                <p><span class="font-medium">Email:</span> {{ $customer->email ?? 'N/A' }}</p>
                                <p><span class="font-medium">Address:</span> {{ $customer->address ?? 'N/A' }}</p>
                                <p><span class="font-medium">Member Level:</span> {{ $customer->member_level ?? 'Normal' }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Information</h3>
                            <div class="space-y-1">
                                <p><span class="font-medium">Payment Method:</span> 
                                    @switch(session('checkout.payment_method'))
                                        @case('cash')
                                            Cash
                                            @break
                                        @case('bank_transfer')
                                            Bank Transfer
                                            @break
                                        @case('check')
                                            Check
                                            @break
                                        @case('credit_card')
                                            Credit Card
                                            @break
                                        @default
                                            {{ session('checkout.payment_method') }}
                                    @endswitch
                                </p>
                                
                                @if($coupon)
                                    <p><span class="font-medium">Coupon Applied:</span> {{ $coupon->code }}</p>
                                    <p><span class="font-medium">Discount:</span> 
                                        @if($coupon->discount_type === 'percentage')
                                            {{ $coupon->discount_value }}%
                                        @else
                                            ${{ number_format($coupon->discount_value, 2) }}
                                        @endif
                                    </p>
                                @endif
                                
                                @if(session('checkout.notes'))
                                    <p><span class="font-medium">Order Notes:</span> {{ session('checkout.notes') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('checkout.processPayment') }}" method="POST">
                        @csrf
                        <div class="bg-gray-50 p-4 rounded-md mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Order Confirmation</h3>
                            <p class="text-gray-600 mb-4">
                                Please review your order details above. Once you confirm, your order will be processed and payment will be recorded.
                            </p>
                            
                            <div class="flex items-center mb-4">
                                <input id="confirm_order" name="confirm_order" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                                <label for="confirm_order" class="ml-2 block text-sm text-gray-900">
                                    I confirm that the order details are correct and I want to proceed with the payment.
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-between">
                            <a href="{{ route('checkout.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Back to Checkout
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Confirm and Pay
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 