<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">

                <h2 class="text-2xl font-semibold mb-6 text-gray-800">Checkout</h2>

                {{-- Loading State --}}
                <div wire:loading wire:target="loadCheckoutData, placeOrder, saveNewAddress" class="fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50">
                    <div class="flex items-center space-x-2">
                        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-lg font-medium text-gray-700">Processing...</span>
                    </div>
                </div>

                {{-- Error Message --}}
                @if($errorMessage)
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                        <p>{{ $errorMessage }}</p>
                    </div>
                @endif

                {{-- Checkout Content --}}
                @if(!$isLoading && !$errorMessage && !empty($checkoutData))
                    <form wire:submit.prevent="placeOrder">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            {{-- Left Column: Shipping, Billing, Payment --}}
                            <div>
                                {{-- Shipping Address --}}
                                <div class="mb-6">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-xl font-semibold text-gray-700">Shipping Address</h3>
                                        <button type="button" wire:click="toggleAddAddressForm" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                            {{ $showAddAddressForm ? 'Cancel' : '+ Add New Address' }}
                                        </button>
                                    </div>

                                    {{-- Add New Address Form --}}
                                    @if($showAddAddressForm)
                                        <div class="p-4 border border-blue-200 rounded-lg bg-blue-50 mb-4 space-y-3 transition-all duration-300 ease-in-out" id="add-address-form">
                                            <h4 class="text-md font-medium text-gray-700">Add New Address</h4>
                                            {{-- Form fields bound to Livewire properties --}}
                                            <div>
                                                <label for="newAddressLine1" class="block text-sm font-medium text-gray-700">Address Line 1</label>
                                                <input type="text" wire:model.lazy="newAddressLine1" id="newAddressLine1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                @error('newAddressLine1') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                            </div>
                                             <div>
                                                <label for="newAddressLine2" class="block text-sm font-medium text-gray-700">Address Line 2 (Optional)</label>
                                                <input type="text" wire:model.lazy="newAddressLine2" id="newAddressLine2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label for="newAddressCity" class="block text-sm font-medium text-gray-700">City</label>
                                                    <input type="text" wire:model.lazy="newAddressCity" id="newAddressCity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                    @error('newAddressCity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                </div>
                                                 <div>
                                                    <label for="newAddressPostcode" class="block text-sm font-medium text-gray-700">Postcode</label>
                                                    <input type="text" wire:model.lazy="newAddressPostcode" id="newAddressPostcode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                    @error('newAddressPostcode') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label for="newAddressState" class="block text-sm font-medium text-gray-700">State</label>
                                                    <input type="text" wire:model.lazy="newAddressState" id="newAddressState" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                    @error('newAddressState') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label for="newAddressCountry" class="block text-sm font-medium text-gray-700">Country</label>
                                                    <input type="text" wire:model.lazy="newAddressCountry" id="newAddressCountry" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                     @error('newAddressCountry') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                 <div>
                                                    <label for="newAddressContactPerson" class="block text-sm font-medium text-gray-700">Contact Person (Optional)</label>
                                                    <input type="text" wire:model.lazy="newAddressContactPerson" id="newAddressContactPerson" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label for="newAddressContactPhone" class="block text-sm font-medium text-gray-700">Contact Phone (Optional)</label>
                                                    <input type="text" wire:model.lazy="newAddressContactPhone" id="newAddressContactPhone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </div>
                                            </div>
                                             <div>
                                                <label for="newAddressType" class="block text-sm font-medium text-gray-700">Address Type</label>
                                                <select wire:model="newAddressType" id="newAddressType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                    <option value="both">Billing & Shipping</option>
                                                    <option value="billing">Billing Only</option>
                                                    <option value="shipping">Shipping Only</option>
                                                </select>
                                                 @error('newAddressType') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model="newAddressIsDefaultBilling" class="form-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-offset-0 focus:ring-blue-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm text-gray-600">Set as default billing</span>
                                                </label>
                                                 <label class="flex items-center">
                                                    <input type="checkbox" wire:model="newAddressIsDefaultShipping" class="form-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-offset-0 focus:ring-blue-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm text-gray-600">Set as default shipping</span>
                                                </label>
                                            </div>
                                            <div class="flex justify-end">
                                                <button type="button" wire:click="saveNewAddress" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    Save Address
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Address Selection List --}}
                                    <div class="space-y-2">
                                        @forelse($addresses as $address)
                                            @if($address['type'] === 'shipping' || $address['type'] === 'both')
                                                <label class="flex items-start p-3 border rounded-md hover:border-blue-500 transition-colors cursor-pointer {{ $selectedShippingAddressId == $address['id'] ? 'bg-blue-50 border-blue-500' : 'border-gray-300' }}">
                                                    <input type="radio" wire:model="selectedShippingAddressId" value="{{ $address['id'] }}" name="shipping_address" class="form-radio h-4 w-4 text-blue-600 focus:ring-blue-500 mt-1">
                                                    <div class="ml-3 text-sm">
                                                        <p class="font-medium text-gray-800">
                                                            {{ $address['contact_person'] ?? $checkoutData['customer']['name'] }}
                                                            @if($address['is_default_shipping'])
                                                                <span class="ml-1 text-xs text-blue-600 font-semibold">(Default)</span>
                                                            @endif
                                                        </p>
                                                        <p class="text-gray-600">{{ $address['formatted'] }}</p>
                                                        @if($address['contact_phone'])
                                                        <p class="text-gray-500 text-xs mt-1">Phone: {{ $address['contact_phone'] }}</p>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endif
                                        @empty
                                            <p class="text-sm text-gray-500 italic">No shipping addresses found. Please add one.</p>
                                        @endforelse
                                    </div>
                                    @error('selectedShippingAddressId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Billing Address --}}
                                <div class="mb-6">
                                    <h3 class="text-xl font-semibold mb-2 text-gray-700">Billing Address</h3>
                                    <div class="space-y-2">
                                         @forelse($addresses as $address)
                                            @if($address['type'] === 'billing' || $address['type'] === 'both')
                                                <label class="flex items-start p-3 border rounded-md hover:border-blue-500 transition-colors cursor-pointer {{ $selectedBillingAddressId == $address['id'] ? 'bg-blue-50 border-blue-500' : 'border-gray-300' }}">
                                                    <input type="radio" wire:model="selectedBillingAddressId" value="{{ $address['id'] }}" name="billing_address" class="form-radio h-4 w-4 text-blue-600 focus:ring-blue-500 mt-1">
                                                    <div class="ml-3 text-sm">
                                                         <p class="font-medium text-gray-800">
                                                            {{ $address['contact_person'] ?? $checkoutData['customer']['name'] }}
                                                             @if($address['is_default_billing'])
                                                                <span class="ml-1 text-xs text-blue-600 font-semibold">(Default)</span>
                                                            @endif
                                                        </p>
                                                        <p class="text-gray-600">{{ $address['formatted'] }}</p>
                                                    </div>
                                                </label>
                                            @endif
                                        @empty
                                             <p class="text-sm text-gray-500 italic">No billing addresses found. Please add one.</p>
                                        @endforelse
                                    </div>
                                     @error('selectedBillingAddressId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Payment Method Selection --}}
                                <div class="mb-6">
                                    <label class="block text-lg font-medium text-gray-700 mb-2">Payment Method</label>
                                    <div class="space-y-2">
                                        @if(!empty($checkoutData['payment_methods']))
                                            @foreach($checkoutData['payment_methods'] as $method)
                                                <label class="flex items-center p-3 border rounded-md hover:border-blue-500 transition-colors cursor-pointer {{ $paymentMethod == $method['id'] ? 'bg-blue-50 border-blue-500' : 'border-gray-300' }}">
                                                    <input type="radio" wire:model="paymentMethod" value="{{ $method['id'] }}" name="payment_method" class="form-radio h-4 w-4 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-3 text-sm font-medium text-gray-700">{{ $method['name'] }}</span>
                                                </label>
                                                {{-- Conditional UI for Bank Transfer --}}
                                                @if($method['id'] === 'bank_transfer' && $paymentMethod === 'bank_transfer')
                                                <div class="pl-7 pb-2 text-xs text-gray-600 bg-blue-50 transition-all duration-300 ease-in-out" id="bank-details">
                                                    <p class="font-medium">Bank Details:</p>
                                                    <p>Account Name: Optic System Sdn Bhd</p>
                                                    <p>Account Number: 123-456-7890</p>
                                                    <p>Bank: XYZ Bank</p>
                                                    <p>Please use your Order ID as the payment reference.</p>
                                                </div>
                                                @endif
                                            @endforeach
                                        @else
                                            <p class="text-sm text-gray-500">No payment methods available.</p>
                                        @endif
                                    </div>
                                    @error('paymentMethod') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                 {{-- Place Order Button (Moved Here for better layout on smaller screens) --}}
                                <button type="submit" 
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-75 cursor-not-allowed"
                                        class="w-full bg-blue-600 border border-transparent rounded-md py-3 px-4 flex items-center justify-center text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                                    <span wire:loading wire:target="placeOrder" class="mr-2">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="placeOrder">Place Order</span>
                                    <span wire:loading wire:target="placeOrder">Processing...</span>
                                </button>
                            </div> 
                            
                             {{-- Right Column: Order Summary --}}
                            <div>
                                <h3 class="text-xl font-semibold mb-4 text-gray-700">Order Summary</h3>
                                <div class="space-y-3 border border-gray-200 rounded-lg p-4 bg-gray-50 sticky top-6">
                                    @foreach($checkoutData['items'] as $item)
                                        <div class="flex items-center justify-between py-2 border-b last:border-b-0">
                                            <div class="flex items-center">
                                                @if(!empty($item['images']))
                                                <img src="{{ $item['images'][0] ?? asset('images/placeholder.png') }}" alt="{{ $item['name'] }}" class="w-12 h-12 object-cover rounded mr-3">
                                                @endif
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ $item['name'] }}</p>
                                                    <p class="text-sm text-gray-500">Qty: {{ $item['quantity'] }}</p>
                                                </div>
                                            </div>
                                            <p class="font-medium text-gray-800">RM {{ number_format($item['subtotal'], 2) }}</p>
                                        </div>
                                    @endforeach
                                    
                                    {{-- Totals --}}
                                    <div class="pt-4 border-t mt-4 space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Subtotal</span>
                                            <span class="font-medium text-gray-900">RM {{ number_format($checkoutData['subtotal'], 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-base font-semibold text-gray-900 pt-2 border-t">
                                            <span>Total</span>
                                            <span>RM {{ number_format($checkoutData['total'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                           
                        </div>
                    </form>
                @elseif($isLoading)
                    {{-- Optional: Add a simpler loading indicator here if the full-screen one is too intrusive --}}
                    <div class="text-center py-10">
                        <p class="text-gray-600">Loading checkout details...</p>
                    </div>
                @endif
                
            </div>
        </div>
    </div>
    
    @push('scripts')
        {{-- Include GSAP --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
        <script>
            document.addEventListener('livewire:load', function () {
                // Example: Animate the add address form
                let addAddressForm = document.getElementById('add-address-form');
                if (addAddressForm) {
                    // Initial state (if needed)
                    //gsap.set(addAddressForm, { height: 0, opacity: 0 });
                    
                    Livewire.hook('element.updated', (el, component) => {
                        // Check if the form was just shown
                         if (el.id === 'add-address-form' && component.showAddAddressForm) {
                            gsap.fromTo(el, { height: 0, opacity: 0 }, { height: 'auto', opacity: 1, duration: 0.4, ease: 'power2.inOut' });
                         } 
                         // Add animation for hiding if needed
                    });
                }
                
                 // Example: Animate bank details section
                let bankDetails = document.getElementById('bank-details');
                 if (bankDetails) {
                    Livewire.hook('element.updated', (el, component) => {
                        if (el.id === 'bank-details') {
                           gsap.fromTo(el, { opacity: 0, y: -10 }, { opacity: 1, y: 0, duration: 0.3, ease: 'power1.out' });
                        }
                    });
                 }
            });
        </script>
    @endpush
</div> 