<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Get or create a cart for the current user/session or a specific customer
     */
    protected function getCart($customer_id = null)
    {
        // Get default store
        $store = Warehouse::where('is_store', true)->where('is_default', true)->first();
        if (!$store) {
            $store = Warehouse::where('is_store', true)->first();
        }
        
        if (!$store) {
            abort(500, 'No store found in the system');
        }

        // If customer_id is provided, get or create cart for that customer
        if ($customer_id) {
            $cart = Cart::firstOrCreate(
                ['customer_id' => $customer_id, 'store_id' => $store->id],
                []
            );
            return $cart;
        }

        if (Auth::check()) {
            // User is logged in, get their cart or create one
            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['store_id' => $store->id]
            );
        } else {
            // User is not logged in, use session ID
            $sessionId = Session::getId();
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId],
                ['store_id' => $store->id]
            );
        }

        return $cart;
    }

    /**
     * Display the cart page
     */
    public function index(Request $request)
    {
        // Get customers for the dropdown
        $customers = \App\Models\Customer::orderBy('name')->get();
        
        // Check if a specific customer's cart was requested
        $customer_id = $request->input('customer_id');
        
        if ($customer_id) {
            $cart = $this->getCart($customer_id);
            $customer = \App\Models\Customer::findOrFail($customer_id);
            $cartItems = $cart->items()->with('product')->get();
            
            return view('cart.index', compact('cart', 'cartItems', 'customers', 'customer'));
        }
        
        // Default to current user/session cart
        $cart = $this->getCart();
        $cartItems = $cart->items()->with('product')->get();
        
        return view('cart.index', compact('cart', 'cartItems', 'customers'));
    }

    /**
     * Add a product to the cart
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'specifications' => 'nullable|array',
        ]);

        $cart = $this->getCart();
        $product = Product::findOrFail($request->product_id);
        
        // Check if this product is already in the cart
        $cartItem = $cart->items()
            ->where('product_id', $product->id)
            ->first();
            
        if ($cartItem) {
            // Update quantity if product already exists in cart
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            // Add new item to cart
            $cartItem = new CartItem([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'specifications' => $request->specifications,
            ]);
            
            $cart->items()->save($cartItem);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart_count' => $cart->items()->sum('quantity')
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart();
        $cartItem = $cart->items()->findOrFail($id);
        
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        // 从产品中获取当前价格
        $price = $cartItem->product ? $cartItem->product->selling_price : 0;

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
            'cart_count' => $cart->items()->sum('quantity'),
            'item_total' => $cartItem->quantity * $price,
            'cart_total' => $cart->total()
        ]);
    }

    /**
     * Remove an item from the cart
     */
    public function removeCartItem($id)
    {
        $cart = $this->getCart();
        $cartItem = $cart->items()->findOrFail($id);
        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => $cart->items()->sum('quantity'),
            'cart_total' => $cart->total()
        ]);
    }

    /**
     * Get the current cart count
     */
    public function getCartCount()
    {
        $cart = $this->getCart();
        $count = $cart->items()->sum('quantity');
        
        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * Clear the entire cart
     */
    public function clearCart()
    {
        $cart = $this->getCart();
        $cart->items()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared'
        ]);
    }

    /**
     * Get a specific customer's cart
     */
    public function getCustomerCart($customer_id)
    {
        // Validate customer exists
        $customer = \App\Models\Customer::findOrFail($customer_id);
        
        // Get the cart
        $cart = $this->getCart($customer_id);
        
        // Get cart items with product details
        $cartItems = $cart->items()->with('product')->get();
        
        // Format for JSON response
        $formattedItems = $cartItems->map(function($item) {
            // 获取产品当前价格
            $price = $item->product ? $item->product->selling_price : 0;
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $price,
                'specifications' => $item->specifications,
                'subtotal' => $price * $item->quantity
            ];
        });
        
        // If it's an AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name
                ],
                'items' => $formattedItems,
                'total' => $formattedItems->sum('subtotal')
            ]);
        }
        
        // Otherwise return the view
        $customers = \App\Models\Customer::orderBy('name')->get();
        return view('cart.index', [
            'cart' => $cart,
            'cartItems' => $cartItems,
            'customers' => $customers,
            'customer' => $customer
        ]);
    }
} 