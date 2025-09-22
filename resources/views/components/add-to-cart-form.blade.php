@props(['productId'])

<div class="add-to-cart-form mt-4" data-product-id="{{ $productId }}">
    <div class="flex items-center mb-4">
        <div class="flex items-center border border-gray-300 rounded-md">
            <button type="button" class="quantity-btn minus-btn px-3 py-1 bg-gray-100 rounded-l-md" data-action="decrease">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </button>
            <input type="number" class="quantity-input w-16 text-center border-0 py-1" value="1" min="1" max="99">
            <button type="button" class="quantity-btn plus-btn px-3 py-1 bg-gray-100 rounded-r-md" data-action="increase">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
        </div>
        <button type="button" class="add-to-cart-btn ml-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
            Add to Cart
        </button>
    </div>
    <div class="specifications-container">
        {{ $slot }}
    </div>
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all add to cart forms
            document.querySelectorAll('.add-to-cart-form').forEach(form => {
                initAddToCartForm(form);
            });
            
            // Function to initialize add to cart form
            function initAddToCartForm(form) {
                const productId = form.dataset.productId;
                const quantityInput = form.querySelector('.quantity-input');
                const minusBtn = form.querySelector('.minus-btn');
                const plusBtn = form.querySelector('.plus-btn');
                const addToCartBtn = form.querySelector('.add-to-cart-btn');
                
                // Handle quantity buttons
                minusBtn.addEventListener('click', function() {
                    let quantity = parseInt(quantityInput.value);
                    if (quantity > 1) {
                        quantityInput.value = quantity - 1;
                    }
                });
                
                plusBtn.addEventListener('click', function() {
                    let quantity = parseInt(quantityInput.value);
                    quantityInput.value = quantity + 1;
                });
                
                // Handle add to cart button
                addToCartBtn.addEventListener('click', function() {
                    const quantity = parseInt(quantityInput.value);
                    
                    // Collect specifications if any
                    const specifications = {};
                    form.querySelectorAll('.specification-select').forEach(select => {
                        if (select.value) {
                            specifications[select.dataset.name] = select.value;
                        }
                    });
                    
                    // Add to cart
                    addToCart(productId, quantity, specifications);
                });
            }
            
            // Function to add product to cart
            function addToCart(productId, quantity, specifications = {}) {
                fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity,
                        specifications: specifications
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count in header
                        updateCartCountDisplay(data.cart_count);
                        
                        // Show success message
                        showToast('Product added to cart', 'success');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to add product to cart', 'error');
                });
            }
            
            // Function to update cart count in header
            function updateCartCountDisplay(count) {
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = count;
                    
                    // Hide badge if count is 0
                    if (count === 0) {
                        cartCountElement.classList.add('hidden');
                    } else {
                        cartCountElement.classList.remove('hidden');
                    }
                }
            }
            
            // Function to show toast notification
            function showToast(message, type = 'success') {
                // Check if toast container exists, if not create it
                let toastContainer = document.getElementById('toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'toast-container';
                    toastContainer.className = 'fixed top-4 right-4 z-50 flex flex-col space-y-4';
                    document.body.appendChild(toastContainer);
                }
                
                // Create toast element
                const toast = document.createElement('div');
                toast.className = `px-4 py-3 rounded shadow-lg flex items-center ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
                
                // Add icon based on type
                const icon = document.createElement('span');
                if (type === 'success') {
                    icon.innerHTML = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>`;
                } else {
                    icon.innerHTML = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>`;
                }
                
                // Add message
                const messageElement = document.createElement('span');
                messageElement.textContent = message;
                
                // Append elements to toast
                toast.appendChild(icon);
                toast.appendChild(messageElement);
                
                // Add toast to container
                toastContainer.appendChild(toast);
                
                // Remove toast after 3 seconds
                setTimeout(() => {
                    toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                    setTimeout(() => {
                        toast.remove();
                        
                        // Remove container if empty
                        if (toastContainer.children.length === 0) {
                            toastContainer.remove();
                        }
                    }, 300);
                }, 3000);
            }
        });
    </script>
    @endpush
@endonce