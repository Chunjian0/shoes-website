<?php
/**
 * Cart API Endpoints
 * 
 * This file defines the API endpoints for cart operations.
 * The implementation ensures that only necessary data (product_id and quantity)
 * are stored in the cart, while pricing and other product details are retrieved
 * from the product service when needed.
 */

// Example implementation with Laravel routes
// In a real project, this would be in a routes/api.php file

/*
|--------------------------------------------------------------------------
| Cart API Routes
|--------------------------------------------------------------------------
|
| These routes define the cart API endpoints. The implementation ensures
| that only product IDs and quantities are stored, with other details
| fetched from the product service when displaying the cart.
|
*/

/*
Route::prefix('api')->middleware(['auth:sanctum'])->group(function () {
    // Get all carts for the authenticated user
    Route::get('/cart', 'CartController@index');
    
    // Get a specific cart by ID
    Route::get('/cart/{id}', 'CartController@show');
    
    // Add an item to a cart
    Route::post('/cart/add', 'CartController@addItem');
    
    // Update a cart item
    Route::put('/cart/update', 'CartController@updateItem');
    
    // Remove an item from a cart
    Route::delete('/cart/remove/{id}', 'CartController@removeItem');
    
    // Clear a cart
    Route::delete('/cart/clear/{id}', 'CartController@clearCart');
    
    // Create a new cart
    Route::post('/cart/create', 'CartController@create');
    
    // Update cart details (name, type, etc.)
    Route::put('/cart/{id}', 'CartController@update');
    
    // Delete a cart
    Route::delete('/cart/{id}', 'CartController@destroy');
    
    // Set a cart as active
    Route::post('/cart/{id}/set-active', 'CartController@setActive');
    
    // Move items between carts
    Route::post('/cart/move-items', 'CartController@moveItems');
});
*/

// Example CartController methods
// In a real project, this would be in app/Http/Controllers/CartController.php

/**
 * Example CartController class
 */
class CartController
{
    /**
     * Get all carts for the authenticated user.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Get all carts for the authenticated user
        $user = auth()->user();
        $carts = Cart::where('user_id', $user->id)->get();
        
        // For each cart, get the items
        $cartsWithItems = $carts->map(function ($cart) {
            // Get the items for this cart
            $items = CartItem::where('cart_id', $cart->id)->get();
            
            // Return the cart with items
            return [
                'id' => $cart->id,
                'name' => $cart->name,
                'type' => $cart->type,
                'is_default' => $cart->is_default,
                'items' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id, // Only store product_id
                        'quantity' => $item->quantity,
                        'size' => $item->size,
                        'color' => $item->color
                    ];
                }),
                'item_count' => $items->count(),
                'total' => 0 // Front-end will calculate based on current prices
            ];
        });
        
        // Get the active cart ID
        $activeCartId = $user->active_cart_id;
        
        return response()->json([
            'success' => true,
            'message' => 'Carts retrieved successfully',
            'data' => [
                'carts' => $cartsWithItems,
                'active_cart_id' => $activeCartId
            ]
        ]);
    }
    
    /**
     * Add an item to a cart.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(Request $request)
    {
        // Validate the request
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'cart_id' => 'nullable|exists:carts,id',
            'size' => 'nullable|string',
            'color' => 'nullable|string'
        ]);
        
        $user = auth()->user();
        
        // Get the cart ID (use active cart if not specified)
        $cartId = $request->cart_id ?? $user->active_cart_id;
        
        // If no cart ID is available, use the user's default cart or create one
        if (!$cartId) {
            $cart = Cart::where('user_id', $user->id)
                ->where('is_default', true)
                ->first();
            
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $user->id,
                    'name' => 'Shopping Cart',
                    'type' => 'default',
                    'is_default' => true
                ]);
                
                // Set as active cart
                $user->active_cart_id = $cart->id;
                $user->save();
            }
            
            $cartId = $cart->id;
        }
        
        // Check if the product is already in the cart
        $cartItem = CartItem::where('cart_id', $cartId)
            ->where('product_id', $request->product_id)
            ->where('size', $request->size)
            ->where('color', $request->color)
            ->first();
        
        if ($cartItem) {
            // Update the quantity
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            // Create a new cart item
            $cartItem = CartItem::create([
                'cart_id' => $cartId,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'size' => $request->size,
                'color' => $request->color
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'data' => [
                'cart_id' => $cartId,
                'item' => [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'size' => $cartItem->size,
                    'color' => $cartItem->color
                ]
            ]
        ]);
    }
    
    /**
     * Update a cart item.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Request $request)
    {
        // Validate the request
        $request->validate([
            'item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $user = auth()->user();
        
        // Get the cart item
        $cartItem = CartItem::findOrFail($request->item_id);
        
        // Ensure the user owns the cart item
        $cart = Cart::findOrFail($cartItem->cart_id);
        if ($cart->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this cart item'
            ], 403);
        }
        
        // Update the quantity
        $cartItem->quantity = $request->quantity;
        $cartItem->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => [
                'item_id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity
            ]
        ]);
    }
    
    /**
     * Remove an item from a cart.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItem($id)
    {
        $user = auth()->user();
        
        // Get the cart item
        $cartItem = CartItem::findOrFail($id);
        
        // Ensure the user owns the cart item
        $cart = Cart::findOrFail($cartItem->cart_id);
        if ($cart->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to remove this cart item'
            ], 403);
        }
        
        // Delete the cart item
        $cartItem->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart item removed successfully'
        ]);
    }
}

// Example database schema (for reference)
/*
Schema::create('carts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('name');
    $table->string('type')->default('default'); // 'default', 'wishlist', 'saveforlater'
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});

Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained();
    $table->integer('quantity');
    $table->string('size')->nullable();
    $table->string('color')->nullable();
    $table->timestamps();
});

// Add active_cart_id to users table
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('active_cart_id')->nullable()->constrained('carts');
});
*/

// For demo purposes, return a sample response:
header('Content-Type: application/json');
echo json_encode([
    'message' => 'Cart API endpoints documentation file. This file provides the API structure and implementation details.',
    'endpoints' => [
        'GET /api/cart',
        'GET /api/cart/{id}',
        'POST /api/cart/add',
        'PUT /api/cart/update',
        'DELETE /api/cart/remove/{id}',
        'DELETE /api/cart/clear/{id}',
        'POST /api/cart/create',
        'PUT /api/cart/{id}',
        'DELETE /api/cart/{id}',
        'POST /api/cart/{id}/set-active',
        'POST /api/cart/move-items'
    ],
    'key_principles' => [
        'Store only product_id and quantity in cart',
        'Calculate prices and totals on the frontend',
        'Fetch latest product data when displaying cart',
        'Support multiple cart types (default, wishlist, save for later)'
    ]
], JSON_PRETTY_PRINT); 