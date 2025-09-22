<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;

class Checkout extends Component
{
    public $cartId;
    public $selectedItemIds = [];
    public $checkoutData = [];
    public $isLoading = true;
    public $errorMessage = null;
    public $paymentMethod;
    
    // Address Properties
    public $addresses = [];
    public $selectedShippingAddressId;
    public $selectedBillingAddressId;
    public $showAddAddressForm = false;

    // New Address Form Properties
    #[Rule('required|in:billing,shipping,both')] 
    public $newAddressType = 'both';
    #[Rule('nullable|string|max:255')] 
    public $newAddressContactPerson = '';
    #[Rule('nullable|string|max:20')] 
    public $newAddressContactPhone = '';
    #[Rule('required|string|max:255')] 
    public $newAddressLine1 = '';
    #[Rule('nullable|string|max:255')] 
    public $newAddressLine2 = '';
    #[Rule('required|string|max:100')] 
    public $newAddressCity = '';
    #[Rule('required|string|max:10')] 
    public $newAddressPostcode = '';
    #[Rule('required|string|max:100')] 
    public $newAddressState = '';
    #[Rule('required|string|max:100')] 
    public $newAddressCountry = 'Malaysia'; // Default to Malaysia
    #[Rule('boolean')] 
    public $newAddressIsDefaultBilling = false;
    #[Rule('boolean')] 
    public $newAddressIsDefaultShipping = false;

    protected $queryString = [
        'cartId' => ['except' => ''],
        'selectedItemIds' => ['as' => 'items', 'except' => []],
    ];

    public function mount(Request $request)
    {
        // Initialize from query string (Livewire handles this automatically via $queryString)
        if (!is_array($this->selectedItemIds)) {
            $this->selectedItemIds = [];
        }
        $this->loadCheckoutData();
    }

    public function loadCheckoutData()
    {
        $this->isLoading = true;
        $this->errorMessage = null;
        $this->addresses = []; // Reset addresses

        if (!$this->cartId) {
            $this->errorMessage = 'Cart ID is missing.';
            $this->isLoading = false;
            return;
        }
        if (!Auth::guard('customer')->check()) {
            $this->errorMessage = 'Please log in to proceed to checkout.';
            $this->isLoading = false;
            return;
        }

        $payload = [
            'cart_id' => (int)$this->cartId,
            'item_ids' => array_map('intval', $this->selectedItemIds)
        ];

        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                          ->withOptions(['cookies' => session()->all()]) // Send session cookies for Sanctum SPA auth
                          ->post(route('api.checkout.prepare'), $payload);

            if ($response->successful()) {
                $data = $response->json('data');
                if ($data) {
                    $this->checkoutData = $data;
                    $this->addresses = $data['addresses'] ?? []; // Populate addresses

                    // Set default selected addresses
                    $defaultShipping = collect($this->addresses)->firstWhere('is_default_shipping', true);
                    $defaultBilling = collect($this->addresses)->firstWhere('is_default_billing', true);
                    $firstAddress = collect($this->addresses)->first();
                    
                    $this->selectedShippingAddressId = $defaultShipping['id'] ?? $firstAddress['id'] ?? null;
                    $this->selectedBillingAddressId = $defaultBilling['id'] ?? $firstAddress['id'] ?? null;
                    
                    // Set default payment method
                    if (!empty($data['payment_methods'])) {
                        $this->paymentMethod = $data['payment_methods'][0]['id'] ?? null;
                    }
                } else {
                    $this->errorMessage = 'Failed to load checkout data: Invalid response format.';
                    Log::error('Checkout API prepare response missing data', ['response' => $response->body()]);
                }
            } else {
                $errorData = $response->json();
                $this->errorMessage = $errorData['message'] ?? 'Failed to load checkout data. Status: ' . $response->status();
                Log::error('Checkout API prepare failed', ['status' => $response->status(), 'response' => $errorData, 'payload' => $payload, 'customer_id' => Auth::guard('customer')->id()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception loading checkout data', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'payload' => $payload, 'customer_id' => Auth::guard('customer')->id()]);
            $this->errorMessage = 'An unexpected error occurred while loading checkout information.';
        }

        $this->isLoading = false;
    }

    public function toggleAddAddressForm()
    {
        $this->showAddAddressForm = !$this->showAddAddressForm;
        if ($this->showAddAddressForm) {
            $this->resetNewAddressFields(); // Reset fields when opening form
        }
    }

    public function resetNewAddressFields()
    {
        $this->newAddressType = 'both';
        $this->newAddressContactPerson = '';
        $this->newAddressContactPhone = '';
        $this->newAddressLine1 = '';
        $this->newAddressLine2 = '';
        $this->newAddressCity = '';
        $this->newAddressPostcode = '';
        $this->newAddressState = '';
        $this->newAddressCountry = 'Malaysia';
        $this->newAddressIsDefaultBilling = false;
        $this->newAddressIsDefaultShipping = false;
        $this->resetValidation(); // Reset validation errors
    }

    public function saveNewAddress()
    {
        $this->validate(); // Validate new address fields based on Rules

        $this->isLoading = true; // Show loading indicator

        $payload = [
            'type' => $this->newAddressType,
            'contact_person' => $this->newAddressContactPerson,
            'contact_phone' => $this->newAddressContactPhone,
            'line1' => $this->newAddressLine1,
            'line2' => $this->newAddressLine2,
            'city' => $this->newAddressCity,
            'postcode' => $this->newAddressPostcode,
            'state' => $this->newAddressState,
            'country' => $this->newAddressCountry,
            'is_default_billing' => $this->newAddressIsDefaultBilling,
            'is_default_shipping' => $this->newAddressIsDefaultShipping,
        ];

        try {
             $response = Http::withHeaders(['Accept' => 'application/json'])
                           ->withOptions(['cookies' => session()->all()])
                           ->post(route('api.customer.addresses.store'), $payload);
            
            if ($response->successful()) {
                 $newAddress = $response->json('data');
                 // Refresh addresses and potentially select the new one
                 $this->loadCheckoutData(); // Reload all data to get updated addresses
                 $this->showAddAddressForm = false; // Close form
                 $this->dispatch('show-toast', type: 'success', message: 'Address added successfully!');
                 
                 // Optionally select the new address
                 // $this->selectedShippingAddressId = $newAddress['id'];
                 // $this->selectedBillingAddressId = $newAddress['id'];
                 
            } else {
                $errorData = $response->json();
                $msg = $errorData['message'] ?? 'Failed to save address.';
                // Optionally display validation errors from API
                if (isset($errorData['errors'])) {
                    foreach ($errorData['errors'] as $field => $messages) {
                        $this->addError('newAddress' . ucfirst($field), $messages[0]);
                    }
                }
                $this->dispatch('show-toast', type: 'error', message: $msg);
                Log::error('Save address API failed', ['status' => $response->status(), 'response' => $errorData, 'payload' => $payload]);
            }

        } catch (\Exception $e) {
            Log::error('Exception saving address', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'payload' => $payload]);
            $this->dispatch('show-toast', type: 'error', message: 'An unexpected error occurred while saving the address.');
        }
        
        $this->isLoading = false;
    }
    
    public function placeOrder()
    {
        $this->isLoading = true;
        $this->errorMessage = null;
        
        // Add address validation
        if (empty($this->selectedShippingAddressId) || empty($this->selectedBillingAddressId)) {
             $this->errorMessage = 'Please select both shipping and billing addresses.';
             $this->isLoading = false;
             $this->dispatch('show-toast', type: 'error', message: $this->errorMessage);
             return;
         }

        if (empty($this->paymentMethod)) {
            $this->errorMessage = 'Please select a payment method.';
            $this->isLoading = false;
            $this->dispatch('show-toast', type: 'error', message: $this->errorMessage);
            return;
        }
        if (!Auth::guard('customer')->check()) {
             $this->errorMessage = 'Authentication lost. Please log in again.';
             $this->isLoading = false;
             $this->dispatch('show-toast', type: 'error', message: $this->errorMessage);
             return;
        }

        $payload = [
            'cart_id' => (int)$this->cartId,
            'item_ids' => array_map('intval', $this->selectedItemIds),
            'payment_method' => $this->paymentMethod,
            'shipping_address_id' => $this->selectedShippingAddressId, // Add selected address IDs
            'billing_address_id' => $this->selectedBillingAddressId,
        ];

        try {
             $response = Http::withHeaders(['Accept' => 'application/json'])
                           ->withOptions(['cookies' => session()->all()]) 
                           ->post(route('api.checkout.process'), $payload);

            if ($response->successful()) {
                $orderId = $response->json('order_id');
                $this->dispatch('show-toast', type: 'success', message: 'Order placed successfully!');
                 Log::info('Order placed', ['order_id' => $orderId]);
                session()->flash('order_success', 'Your order (ID: '.$orderId.') has been placed successfully!');
                 return redirect()->route('checkout.success');
            } else {
                $errorData = $response->json();
                $this->errorMessage = $errorData['message'] ?? 'Failed to place order. Status: ' . $response->status();
                Log::error('Checkout API process failed', ['status' => $response->status(), 'response' => $errorData, 'payload' => $payload, 'customer_id' => Auth::guard('customer')->id()]);
                $this->dispatch('show-toast', type: 'error', message: $this->errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('Exception processing checkout', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'payload' => $payload, 'customer_id' => Auth::guard('customer')->id()]);
            $this->errorMessage = 'An unexpected error occurred while placing your order.';
            $this->dispatch('show-toast', type: 'error', message: $this->errorMessage);
        }
        $this->isLoading = false;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.checkout'); 
    }
} 