<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Checkout') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
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
                                            {{ number_format($cart->total(), 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <form action="{{ route('checkout.confirm') }}" method="POST" id="checkout-form">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                                
                                <div class="mb-4">
                                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        Select Customer
                                    </label>
                                    <select id="customer_id" name="customer_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('customer_id') border-red-500 @enderror" required>
                                        <option value="">Select a customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">
                                                {{ $customer->name }} ({{ $customer->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div id="customer-details" class="bg-gray-50 p-4 rounded-md hidden">
                                    <h4 class="font-medium text-sm text-gray-700 mb-2">Customer Details</h4>
                                    <div id="customer-info"></div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Payment Method
                                    </label>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex items-center">
                                            <input id="payment_method_cash" name="payment_method" type="radio" value="cash" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" checked>
                                            <label for="payment_method_cash" class="ml-3 block text-sm font-medium text-gray-700">
                                                Cash
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="payment_method_bank_transfer" name="payment_method" type="radio" value="bank_transfer" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                            <label for="payment_method_bank_transfer" class="ml-3 block text-sm font-medium text-gray-700">
                                                Bank Transfer
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="payment_method_check" name="payment_method" type="radio" value="check" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                            <label for="payment_method_check" class="ml-3 block text-sm font-medium text-gray-700">
                                                Check
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="payment_method_credit_card" name="payment_method" type="radio" value="credit_card" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                            <label for="payment_method_credit_card" class="ml-3 block text-sm font-medium text-gray-700">
                                                Credit Card
                                            </label>
                                        </div>
                                    </div>
                                    @error('payment_method')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label for="coupon_code" class="block text-sm font-medium text-gray-700 mb-1">
                                        Coupon Code (Optional)
                                    </label>
                                    <div class="flex">
                                        <input type="text" id="coupon_code" name="coupon_code" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('coupon_code') border-red-500 @enderror">
                                        <button type="button" id="apply-coupon" class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Apply
                                        </button>
                                    </div>
                                    @error('coupon_code')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    <div id="coupon-message" class="mt-2 text-sm"></div>
                                </div>
                                
                                <div id="coupon-details" class="bg-gray-50 p-4 rounded-md mb-4 hidden">
                                    <h4 class="font-medium text-sm text-gray-700 mb-2">Coupon Applied</h4>
                                    <div id="coupon-info"></div>
                                    <div class="mt-2">
                                        <div class="flex justify-between text-sm">
                                            <span>Subtotal:</span>
                                            <span id="order-subtotal">{{ number_format($cart->total(), 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm text-green-600">
                                            <span>Discount:</span>
                                            <span id="order-discount">0.00</span>
                                        </div>
                                        <div class="flex justify-between font-medium mt-1">
                                            <span>Total:</span>
                                            <span id="order-total">{{ number_format($cart->total(), 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                        Order Notes (Optional)
                                    </label>
                                    <textarea id="notes" name="notes" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('notes') border-red-500 @enderror"></textarea>
                                    @error('notes')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-between">
                            <a href="{{ route('cart.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Back to Cart
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Continue to Confirmation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script src="{{ asset('js/checkout.js') }}"></script>
    @endpush
</x-app-layout> 