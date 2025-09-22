/**
 * Cart Management Frontend Integration
 * 
 * This file demonstrates how the frontend would work with the cart API
 * that only provides product IDs and quantities, retrieving current pricing
 * and other product details from a product service.
 */

// Sample ProductService to fetch current product details
class ProductService {
  // In a real application, this would make API calls to get product data
  async getProductDetails(productId) {
    // Simulating API delay
    await new Promise(resolve => setTimeout(resolve, 300));
    
    // Mock product data for demonstration
    const products = {
      5: {
        id: 5,
        name: "Running Shoes",
        price: 89.99,
        sale_price: 69.99,
        image: "/assets/images/products/running-shoes.jpg",
        stock: 10
      },
      7: {
        id: 7,
        name: "Athletic T-Shirt",
        price: 29.99,
        sale_price: null,
        image: "/assets/images/products/athletic-shirt.jpg",
        stock: 25
      },
      12: {
        id: 12,
        name: "Sports Watch",
        price: 199.99,
        sale_price: 159.99,
        image: "/assets/images/products/sports-watch.jpg",
        stock: 5
      }
    };
    
    return products[productId] || null;
  }
  
  async getMultipleProductDetails(productIds) {
    const uniqueIds = [...new Set(productIds)];
    const productPromises = uniqueIds.map(id => this.getProductDetails(id));
    const products = await Promise.all(productPromises);
    
    // Convert to a map for easier lookup
    const productMap = {};
    products.forEach(product => {
      if (product) {
        productMap[product.id] = product;
      }
    });
    
    return productMap;
  }
}

// Cart Service that communicates with the backend
class CartService {
  constructor() {
    this.productService = new ProductService();
    this.apiBaseUrl = '/api';
  }
  
  // Fetch cart data from the API
  async getCart() {
    try {
      const response = await fetch(`${this.apiBaseUrl}/cart`);
      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.message || 'Failed to fetch cart data');
      }
      
      // Enrich cart data with product details
      return await this.enrichCartData(data.data);
    } catch (error) {
      console.error('Error fetching cart:', error);
      throw error;
    }
  }
  
  // Add product to cart
  async addToCart(productId, quantity, options = {}) {
    try {
      const payload = {
        product_id: productId,
        quantity: quantity,
        ...options
      };
      
      const response = await fetch(`${this.apiBaseUrl}/cart/add`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });
      
      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.message || 'Failed to add item to cart');
      }
      
      return data;
    } catch (error) {
      console.error('Error adding to cart:', error);
      throw error;
    }
  }
  
  // Update cart item quantity
  async updateCartItem(itemId, quantity) {
    try {
      const payload = {
        item_id: itemId,
        quantity: quantity
      };
      
      const response = await fetch(`${this.apiBaseUrl}/cart/update`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });
      
      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.message || 'Failed to update cart item');
      }
      
      return data;
    } catch (error) {
      console.error('Error updating cart item:', error);
      throw error;
    }
  }
  
  // Enrich cart data with product details from product service
  async enrichCartData(cartData) {
    if (!cartData?.carts?.length) {
      return cartData;
    }
    
    // Extract all product IDs from all carts
    const allProductIds = [];
    cartData.carts.forEach(cart => {
      if (cart.items && cart.items.length) {
        cart.items.forEach(item => {
          allProductIds.push(item.product_id);
        });
      }
    });
    
    // Fetch all product details in a single batch
    const productDetailsMap = await this.productService.getMultipleProductDetails(allProductIds);
    
    // Enrich cart items with product details and calculate totals
    const enrichedCarts = cartData.carts.map(cart => {
      let cartTotal = 0;
      
      const enrichedItems = (cart.items || []).map(item => {
        const productDetails = productDetailsMap[item.product_id] || {};
        
        // Use current price (sale_price if available, otherwise regular price)
        const currentPrice = (productDetails.sale_price !== null && productDetails.sale_price !== undefined) 
          ? productDetails.sale_price 
          : productDetails.price || 0;
        
        // Calculate line total
        const lineTotal = currentPrice * item.quantity;
        cartTotal += lineTotal;
        
        // Return enriched item with product details
        return {
          ...item,
          product: productDetails,
          price: currentPrice,
          line_total: lineTotal
        };
      });
      
      return {
        ...cart,
        items: enrichedItems,
        total: cartTotal
      };
    });
    
    return {
      ...cartData,
      carts: enrichedCarts
    };
  }
  
  // Calculate cart totals for UI display
  calculateCartTotals(cart) {
    if (!cart || !cart.items) {
      return {
        subtotal: 0,
        tax: 0,
        shipping: 0,
        total: 0,
        item_count: 0
      };
    }
    
    const subtotal = cart.items.reduce((sum, item) => sum + (item.line_total || 0), 0);
    const itemCount = cart.items.reduce((sum, item) => sum + item.quantity, 0);
    
    // Sample tax and shipping calculations (would be more complex in a real app)
    const taxRate = 0.08; // 8% tax
    const tax = subtotal * taxRate;
    
    // Free shipping over $100, otherwise $9.99
    const shipping = subtotal > 100 ? 0 : 9.99;
    
    const total = subtotal + tax + shipping;
    
    return {
      subtotal,
      tax,
      shipping,
      total,
      item_count: itemCount
    };
  }
}

// Example usage in a React/Vue component or vanilla JS application
async function initializeCartPage() {
  try {
    const cartService = new CartService();
    
    // Show loading state
    document.getElementById('cart-loading').style.display = 'block';
    document.getElementById('cart-content').style.display = 'none';
    
    // Fetch cart data with enriched product details
    const cartData = await cartService.getCart();
    
    // Get active cart
    const activeCart = cartData.carts.find(cart => cart.id === cartData.active_cart_id) || cartData.carts[0];
    
    if (!activeCart) {
      document.getElementById('cart-empty').style.display = 'block';
      document.getElementById('cart-loading').style.display = 'none';
      return;
    }
    
    // Calculate totals for display
    const totals = cartService.calculateCartTotals(activeCart);
    
    // Update UI (this would be done differently depending on your framework)
    document.getElementById('cart-item-count').textContent = totals.item_count;
    document.getElementById('cart-subtotal').textContent = `$${totals.subtotal.toFixed(2)}`;
    document.getElementById('cart-tax').textContent = `$${totals.tax.toFixed(2)}`;
    document.getElementById('cart-shipping').textContent = totals.shipping === 0 ? 'Free' : `$${totals.shipping.toFixed(2)}`;
    document.getElementById('cart-total').textContent = `$${totals.total.toFixed(2)}`;
    
    // Render cart items (simplified example)
    const cartItemsContainer = document.getElementById('cart-items');
    cartItemsContainer.innerHTML = '';
    
    activeCart.items.forEach(item => {
      const itemElement = document.createElement('div');
      itemElement.className = 'cart-item';
      itemElement.innerHTML = `
        <div class="cart-item-image">
          <img src="${item.product?.image || '/assets/images/placeholder.jpg'}" alt="${item.product?.name || 'Product'}">
        </div>
        <div class="cart-item-details">
          <h3>${item.product?.name || 'Product'}</h3>
          ${item.size ? `<p>Size: ${item.size}</p>` : ''}
          ${item.color ? `<p>Color: ${item.color}</p>` : ''}
          <p>Price: $${item.price.toFixed(2)}</p>
          <div class="quantity-control">
            <button class="quantity-decrease" data-item-id="${item.id}">-</button>
            <input type="number" min="1" value="${item.quantity}" class="quantity-input" data-item-id="${item.id}">
            <button class="quantity-increase" data-item-id="${item.id}">+</button>
          </div>
        </div>
        <div class="cart-item-price">
          $${item.line_total.toFixed(2)}
        </div>
        <button class="remove-item" data-item-id="${item.id}">Remove</button>
      `;
      
      cartItemsContainer.appendChild(itemElement);
    });
    
    // Add event listeners for quantity controls and removal (simplified)
    document.querySelectorAll('.quantity-decrease').forEach(button => {
      button.addEventListener('click', async (e) => {
        const itemId = e.target.dataset.itemId;
        const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
        const newQuantity = Math.max(1, parseInt(input.value) - 1);
        input.value = newQuantity;
        
        await cartService.updateCartItem(itemId, newQuantity);
        // Would typically refresh the cart here
      });
    });
    
    document.querySelectorAll('.quantity-increase').forEach(button => {
      button.addEventListener('click', async (e) => {
        const itemId = e.target.dataset.itemId;
        const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
        const newQuantity = parseInt(input.value) + 1;
        input.value = newQuantity;
        
        await cartService.updateCartItem(itemId, newQuantity);
        // Would typically refresh the cart here
      });
    });
    
    // Hide loading and show content
    document.getElementById('cart-loading').style.display = 'none';
    document.getElementById('cart-content').style.display = 'block';
    
  } catch (error) {
    console.error('Failed to initialize cart page:', error);
    document.getElementById('cart-error').textContent = 'Failed to load cart. Please try again.';
    document.getElementById('cart-error').style.display = 'block';
    document.getElementById('cart-loading').style.display = 'none';
  }
}

// Initialize cart page when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeCartPage); 