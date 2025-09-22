<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 sm:mb-0">
                {{ __('Shopping Cart') }}
                @if(isset($customer))
                <span class="ml-2 text-sm text-gray-600">({{ $customer->name }})</span>
                @endif
            </h2>
            
            <div class="flex items-center">
                <form action="{{ route('cart.index') }}" method="GET" class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-2">
                    <select name="customer_id" id="customer_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Current Cart</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ isset($customer) && $customer->id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition ease-in-out duration-150">
                        View Cart
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200" id="cart-container" data-cart-id="{{ $cart->id }}">
                    @if(isset($customer))
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                            <div class="flex flex-col sm:flex-row sm:items-center">
                                <div class="flex-shrink-0 flex items-center justify-center sm:justify-start mb-3 sm:mb-0">
                                    <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="ml-0 sm:ml-3 text-center sm:text-left">
                                    <h3 class="text-md font-medium text-blue-800">Customer Information</h3>
                                    <div class="mt-1 text-sm text-blue-700 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                                        <p><strong>Name:</strong> {{ $customer->name }}</p>
                                        <p><strong>Contact:</strong> {{ $customer->contact_number }}</p>
                                        <p><strong>Email:</strong> {{ $customer->email }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($cartItems->count() > 0)
                        <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 sm:mb-0">
                                @if(isset($customer))
                                    {{ $customer->name }}'s Cart Items
                                @else
                                    Your Cart Items
                                @endif
                            </h3>
                            <button id="clear-cart" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Clear Cart
                            </button>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2">
                                <div class="border rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 p-3 border-b flex items-center">
                                        <input type="checkbox" id="select-all-items" class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 mr-3">
                                        <label for="select-all-items" class="text-sm font-medium text-gray-700">Select All</label>
                                    </div>
                                    <div class="cart-items divide-y divide-gray-200">
                                        @foreach($cartItems as $item)
                                            {{-- Pass item data needed for JS calculation --}}
                                            <x-cart-item :item="$item" 
                                                         :price="$item->product->selling_price ?? $item->product->price"
                                                         :item_id="$item->id" />
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <div class="lg:col-span-1">
                                <div class="sticky top-6">
                                    {{-- Updated Summary Section --}}
                                    <div id="order-summary" class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                                        <div class="space-y-3 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Subtotal (<span id="summary-item-count">0</span> items)</span>
                                                <span id="summary-subtotal" class="font-medium text-gray-900">RM 0.00</span>
                                            </div>
                                            {{-- Add Tax/Discount rows if applicable later --}}
                                            {{-- <div class="flex justify-between">
                                                <span class="text-gray-600">Tax (SST 6%)</span>
                                                <span id="summary-tax" class="font-medium text-gray-900">RM 0.00</span>
                                            </div>
                                            <div class="flex justify-between text-green-600">
                                                <span>Discount</span>
                                                <span id="summary-discount" class="font-medium">- RM 0.00</span>
                                            </div> --}}
                                            <div class="border-t pt-3 mt-3 flex justify-between items-center">
                                                <span class="text-base font-semibold text-gray-900">Total</span>
                                                <span id="summary-total" class="text-base font-semibold text-gray-900">RM 0.00</span>
                                            </div>
                                        </div>
                                        {{-- Placeholder for coupon --}}
                                        {{-- <div class="mt-4">
                                            <label for="coupon_code" class="block text-sm font-medium text-gray-700">Coupon Code</label>
                                            <div class="mt-1 flex rounded-md shadow-sm">
                                                <input type="text" name="coupon_code" id="coupon_code" class="focus:ring-blue-500 focus:border-blue-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300" placeholder="Enter coupon code">
                                                <button type="button" class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">Apply</button>
                                            </div>
                                        </div> --}}
                                        <button id="checkout-button" class="mt-6 w-full bg-blue-600 border border-transparent rounded-md py-3 px-4 flex items-center justify-center text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition ease-in-out duration-150">
                                            Proceed to Checkout
                                        </button>
                                    </div>
                                    {{-- Old Summary Component - Can be removed later --}}
                                    {{-- 
                                    <x-cart-summary
                                        :cart="$cart"
                                        :subtotal="$cart->subtotal"
                                        :tax="$cart->tax_amount"
                                        :discount="$cart->discount_amount"
                                        :total="$cart->total"
                                        :itemCount="$cartItems->count()"
                                    >
                                        @if(isset($couponForm))
                                            {{ $couponForm }}
                                        @endif
                                    </x-cart-summary> 
                                    --}}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12 empty-cart-container wave-background">
                            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">Your cart is empty</h3>
                            <p class="mt-2 text-md text-gray-500">Start adding some products to your cart.</p>
                            <div class="mt-6">
                                <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors pulse-button">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Browse Products
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 确认对话框 --}}
    <x-custom-dialog id="confirm-remove-dialog" title="Remove Item">
        Are you sure you want to remove this item from your cart?
        
        <x-slot name="icon">
            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </x-slot>
    </x-custom-dialog>
    
    <x-custom-dialog id="confirm-clear-dialog" title="Clear Cart">
        Are you sure you want to remove all items from your cart? This action cannot be undone.
        
        <x-slot name="icon">
            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </x-slot>
        
        <x-slot name="footer">
            <button type="button" class="dialog-confirm w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                Clear Cart
            </button>
            <button type="button" class="dialog-cancel mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                Cancel
            </button>
        </x-slot>
    </x-custom-dialog>

    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/cart-animations.css') }}">
    <style>
        /* Basic transition for summary updates */
        #order-summary span {
            transition: all 0.3s ease-in-out;
        }
    </style>
    @endpush
    
    @push('scripts')
    {{-- Ensure AlpineJS is loaded if using it --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script> --}}
    <script src="{{ asset('js/cart-checkout-logic.js') }}"></script> {{-- NEW JS file --}}
    <script src="{{ asset('js/cart.js') }}"></script> {{-- Keep for quantity/remove? Review needed --}}
    <script src="{{ asset('js/cart-touch.js') }}"></script> {{-- Keep for touch interactions? Review needed --}}
    @endpush
</x-app-layout> 