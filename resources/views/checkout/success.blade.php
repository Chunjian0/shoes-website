<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order Confirmation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-12 bg-white border-b border-gray-200 text-center">
                    
                    {{-- Success Icon/Animation Placeholder --}}
                    <div class="mb-6">
                        {{-- You can replace this SVG with a Lottie animation or a more elaborate GSAP animation --}}
                        <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>

                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">Order Placed Successfully!</h3>

                    @if(session('order_success'))
                        <p class="text-gray-600 mb-8">{{ session('order_success') }}</p>
                    @else
                        <p class="text-gray-600 mb-8">Thank you for your purchase.</p>
                    @endif

                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        {{-- Link to Order History (Assuming you have an order history route) --}}
                        {{-- 
                        <a href="{{ route('orders.history') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            View Order
                        </a> 
                        --}}
                        
                        {{-- Link back to Products/Homepage --}}
                        <a href="{{ route('products.index') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            Continue Shopping
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Example GSAP animation for the success checkmark
                gsap.fromTo('.w-16.h-16.mx-auto.text-green-500', 
                    { scale: 0.5, opacity: 0, rotation: -180 }, 
                    { scale: 1, opacity: 1, rotation: 0, duration: 0.8, ease: "back.out(1.7)", delay: 0.2 }
                );
            });
        </script>
    @endpush
</x-app-layout> 