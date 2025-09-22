<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\ProductTemplate;
use App\Models\Warehouse;

class CartController extends Controller
{
    /**
     * Get cart items for the authenticated customer.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Use the standard way to get the authenticated customer
            $customer = Auth::guard('customer')->user();

            // Ensure customer is authenticated
            if (!$customer) {
                Log::warning('CartController.index - Unauthorized access attempt.');
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $cartType = $request->input('type', 'default'); // Keep type filtering if needed
            $carts = [];
            
            // Use simplified logging for authenticated user
            $logData = [
                'customer_id' => $customer->id,
                'cart_type_requested' => $cartType,
            ];
            Log::info('CartController.index called by authenticated customer', $logData);

            // Directly query carts for the authenticated customer
                    $query = Cart::where('customer_id', $customer->id);
                    
            if ($request->has('type') && $request->input('type') !== 'all') { // Allow fetching all types if 'all' is specified
                        $query->where('type', $cartType);
                    }
                    
                    $carts = $query->get();
            Log::info('Found carts for customer', ['customer_id' => $customer->id, 'count' => $carts->count()]);
                    
            // If no carts found, automatically create a default cart
            if ($carts->isEmpty() && (!$request->has('type') || $request->input('type') === 'default')) {
                Log::info('Creating default cart for customer', ['customer_id' => $customer->id]);
                        
                // Check if a default cart somehow exists already (due to potential race condition or previous logic)
                $existingDefault = Cart::where('customer_id', $customer->id)->where('is_default', true)->first();
                
                if (!$existingDefault) {
                        $cart = new Cart();
                        $cart->customer_id = $customer->id;
                    $cart->type = 'default'; // Ensure type is 'default'
                        $cart->is_default = true;
                    $cart->name = 'Default Cart'; // Use the new default name
                        $cart->save();
                        
                        $carts = collect([$cart]);
                    Log::info('Default cart created successfully', ['cart_id' => $cart->id, 'customer_id' => $customer->id]);
                } else {
                    Log::warning('Default cart already exists for customer, skipping creation.', ['customer_id' => $customer->id, 'existing_cart_id' => $existingDefault->id]);
                    $carts = collect([$existingDefault]); // Use the existing default cart
                }
            }

            // Removed guest cart merging logic

            if ($carts->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart is empty',
                    'data' => [
                        'carts' => [],
                        'items' => [], // Keep items for consistency
                        'total' => 0
                    ]
                ]);
            }

            // If request specifies cart_id, filter the fetched carts
            $cartId = $request->input('cart_id');
            if ($cartId) {
                // Ensure the requested cart belongs to the authenticated customer
                $cart = $carts->where('id', $cartId)->first();
                if (!$cart) {
                    Log::warning('Customer tried to access cart not belonging to them or non-existent', [
                        'customer_id' => $customer->id, 
                        'requested_cart_id' => $cartId
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Cart not found or access denied',
                ], 404);
                }
                $carts = collect([$cart]); // Only process the requested cart
            }

            $result = [];
            $totalAmount = 0; // Initialize total amount for all carts returned
            
            foreach ($carts as $cart) {
                // 使用with预加载关联数据，避免N+1查询问题
                $cartItems = CartItem::with(['product.media', 'product.template'])
                    ->where('cart_id', $cart->id)
                    ->get();

                $items = [];
                $cartTotal = 0;

                foreach ($cartItems as $item) {
                    $product = $item->product;
                    if (!$product) {
                        continue; // 跳过无效产品
                    }
                    
                    // Get current price from the product
                    $price = $product->selling_price ?? $product->price;
                    // Calculate subtotal dynamically
                    $subtotal = $price * $item->quantity;

                    $cartTotal += $subtotal;
                    $totalAmount += $subtotal;
                    
                    // 尝试获取关联的模板
                    $template = null;
                    $parameterGroup = $item->parameter_group;
                    $templateId = $item->specifications['template_id'] ?? null;
                    
                    if ($templateId) {
                        $template = ProductTemplate::with(['category', 'media'])
                            ->find($templateId);
                    } elseif ($product->template_id) {
                        $template = ProductTemplate::with(['category', 'media'])
                            ->find($product->template_id);
                    } else {
                        // 通过product_template_product关联查找
                        $template = $item->productTemplate();
                    }
                    
                    // 获取产品图片
                    $images = [];
                    
                    // 优先使用getAllImages方法
                    if (method_exists($product, 'getAllImages')) {
                        $images = $product->getAllImages();
                    } 
                    // 回退到media关系
                    elseif ($product->relationLoaded('media') && $product->media->isNotEmpty()) {
                        $images = $product->media->map(function($media) {
                            return asset('storage/' . $media->path);
                        })->toArray();
                    }
                    // 回退到images数组
                    elseif (!empty($product->images) && is_array($product->images)) {
                        $images = array_map(function($image) {
                            return asset('storage/' . $image);
                        }, $product->images);
                    }
                    
                    if (empty($images)) {
                        $images[] = asset('images/placeholder.png');
                    }

                    $items[] = [
                    'id' => $item->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                    'quantity' => $item->quantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'original_price' => $product->price,
                        'selling_price' => $product->selling_price,
                        'discount_percentage' => $product->discount_percentage ?? 0,
                    'specifications' => $item->specifications,
                        'parameter_group' => $parameterGroup,
                        'images' => $images,
                        'template' => $template ? [
                            'id' => $template->id,
                            'name' => $template->name,
                            'category' => $template->category ? [
                                'id' => $template->category->id,
                                'name' => $template->category->name
                            ] : null,
                            'parameters' => $template->parameters
                        ] : null,
                        'parameters' => $product->parameters
                    ];
                }
                
                $result[] = [
                    'id' => $cart->id,
                    'name' => $cart->name ?: ($cart->type === 'default' ? '默认购物车' : $cart->type),
                    'type' => $cart->type,
                    'is_default' => $cart->is_default,
                    'items' => $items,
                    'total' => $cartTotal,
                    'item_count' => count($items)
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart items retrieved successfully',
                'data' => [
                    'carts' => $result,
                    'total' => $totalAmount,
                    'cart_count' => count($result)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve cart items', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart items. Please try again.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        // --- Authentication Debug Logging --- START ---
        $authCheckResult = Auth::guard('customer')->check();
        $authenticatedCustomer = $request->user('customer'); // Attempt to get user via request helper
        Log::info('CartController.store - Authentication Check', [
            'auth_guard_check' => $authCheckResult,
            'request_user_customer_id' => $authenticatedCustomer ? $authenticatedCustomer->id : null,
            'isAuthenticatedViaGuard' => Auth::check(),
            'default_guard' => config('auth.defaults.guard'),
            'session_id' => session()->getId(),
            'has_session_token' => $request->hasSession() ? $request->session()->has('_token') : 'NoSession',
            'has_auth_token' => $request->bearerToken() ? 'Yes' : 'No',
        ]);
        // --- Authentication Debug Logging --- END ---
        
        // Get authenticated customer
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            Log::warning('CartController.store - Unauthorized access attempt.');
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }
        
        $validator = Validator::make($request->all(), [
            'cart_id' => 'nullable|integer|exists:carts,id,customer_id,' . $customer->id,
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'specifications' => 'nullable|array', // e.g., ['color' => 'Red', 'size' => 'M']
            'parameter_group' => 'nullable|string',
            'template_id' => 'nullable|integer|exists:product_templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Invalid data provided', 'errors' => $validator->errors()], 422);
        }

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');
        $specifications = $request->input('specifications');
        $parameterGroup = $request->input('parameter_group');
        $cartId = $request->input('cart_id');
        $templateId = $request->input('template_id');

        Log::info('CartController.store - Processing Add to Cart', [
            'customer_id' => $customer->id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'specifications' => $specifications,
            'parameter_group' => $parameterGroup,
            'template_id' => $templateId,
            'target_cart_id' => $cartId
        ]);

        DB::beginTransaction();
        try {
            // Find the product
            $product = Product::find($productId);
            if (!$product) {
                Log::error('CartController.store - Product not found', ['product_id' => $productId]);
                return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
            }
            
            // Determine the target cart
            if ($cartId) {
                $cart = Cart::where('id', $cartId)->where('customer_id', $customer->id)->first();
                if (!$cart) {
                    Log::warning('CartController.store - Cart not found or does not belong to customer', ['cart_id' => $cartId, 'customer_id' => $customer->id]);
                    return response()->json(['status' => 'error', 'message' => 'Cart not found or access denied'], 404);
                }
            } else {
                // Get or create the default cart
                $cart = Cart::firstOrCreate(
                    ['customer_id' => $customer->id, 'is_default' => true],
                    ['name' => 'Default Cart', 'type' => 'default']
                );
                Log::info('CartController.store - Using default cart', ['cart_id' => $cart->id, 'customer_id' => $customer->id]);
            }

            // Check stock availability using StockService
            // First, get the default warehouse ID (adjust logic if needed)
            $defaultWarehouse = Warehouse::where('status', 1)->first(); // Assuming status 1 is active/default
            if (!$defaultWarehouse) {
                Log::error('CartController.store - Default warehouse not found.');
                return response()->json(['status' => 'error', 'message' => 'Cannot check stock, default warehouse not configured.'], 500);
            }
            $warehouseIdToCheck = $defaultWarehouse->id;
            
            $stockService = app(\App\Services\StockService::class);
            // Use the correct checkStock method
            if (!$stockService->checkStock($productId, $warehouseIdToCheck, $quantity)) {
                $availableStock = $stockService->getStock($productId); // Get total stock across all warehouses for message
                Log::warning('CartController.store - Insufficient stock', [
                    'product_id' => $productId, 
                    'requested' => $quantity, 
                    'available' => $availableStock // Log total available stock
                ]);
                return response()->json([
                    'status' => 'error', 
                    'message' => "Insufficient stock for product {$product->name}. Only {$availableStock} available."
                ], 400);
            }

            // Find existing item or create a new one
            // Consider specifications for uniqueness
            $cartItem = CartItem::where('cart_id', $cart->id)
                              ->where('product_id', $productId)
                              ->whereJsonContains('specifications', $specifications ?? []) // Match specifications
                              ->first();

            if ($cartItem) {
                // Update quantity
                $cartItem->quantity += $quantity;
                $cartItem->save();
                Log::info('CartController.store - Updated item quantity', ['cart_item_id' => $cartItem->id, 'new_quantity' => $cartItem->quantity]);
            } else {
                // Create new item
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'specifications' => $specifications,
                    'parameter_group' => $parameterGroup,
                    'template_id' => $templateId,
                ]);
                Log::info('CartController.store - Created new cart item', ['cart_item_id' => $cartItem->id]);
            }

            DB::commit();

            // Return success response with cart item details
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => [
                    'cart_id' => $cart->id,
                    'item' => $cartItem->load('product') // Load product details for response
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CartController.store - Failed to add item to cart', [
                'customer_id' => $customer->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        // --- Authentication Debug Logging --- START ---
        $authCheckResult = Auth::guard('customer')->check();
        $authenticatedCustomerViaAuth = Auth::guard('customer')->user();
        $authenticatedCustomerViaRequest = $request->user('customer'); // Alternative method
        $authenticatedCustomerViaAttribute = $request->attributes->get('customer'); // Original method

        Log::info('CartController.update - Authentication Check', [
            'item_id' => $id,
            'auth_guard_check' => $authCheckResult,
            'auth_guard_user_id' => $authenticatedCustomerViaAuth ? $authenticatedCustomerViaAuth->id : null,
            'request_user_customer_id' => $authenticatedCustomerViaRequest ? $authenticatedCustomerViaRequest->id : null,
            'request_attribute_customer_present' => !is_null($authenticatedCustomerViaAttribute),
            'authorization_header_present' => $request->hasHeader('Authorization'),
            // Optionally log part of the token for debugging (BE CAREFUL WITH SENSITIVE DATA)
            // 'authorization_header' => $request->header('Authorization') ? substr($request->header('Authorization'), 0, 15) . '...' : null
        ]);
        // --- Authentication Debug Logging --- END ---
        
        try {
            // Use the standard Auth facade for the customer guard
            $customer = Auth::guard('customer')->user();

            // Ensure customer is authenticated
            if (!$customer) {
                Log::warning('CartController.update - Unauthorized attempt to update cart item (Auth facade check failed).', [
                    'item_id' => $id,
                    'reason' => 'Auth::guard(\'customer\')->user() returned null'
                ]);
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $customerId = $customer->id;
            Log::info('CartController.update - Authenticated successfully', ['customer_id' => $customerId, 'item_id' => $id]);
            
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                Log::error('CartController.update - Validation failed', [
                    'customer_id' => $customerId,
                'item_id' => $id,
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
            }

            // Find the cart item and ensure it belongs to the customer's cart
            $cartItem = CartItem::where('id', $id)
                ->whereHas('cart', function ($query) use ($customerId) {
                    $query->where('customer_id', $customerId);
                })
                ->first();

            if (!$cartItem) {
                Log::warning('CartController.update - Cart item not found or access denied', [
                    'customer_id' => $customerId,
                    'item_id' => $id
                ]);
                return response()->json(['status' => 'error', 'message' => 'Cart item not found or access denied'], 404);
                }
                
            $quantity = (int)$request->input('quantity');
            
            // Find the associated product to check stock
            $product = Product::find($cartItem->product_id);
            if (!$product) {
                Log::error('CartController.update - Product associated with cart item not found', [
                    'product_id' => $cartItem->product_id,
                    'item_id' => $id,
                    'customer_id' => $customerId
                    ]);
                // Decide if we should remove the item or return error. Let's return error for now.
                return response()->json(['status' => 'error', 'message' => 'Associated product not found.'], 404);
                    }
                
            // Use the dedicated method to check stock
            $availableStockForUpdate = $product->getTotalStock();
            if ($availableStockForUpdate < $quantity) {
                 Log::warning('CartController.update - Insufficient stock for update', [
                    'product_id' => $product->id, 
                    'requested_quantity' => $quantity, 
                    'available_stock' => $availableStockForUpdate,
                    'customer_id' => $customerId,
                    'item_id' => $id
                    ]);
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Insufficient stock available. Only ' . $availableStockForUpdate . ' left.'
                ], 400);
            }

            // Simple update of the quantity
            $cartItem->update([
                'quantity' => $quantity,
                // Subtotal will be recalculated when fetching the cart via index or handled by an observer/accessor if set up
            ]);
            
            Log::info('Cart item updated successfully (quantity only)', ['item_id' => $id, 'customer_id' => $customerId, 'new_quantity' => $quantity]);

            // Return the current state of the cart by calling the index method
            return $this->index($request);

        } catch (\Exception $e) {
            // Keep general exception handling
            Log::error('Error updating cart item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $request->user('customer') ? $request->user('customer')->id : 'N/A', // Safely get customer id
                'item_id' => $id
            ]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred while updating the cart item.'], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Get the authenticated customer from the middleware
            $customer = Auth::guard('customer')->user();
            
            // Ensure customer is authenticated
                if (!$customer) {
                Log::warning('CartController.destroy - Unauthorized attempt to remove cart item.', ['item_id' => $id]);
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
                }
                
            $customerId = $customer->id;

            // Find the cart item and ensure it belongs to the customer's cart
            $cartItem = CartItem::where('id', $id)
                ->whereHas('cart', function ($query) use ($customerId) {
                    $query->where('customer_id', $customerId);
                })
                ->first();

            if (!$cartItem) {
                Log::warning('CartController.destroy - Cart item not found or access denied', [
                    'customer_id' => $customerId,
                    'item_id' => $id
                ]);
                // If item not found, maybe it was already deleted. Return success.
                return response()->json([
                    'success' => true, 
                    'message' => 'Item not found or already removed'
                ]); 
                    }

            // Delete the item
            DB::beginTransaction();
            try {
                $cartItem->delete();
                DB::commit();
                Log::info('Cart item removed successfully', ['item_id' => $id, 'customer_id' => $customerId]);
                    return response()->json([
                    'success' => true,
                    'message' => 'Item removed from cart'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error removing cart item during transaction', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'customer_id' => $customerId,
                    'item_id' => $id
                    ]);
                return response()->json(['status' => 'error', 'message' => 'Failed to remove item from cart.'], 500);
            }

        } catch (\Exception $e) {
            // Catch general exceptions before transaction
            Log::error('Error preparing to remove cart item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $request->attributes->get('customer_id', 'N/A'),
                'item_id' => $id
            ]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred while removing the item from the cart.'], 500);
        }
    }

    /**
     * Clear all items from a cart
     */
    public function clear(Request $request)
    {
        try {
            // Get the authenticated customer from the middleware
            $customer = Auth::guard('customer')->user();
            
            // Ensure customer is authenticated
            if (!$customer) {
                Log::warning('CartController.clear - Unauthorized attempt to clear cart.');
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $customerId = $customer->id;
            $cartId = $request->input('cart_id'); // Allow specifying a cart ID to clear
            $cartType = $request->input('type', 'default'); // Allow specifying a cart type

            Log::info('CartController.clear called', [
                'customer_id' => $customerId,
                'requested_cart_id' => $cartId,
                'requested_cart_type' => $cartType
            ]);

            // Find the target cart(s)
            $query = Cart::where('customer_id', $customerId);
                
                if ($cartId) {
                    $query->where('id', $cartId);
            } else {
                // If no specific cart ID, clear the default cart of the specified type
                $query->where('type', $cartType)->where('is_default', true);
                }
                
            $carts = $query->get();

            if ($carts->isEmpty()) {
                Log::info('CartController.clear - No cart found to clear', [
                    'customer_id' => $customerId, 
                    'cart_id' => $cartId, 
                    'type' => $cartType
                ]);
                return response()->json([
                    'success' => true, 
                    'message' => 'Cart already empty or not found'
                ]);
            }

            // Clear items from the found cart(s)
            DB::beginTransaction();
            try {
                $clearedCartIds = $carts->pluck('id');
                $deletedCount = CartItem::whereIn('cart_id', $clearedCartIds)->delete();
                DB::commit();
                
                Log::info('Cart cleared successfully', [
                    'customer_id' => $customerId, 
                    'cleared_cart_ids' => $clearedCartIds->toArray(),
                    'items_deleted' => $deletedCount
                ]);

            return response()->json([
                'success' => true,
                    'message' => 'Cart cleared successfully'
            ]);
        } catch (\Exception $e) {
                DB::rollBack();
                 Log::error('Error clearing cart during transaction', [
                'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'customer_id' => $customerId,
                    'cart_ids' => $carts->pluck('id')->toArray()
            ]);
                return response()->json(['status' => 'error', 'message' => 'Failed to clear cart.'], 500);
            }

        } catch (\Exception $e) {
             // Catch general exceptions before transaction
            Log::error('Error preparing to clear cart', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $request->attributes->get('customer_id', 'N/A')
            ]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred while clearing the cart.'], 500);
        }
    }

    /**
     * Apply coupon to cart
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyCoupon(Request $request)
    {
        try {
            Log::info('Apply coupon method called', $request->all());
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            // TODO: 实现优惠券验证和应用逻辑
            
            // 暂时返回一个模拟的响应
            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully',
                'discount' => 10.00,
                'coupon' => $request->code
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to apply coupon', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon. Please try again.',
            ], 500);
        }
    }

    /**
     * Create a new cart
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCart(Request $request)
    {
        /** @var Customer|null $customer */
        $customer = Auth::s('customer')->user();
        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:cart,wishlist,temp', // Allow specific types
            'is_default' => 'nullable|boolean',
            // Do NOT require product_id here
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $isDefault = $request->input('is_default', false);
            $cartType = $request->input('type', 'cart'); // Default to 'cart'

            // If setting as default, ensure no other cart is default
            if ($isDefault) {
                Cart::where('customer_id', $customer->id)->update(['is_default' => false]);
            }

            $cart = Cart::create([
                'customer_id' => $customer->id,
                'name' => $request->input('name'),
                'type' => $cartType,
                'is_default' => $isDefault,
            ]);

            DB::commit();

            Log::info('CartController.createCart - New cart created', [
                'customer_id' => $customer->id,
                'cart_id' => $cart->id,
                'name' => $cart->name,
                'type' => $cart->type,
                'is_default' => $cart->is_default,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cart created successfully.',
                'data' => $cart
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CartController.createCart - Failed to create cart', [
                'customer_id' => $customer->id,
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create cart. ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update cart details (rename, change type, etc.)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCart(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'type' => 'nullable|string|in:default,wishlist,saveforlater',
                'is_default' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            
            // 获取购物车
            $cart = Cart::findOrFail($id);
            
            // 验证访问权限
            $user = $request->user();
            if ($user) {
                $customer = Customer::where('email', $user->email)->first();
                if (!$customer || $cart->customer_id !== $customer->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to cart',
                    ], 403);
                }
            } else {
                // 访客购物车验证
                $sessionId = $request->session()->getId();
                if ($cart->session_id !== $sessionId || $cart->customer_id !== null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to cart',
                    ], 403);
                }
            }
            
            // 更新购物车信息
            if ($request->has('name')) {
                $cart->name = $request->input('name');
            }
            
            if ($request->has('type')) {
                $cart->type = $request->input('type');
            }
            
            if ($request->has('is_default')) {
                $wasDefault = $cart->is_default;
                $cart->is_default = $request->input('is_default');
                
                // 如果设为默认，更新其他同类型的购物车
                if ($cart->is_default && !$wasDefault) {
                    if ($user) {
                        Cart::where('customer_id', $cart->customer_id)
                            ->where('type', $cart->type)
                            ->where('id', '!=', $cart->id)
                            ->update(['is_default' => false]);
                    } else {
                        Cart::where('session_id', $cart->session_id)
                            ->where('customer_id', null)
                            ->where('type', $cart->type)
                            ->where('id', '!=', $cart->id)
                            ->update(['is_default' => false]);
                    }
                }
            }
            
            $cart->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                    'data' => [
                    'cart_id' => $cart->id,
                    'name' => $cart->name,
                    'type' => $cart->type,
                    'is_default' => $cart->is_default
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update cart', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart. Please try again.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Delete a cart
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCart(Request $request, $id)
    {
        try {
            // 获取购物车
            $cart = Cart::findOrFail($id);
            
            // 验证访问权限
            $user = $request->user();
            if ($user) {
                $customer = Customer::where('email', $user->email)->first();
                if (!$customer || $cart->customer_id !== $customer->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to cart',
                    ], 403);
                }
            } else {
                // 访客购物车验证
                $sessionId = $request->session()->getId();
                if ($cart->session_id !== $sessionId || $cart->customer_id !== null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to cart',
                    ], 403);
                }
            }
            
            // 删除购物车内的商品
            CartItem::where('cart_id', $cart->id)->delete();
            
            // 删除购物车
            $cart->delete();
            
            // 如果删除的是默认购物车，尝试将同类型的其他购物车设为默认
            if ($cart->is_default) {
                if ($user) {
                    $firstCart = Cart::where('customer_id', $customer->id)
                        ->where('type', $cart->type)
                        ->first();
                    
                    if ($firstCart) {
                        $firstCart->is_default = true;
                        $firstCart->save();
                    }
                } else {
                    $firstCart = Cart::where('session_id', $sessionId)
                        ->where('customer_id', null)
                        ->where('type', $cart->type)
                        ->first();
                    
                    if ($firstCart) {
                        $firstCart->is_default = true;
                        $firstCart->save();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete cart', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cart. Please try again.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Move items between carts
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveItems(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'from_cart_id' => 'required|integer|exists:carts,id',
                'to_cart_id' => 'required|integer|exists:carts,id',
                'item_ids' => 'required|array',
                'item_ids.*' => 'required|integer|exists:cart_items,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            
            $fromCartId = $request->input('from_cart_id');
            $toCartId = $request->input('to_cart_id');
            $itemIds = $request->input('item_ids');
            
            // 验证购物车权限
            $user = $request->user();
            $sessionId = $request->hasSession() ? $request->session()->getId() : null;
            
            $fromCart = Cart::findOrFail($fromCartId);
            $toCart = Cart::findOrFail($toCartId);
            
            // 验证访问权限
            if ($user) {
                $customer = Customer::where('email', $user->email)->first();
                if (!$customer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer not found',
                    ], 404);
                }
                
                if (($fromCart->customer_id !== $customer->id) || ($toCart->customer_id !== $customer->id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to cart',
                    ], 403);
                }
            } else if ($sessionId) {
                // 访客购物车验证
                if (($fromCart->session_id !== $sessionId || $fromCart->customer_id !== null) ||
                    ($toCart->session_id !== $sessionId || $toCart->customer_id !== null)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to cart',
                    ], 403);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No session available',
                ], 401);
            }
            
            // 获取要移动的购物车项目
            $cartItems = CartItem::whereIn('id', $itemIds)
                ->where('cart_id', $fromCartId)
                ->get();
            
            // 检查要移动的项目是否在源购物车中
            if ($cartItems->count() !== count($itemIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some items not found in the source cart',
                ], 400);
            }
            
            // 移动项目
            foreach ($cartItems as $item) {
                // 检查目标购物车中是否已存在相同产品和规格的项目
                $existingItem = CartItem::where('cart_id', $toCartId)
                    ->where('product_id', $item->product_id)
                    ->get()
                    ->filter(function ($existing) use ($item) {
                        // 比较JSON规格
                        return json_encode($existing->specifications) === json_encode($item->specifications);
                    })
                    ->first();
                
                if ($existingItem) {
                    // 如果存在，增加数量
                    $existingItem->quantity += $item->quantity;
                    $existingItem->save();
                    
                    // 删除原项目
                    $item->delete();
                } else {
                    // 如果不存在，直接更新购物车ID
                    $item->cart_id = $toCartId;
                    $item->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Items moved successfully',
                'data' => [
                    'from_cart_id' => $fromCartId,
                    'to_cart_id' => $toCartId,
                    'items_moved' => $cartItems->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to move items between carts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to move items. Please try again.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 将访客购物车合并到客户账户
     * 
     * @param \Illuminate\Support\Collection $guestCarts 访客购物车集合
     * @param int $customerId 客户ID
     * @return void
     */
    protected function mergeGuestCartsToCustomer($guestCarts, $customerId)
    {
        try {
            Log::info('开始合并购物车', ['customer_id' => $customerId, 'guest_carts_count' => $guestCarts->count()]);
            
            // 获取客户的所有购物车
            $customerCarts = Cart::where('customer_id', $customerId)->get();
            
            foreach ($guestCarts as $guestCart) {
                // 查找客户是否有相同类型的购物车
                $customerCart = $customerCarts->where('type', $guestCart->type)->first();
                
                if ($customerCart) {
                    Log::info('找到匹配的客户购物车', [
                        'guest_cart_id' => $guestCart->id,
                        'customer_cart_id' => $customerCart->id,
                        'type' => $guestCart->type
                    ]);
                    
                    // 获取访客购物车的所有项目
                    $guestItems = CartItem::where('cart_id', $guestCart->id)->get();
                    
                    // 将访客购物车项目移动到客户购物车
                    foreach ($guestItems as $guestItem) {
                        // 检查客户购物车中是否已有相同商品
                        $customerItem = CartItem::where('cart_id', $customerCart->id)
                            ->where('product_id', $guestItem->product_id)
                            ->get()
                            ->filter(function ($item) use ($guestItem) {
                                // 比较规格是否相同
                                if (!$item->specifications && !$guestItem->specifications) {
                                    return true;
                                }
                                if (!$item->specifications || !$guestItem->specifications) {
                                    return false;
                                }
                                return json_encode($item->specifications) === json_encode($guestItem->specifications);
                            })
                            ->first();
                            
                        if ($customerItem) {
                            // 合并数量
                            Log::info('合并购物车项目数量', [
                                'customer_item_id' => $customerItem->id,
                                'guest_item_id' => $guestItem->id,
                                'product_id' => $guestItem->product_id,
                                'old_quantity' => $customerItem->quantity,
                                'add_quantity' => $guestItem->quantity
                            ]);
                            
                            $customerItem->quantity += $guestItem->quantity;
                            $customerItem->save();
                            
                            // 删除访客购物车项目
                            $guestItem->delete();
                        } else {
                            // 将访客购物车项目移动到客户购物车
                            Log::info('移动购物车项目', [
                                'guest_item_id' => $guestItem->id,
                                'from_cart_id' => $guestCart->id,
                                'to_cart_id' => $customerCart->id
                            ]);
                            
                            $guestItem->cart_id = $customerCart->id;
                            $guestItem->save();
                        }
                    }
                    
                    // 删除访客购物车
                    Log::info('删除已合并的访客购物车', ['cart_id' => $guestCart->id]);
                    $guestCart->delete();
                } else {
                    // 如果客户没有匹配的购物车类型，直接关联访客购物车到客户
                    Log::info('将访客购物车直接关联到客户', [
                        'cart_id' => $guestCart->id,
                        'customer_id' => $customerId
                    ]);
                    
                    $guestCart->customer_id = $customerId;
                    $guestCart->save();
                }
            }
            
            Log::info('购物车合并完成', ['customer_id' => $customerId]);
        } catch (\Exception $e) {
            Log::error('合并购物车失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 