@props(['cart', 'subtotal', 'discount' => 0, 'tax' => 0, 'total', 'itemCount'])

<div class="cart-summary bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-4 sm:p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h3>
        
        <div class="space-y-3">
            <div class="flex justify-between text-sm sm:text-base">
                <span class="text-gray-600">Subtotal ({{ $itemCount }} {{ Str::plural('item', $itemCount) }})</span>
                <span class="font-medium text-gray-800">{{ number_format($subtotal, 2) }}</span>
            </div>
            
            @if($discount > 0)
            <div class="flex justify-between text-sm sm:text-base">
                <span class="text-gray-600">Discount</span>
                <span class="font-medium text-green-600">-{{ number_format($discount, 2) }}</span>
            </div>
            @endif
            
            @if($tax > 0)
            <div class="flex justify-between text-sm sm:text-base">
                <span class="text-gray-600">Estimated Tax</span>
                <span class="font-medium text-gray-800">{{ number_format($tax, 2) }}</span>
            </div>
            @endif
            
            <div class="pt-3 mt-3 border-t border-gray-200">
                <div class="flex justify-between">
                    <span class="font-semibold text-gray-900">Total</span>
                    <span class="font-bold text-lg text-gray-900">{{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>
        
        <div class="mt-6 space-y-3">
            <a href="{{ route('checkout.index') }}" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Proceed to Checkout
            </a>
            
            <a href="{{ route('products.index') }}" class="w-full bg-gray-100 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-200 transition-colors duration-200 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Continue Shopping
            </a>
        </div>
    </div>
    
    @if($slot->isNotEmpty())
    <div class="bg-gray-50 p-4 sm:p-6 border-t border-gray-200">
        {{ $slot }}
    </div>
    @endif
</div> 