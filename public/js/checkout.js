document.addEventListener('DOMContentLoaded', function() {
    // Customer selection handling
    const customerSelect = document.getElementById('customer_id');
    const customerDetails = document.getElementById('customer-details');
    
    if (customerSelect && customerDetails) {
        customerSelect.addEventListener('change', function() {
            const customerId = this.value;
            if (customerId) {
                fetch(`/customers/${customerId}/details`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update customer details
                            const customer = data.customer;
                            let detailsHtml = `
                                <div class="mt-4 bg-gray-50 p-4 rounded-md">
                                    <h4 class="text-md font-medium text-gray-900 mb-2">Selected Customer Details</h4>
                                    <div class="space-y-1">
                                        <p><span class="font-medium">Name:</span> ${customer.name}</p>
                                        <p><span class="font-medium">Phone:</span> ${customer.phone || 'N/A'}</p>
                                        <p><span class="font-medium">Email:</span> ${customer.email || 'N/A'}</p>
                                        <p><span class="font-medium">Address:</span> ${customer.address || 'N/A'}</p>
                                        <p><span class="font-medium">Member Level:</span> ${customer.member_level || 'Normal'}</p>
                                    </div>
                                </div>
                            `;
                            customerDetails.innerHTML = detailsHtml;
                            customerDetails.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Failed to load customer details', 'error');
                    });
            } else {
                customerDetails.innerHTML = '';
                customerDetails.classList.add('hidden');
            }
        });
    }
    
    // Coupon application handling
    const couponForm = document.getElementById('coupon-form');
    const couponCode = document.getElementById('coupon_code');
    const couponDetails = document.getElementById('coupon-details');
    const orderTotals = document.getElementById('order-totals');
    
    if (couponForm && couponCode) {
        couponForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const code = couponCode.value.trim();
            if (!code) {
                showToast('Please enter a coupon code', 'error');
                return;
            }
            
            fetch(couponForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ coupon_code: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show coupon details
                    if (couponDetails) {
                        let detailsHtml = `
                            <div class="mt-4 bg-green-50 p-4 rounded-md">
                                <h4 class="text-md font-medium text-green-800 mb-2">Coupon Applied</h4>
                                <div class="space-y-1 text-green-700">
                                    <p><span class="font-medium">Code:</span> ${data.coupon.code}</p>
                                    <p><span class="font-medium">Discount:</span> `;
                                    
                        if (data.coupon.discount_type === 'percentage') {
                            detailsHtml += `${data.coupon.discount_value}%`;
                        } else {
                            detailsHtml += `$${parseFloat(data.coupon.discount_value).toFixed(2)}`;
                        }
                        
                        detailsHtml += `</p>
                                </div>
                            </div>
                        `;
                        couponDetails.innerHTML = detailsHtml;
                        couponDetails.classList.remove('hidden');
                    }
                    
                    // Update order totals
                    if (orderTotals) {
                        let totalsHtml = `
                            <div class="mt-4 space-y-2">
                                <div class="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>$${parseFloat(data.subtotal).toFixed(2)}</span>
                                </div>
                                <div class="flex justify-between text-green-600">
                                    <span>Discount:</span>
                                    <span>-$${parseFloat(data.discount).toFixed(2)}</span>
                                </div>
                                <div class="flex justify-between font-bold text-lg">
                                    <span>Total:</span>
                                    <span>$${parseFloat(data.total).toFixed(2)}</span>
                                </div>
                            </div>
                        `;
                        orderTotals.innerHTML = totalsHtml;
                    }
                    
                    showToast('Coupon applied successfully', 'success');
                } else {
                    showToast(data.message || 'Failed to apply coupon', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to apply coupon', 'error');
            });
        });
    }
    
    // Form validation
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const customerId = document.getElementById('customer_id').value;
            if (!customerId) {
                e.preventDefault();
                showToast('Please select a customer before proceeding', 'error');
                return false;
            }
            
            // Ensure a payment method is selected
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) {
                e.preventDefault();
                showToast('Please select a payment method', 'error');
                return false;
            }
            
            return true;
        });
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