document.addEventListener('DOMContentLoaded', () => {
    const cartContainer = document.getElementById('cart-container');
    if (!cartContainer) return;

    const cartId = cartContainer.dataset.cartId;
    const itemCheckboxes = document.querySelectorAll('.item-checkbox'); // Assumes checkbox inside x-cart-item has this class
    const selectAllCheckbox = document.getElementById('select-all-items');
    const summaryItemCount = document.getElementById('summary-item-count');
    const summarySubtotal = document.getElementById('summary-subtotal');
    const summaryTotal = document.getElementById('summary-total');
    const checkoutButton = document.getElementById('checkout-button');

    let selectedItems = []; // Array to hold { id: itemId, price: itemPrice, quantity: itemQuantity }

    function updateSummary() {
        let count = 0;
        let subtotal = 0;

        selectedItems.forEach(item => {
            count += item.quantity;
            subtotal += item.price * item.quantity;
        });

        // Format currency (simple example, consider using Intl.NumberFormat for better localization)
        const formatCurrency = (amount) => `RM ${amount.toFixed(2)}`;

        summaryItemCount.textContent = selectedItems.length; // Show number of unique selected items
        summarySubtotal.textContent = formatCurrency(subtotal);
        // TODO: Add tax/discount calculation here if needed
        summaryTotal.textContent = formatCurrency(subtotal);

        // Enable/disable checkout button based on selection
        // checkoutButton.disabled = selectedItems.length === 0;
        // Always enable checkout button, if nothing selected, checkout all
        checkoutButton.disabled = false; 

        // Update Select All checkbox state
        if (selectAllCheckbox) {
             if (itemCheckboxes.length > 0 && selectedItems.length === itemCheckboxes.length) {
                 selectAllCheckbox.checked = true;
                 selectAllCheckbox.indeterminate = false;
             } else if (selectedItems.length > 0) {
                 selectAllCheckbox.checked = false;
                 selectAllCheckbox.indeterminate = true;
             } else {
                 selectAllCheckbox.checked = false;
                 selectAllCheckbox.indeterminate = false;
             }
         }
    }

    function handleItemSelectionChange(checkbox) {
        const itemElement = checkbox.closest('[data-item-id]'); // Assumes x-cart-item's root has data-item-id
        if (!itemElement) return;

        const itemId = parseInt(itemElement.dataset.itemId);
        const itemPrice = parseFloat(itemElement.dataset.price);
        // Find quantity input within the item element (adjust selector as needed)
        const quantityInput = itemElement.querySelector('.item-quantity-input'); 
        const itemQuantity = quantityInput ? parseInt(quantityInput.value) : 1;

        if (checkbox.checked) {
            // Add item if not already selected
            if (!selectedItems.some(item => item.id === itemId)) {
                selectedItems.push({ id: itemId, price: itemPrice, quantity: itemQuantity });
            }
        } else {
            // Remove item
            selectedItems = selectedItems.filter(item => item.id !== itemId);
        }
        updateSummary();
    }

    // --- Event Listeners ---

    // Individual item checkboxes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => handleItemSelectionChange(checkbox));
        // Initial check on page load (if needed, e.g., if state is preserved)
        // handleItemSelectionChange(checkbox); 
    });

    // Select All checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', () => {
            const isChecked = selectAllCheckbox.checked;
            itemCheckboxes.forEach(checkbox => {
                if (checkbox.checked !== isChecked) {
                    checkbox.checked = isChecked;
                    handleItemSelectionChange(checkbox); // Update selection state for each item
                }
            });
            // Ensure summary updates after all changes are processed
            // updateSummary(); // updateSummary is called within handleItemSelectionChange
        });
    }

    // Checkout button
    if (checkoutButton) {
        checkoutButton.addEventListener('click', () => {
            let url = `/checkout?cart_id=${cartId}`;
            if (selectedItems.length > 0) {
                const itemParams = selectedItems.map(item => `items[]=${item.id}`);
                url += '&' + itemParams.join('&');
            } else {
                // No items selected, proceed with all items (don't add specific item IDs)
                // The backend prepare method should handle this case
            }
            
            // Add loading indicator/disable button here
            checkoutButton.disabled = true;
            checkoutButton.innerHTML = 'Processing...'; // Example loading state

            window.location.href = url;
        });
    }
    
    // Handle quantity changes affecting the summary (if quantity changes outside of checkbox click)
    // This requires the quantity update logic in cart.js to potentially trigger a re-calculation
    // Or we can re-query the quantity when a checkbox state changes.
    // Let's stick to re-querying quantity on checkbox change for simplicity first.
    // We need to ensure quantity changes update the data-quantity or input value reliably.

    // Initial summary calculation on page load
    updateSummary(); 

    // Optional: Listen for custom events if cart.js updates quantities via AJAX
    // document.addEventListener('cart-item-updated', (event) => {
    //     const updatedItemId = event.detail.itemId;
    //     const newQuantity = event.detail.quantity;
    //     const itemIndex = selectedItems.findIndex(item => item.id === updatedItemId);
    //     if (itemIndex > -1) {
    //         selectedItems[itemIndex].quantity = newQuantity;
    //         updateSummary();
    //     }
    // });
}); 