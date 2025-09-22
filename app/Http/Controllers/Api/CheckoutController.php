<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\StockService; // Assuming a stock service exists
use App\Services\PaymentService; // Assuming a payment service exists
use App\Models\Address; // Import Address model
use App\Models\Customer; // Import Customer model
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Import Str facade
use App\Models\Warehouse; // Import Warehouse model
use App\Http\Resources\SalesOrderResource;
use App\Exceptions\StockUnavailableException;
use App\Services\OrderMailService;
use Illuminate\Support\Facades\Cache; // <-- Import Cache

class CheckoutController extends Controller
{
    /**
     * 库存服务
     *
     * @var \App\Services\StockService
     */
    protected $stockService;
    
    /**
     * 支付服务
     *
     * @var \App\Services\PaymentService
     */
    protected $paymentService;
    
    /**
     * 订单邮件服务
     *
     * @var \App\Services\OrderMailService
     */
    protected $orderMailService;

    /**
     * 构造函数
     *
     * @param StockService $stockService
     * @param PaymentService $paymentService
     * @param OrderMailService $orderMailService
     */
    public function __construct(StockService $stockService, PaymentService $paymentService, OrderMailService $orderMailService)
    {
        $this->stockService = $stockService;
        $this->paymentService = $paymentService;
        $this->orderMailService = $orderMailService;
    }

    /**
     * Prepare checkout data based on cart and selected items.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function prepare(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'cart_id' => 'required|integer|exists:carts,id,customer_id,' . $customer->id,
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'integer|exists:cart_items,id', // Ensure item IDs exist in cart_items
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Invalid input', 'errors' => $validator->errors()], 422);
        }

        $cartId = $request->input('cart_id');
        $selectedItemIds = $request->input('item_ids');

        try {
            $cart = Cart::findOrFail($cartId);
            
            // Ensure the cart belongs to the authenticated customer (redundant due to validation, but safe)
            if ($cart->customer_id !== $customer->id) {
                 Log::warning('CheckoutController.prepare - Attempt to access another customer\'s cart.', [
                    'auth_customer_id' => $customer->id,
                    'requested_cart_id' => $cartId
                ]);
                return response()->json(['status' => 'error', 'message' => 'Cart access denied'], 403);
            }

            $query = CartItem::with(['product', 'product.media', 'product.template'])
                        ->where('cart_id', $cartId);

            // Filter by selected item IDs if provided
            if (!empty($selectedItemIds)) {
                // Verify selected items actually belong to the cart
                $validItemIds = CartItem::where('cart_id', $cartId)->whereIn('id', $selectedItemIds)->pluck('id');
                if (count($validItemIds) !== count($selectedItemIds)) {
                    Log::warning('CheckoutController.prepare - Invalid item IDs provided for cart.', [
                        'customer_id' => $customer->id,
                        'cart_id' => $cartId,
                        'provided_ids' => $selectedItemIds,
                        'valid_ids' => $validItemIds->toArray()
                    ]);
                     return response()->json(['status' => 'error', 'message' => 'Invalid item selection.'], 400);
                }
                $query->whereIn('id', $validItemIds);
            }

            $checkoutItems = $query->get();

            if ($checkoutItems->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'No items selected or cart is empty.'], 400);
            }

            // Calculate totals dynamically
            $subtotal = 0;
            $formattedItems = [];
            $errors = [];
            
            foreach ($checkoutItems as $item) {
                if (!$item->product) {
                     Log::error('CheckoutController.prepare - CartItem missing product relation', ['cart_item_id' => $item->id]);
                     $errors[] = "Item with ID {$item->id} is invalid and cannot be checked out.";
                     continue; // Skip this item
                }

                // TODO: Add stock check here if necessary before showing checkout page
                // $availableStock = $this->stockService->getStock($item->product_id);
                // if ($availableStock < $item->quantity) { ... }

                $price = $item->product->selling_price ?? $item->product->price;
                $itemSubtotal = $price * $item->quantity;
                $subtotal += $itemSubtotal;

                $images = $item->product->getAllImages(); // Assuming this method exists and works
                if (empty($images)) {
                    $images[] = asset('images/placeholder.png');
                }

                $formattedItems[] = [
                    'cart_item_id' => $item->id,
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'sku' => $item->product->sku,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'subtotal' => $itemSubtotal,
                    'specifications' => $item->specifications,
                    'images' => $images, // Use the fetched images
                    // Add template/parameter info if needed
                ];
            }
            
             if (!empty($errors)) {
                return response()->json(['status' => 'error', 'message' => 'Some items are invalid.', 'errors' => $errors], 400);
            }

            // TODO: Calculate tax, discounts, shipping etc. based on customer, items, coupons
            $tax = 0; // Placeholder
            $discount = 0; // Placeholder
             $shipping_cost = 0; // Placeholder for shipping cost
            $total = $subtotal + $tax + $shipping_cost - $discount;

            // Get customer addresses
            /** @var \App\Models\Customer $customer */
            $addresses = $customer->addresses()->orderByDesc('is_default_shipping')->orderByDesc('is_default_billing')->latest()->get()->map(function($address) {
                 return [
                     'id' => $address->id,
                     'type' => $address->type,
                     'contact_person' => $address->contact_person,
                     'contact_phone' => $address->contact_phone,
                     'line1' => $address->line1,
                     'line2' => $address->line2,
                     'city' => $address->city,
                     'postcode' => $address->postcode,
                     'state' => $address->state,
                     'country' => $address->country,
                     'is_default_billing' => (bool)$address->is_default_billing,
                     'is_default_shipping' => (bool)$address->is_default_shipping,
                     'formatted' => $address->formatted_address // Using the accessor
                 ];
             });

            // TODO: Get available payment methods
            $paymentMethods = [
                ['id' => 'cash', 'name' => 'Cash'],
                ['id' => 'bank_transfer', 'name' => 'Bank Transfer']
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $formattedItems,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                     'shipping_cost' => $shipping_cost,
                    'discount' => $discount,
                    'total' => $total,
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        // Add address etc. if needed
                    ],
                    'addresses' => $addresses, // Add addresses to response
                    'payment_methods' => $paymentMethods,
                    'cart_id' => $cartId // Pass cart_id back for processing step
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Cart not found.'], 404);
        } catch (\Exception $e) {
            Log::error('CheckoutController.prepare - Error preparing checkout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $customer->id,
                'cart_id' => $cartId,
                'item_ids' => $selectedItemIds
            ]);
            return response()->json(['status' => 'error', 'message' => 'Could not prepare checkout.'], 500);
        }
    }

    /**
     * Process the checkout and create the order.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(Request $request)
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }
        // Add explicit type check/assertion (alternative to @var)
        if (!($customer instanceof Customer)) {
             Log::error('CheckoutController.process - Authenticated user is not an instance of Customer model.', ['user_id' => $customer->id ?? null]);
             return response()->json(['status' => 'error', 'message' => 'Invalid user type.'], 500);
        }

        $validator = Validator::make($request->all(), [
            'cart_id' => 'nullable|integer|exists:carts,id,customer_id,' . $customer->id, // Cart is nullable now
            'order_id' => 'nullable|integer|exists:sales_orders,id,customer_id,' . $customer->id, // Allow direct order ID for buy now
            'payment_method' => 'required|string',
            'shipping_method' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'shipping_cost' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.specifications' => 'nullable|array',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'address_details' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::warning('CheckoutController.process - Validation failed', [
                'customer_id' => $customer->id,
                'errors' => $validator->errors()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Invalid checkout data', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $cartId = $request->input('cart_id');
            $temporaryOrderId = $request->input('order_id');
            $orderItemsData = $request->input('items');
            $orderData = $request->only([
                'payment_method', 'shipping_method', 'subtotal',
                'shipping_cost', 'tax_amount', 'discount_amount', 'total_amount',
                'contact_name', 'contact_phone', 'address_details'
            ]);

            // Generate order number
            $orderData['order_number'] = 'ORD-' . strtoupper(Str::random(8));
            $orderData['order_date'] = now(); // Set order date
            $orderData['customer_id'] = $customer->id;
            $orderData['status'] = 'pending_payment'; // Initial status
            $orderData['shipping_status'] = 'pending_shipment';
            $orderData['payment_status'] = 'unpaid';

            $targetStoreId = null; // Initialize store ID

            // Find or create the SalesOrder
            if ($temporaryOrderId) {
                $order = SalesOrder::where('id', $temporaryOrderId)
                                    ->where('customer_id', $customer->id)
                                    ->where('status', 'pending_checkout') // Ensure it's the correct temporary order
                                    ->firstOrFail();
                // Update the temporary order with final details
                $order->update($orderData);
                $targetStoreId = $order->store_id; // Use existing store_id from temp order
                Log::info('CheckoutController.process - Updated existing temporary order.', ['order_id' => $order->id]);
            } else {
                // --- Logic for Cart Checkout: Find a suitable store --- 
                $activeWarehouses = Warehouse::where('status', 1)->get();
                if ($activeWarehouses->isEmpty()) {
                    DB::rollBack();
                    Log::error('CheckoutController.process - No active warehouses found for cart checkout.', ['customer_id' => $customer->id]);
                    return response()->json(['status' => 'error', 'message' => 'No available stores to fulfill the order.'], 500);
                }

                foreach ($activeWarehouses as $warehouse) {
                    $canFulfill = true;
                    foreach ($orderItemsData as $itemData) {
                        // *** Assumes StockService has this method ***
                        if (!$this->stockService->checkStockAvailabilityAtWarehouse($itemData['product_id'], $itemData['quantity'], $warehouse->id)) {
                            $canFulfill = false;
                            break; // No need to check other items in this warehouse
                        }
                    }

                    if ($canFulfill) {
                        $targetStoreId = $warehouse->id;
                        break; // Found a suitable warehouse
                    }
                }

                if (is_null($targetStoreId)) {
                    DB::rollBack();
                    Log::warning('CheckoutController.process - No single warehouse found with sufficient stock for all items.', [
                        'customer_id' => $customer->id,
                        'cart_id' => $cartId,
                        'items' => $orderItemsData
                    ]);
                    return response()->json(['status' => 'error', 'message' => 'Cannot fulfill order: No single store has all items in stock.'], 409); // 409 Conflict
                }
                
                // Add the found store_id to the order data
                $orderData['store_id'] = $targetStoreId;
                // ------------------------------------------------------

                // Create a new order from cart
                $order = SalesOrder::create($orderData);
                Log::info('CheckoutController.process - Created new order from cart.', ['order_id' => $order->id, 'cart_id' => $cartId, 'store_id' => $targetStoreId]);
            }
            
            if (is_null($targetStoreId)) {
                 // This case should theoretically not be reached if logic above is correct, but as a safeguard:
                     DB::rollBack();
                 Log::error('CheckoutController.process - Target Store ID is null unexpectedly before processing items.', ['order_id' => $order->id ?? null]);
                 return response()->json(['status' => 'error', 'message' => 'Internal error determining store.'], 500);
            }

            // Process order items - Remove old items and add new ones to handle updates
            $order->items()->delete(); // Clear existing items if any (especially for temporary orders)
            foreach ($orderItemsData as $itemData) {
                $productId = $itemData['product_id']; // Store product ID for reuse
                $product = Product::find($productId);
                if (!$product) {
                     Log::error('CheckoutController.process - Product not found during item creation.', ['product_id' => $productId, 'order_id' => $order->id]);
                     throw new \Exception("Product with ID {$productId} not found.");
                }
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $productId,
                    'product_name' => $product->name, // Add product name
                    'product_sku' => $product->sku,   // Add product SKU
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['price'],
                    'subtotal' => $itemData['price'] * $itemData['quantity'],
                    'specifications' => $itemData['specifications'] ?? null,
                ]);
                // TODO: Adjust stock decrease to use targetStoreId if necessary
                // Decrease stock - Potentially needs $targetStoreId
                // Ensure decreaseStock uses the determined targetStoreId
                 if (!is_null($targetStoreId)) { // Check if targetStoreId is determined
                     $this->stockService->decreaseStock($productId, $targetStoreId, $itemData['quantity'], 'sales', $order->id);
                     
                     // --- Clear relevant caches for this product --- 
                     $stockCacheKey = "product_stock_{$productId}";
                     $detailsCacheKey = "product_details_{$productId}";
                     Cache::forget($stockCacheKey);
                     Cache::forget($detailsCacheKey);
                     Log::info('CheckoutController.process - Cleared product cache after stock update.', ['order_id' => $order->id, 'product_id' => $productId, 'keys' => [$stockCacheKey, $detailsCacheKey]]);
                     // ----------------------------------------------

                 } else {
                     // Log error or handle case where store ID is still null (shouldn't happen with prior checks)
                     Log::error('CheckoutController.process - Cannot decrease stock, targetStoreId is null.', ['order_id' => $order->id, 'product_id' => $productId]);
                     // Depending on requirements, you might want to throw an exception here to rollback
                     throw new \Exception("Could not determine the store to decrease stock from for product ID: " . $productId);
                 }
            }

            // Process payment (Simulated)
             $paymentResult = $this->paymentService->processPayment(
                 $order, // Pass the SalesOrder object itself
                 $order->total_amount,
                 ['method' => $order->payment_method] // Pass payment data as an array
                // Add other necessary payment details here, e.g., card token
             );

            if ($paymentResult['success']) {
                $order->payment_status = 'paid';
                $order->status = 'processing'; // Update overall status
                // Ensure store_id is saved if it wasn't part of the initial create/update 
                // (though it should be included in $orderData or fetched for temp orders now)
                $order->store_id = $targetStoreId; 
                $order->save();

                // Clear the cart if checkout started from a cart
                if ($cartId) {
                    $cart = Cart::find($cartId);
                    if ($cart && $cart->customer_id === $customer->id) {
                        // Decide whether to clear all items or just the ones checked out
                        // For now, assume clear all items from this cart
                        $cart->items()->delete();
                        Log::info('CheckoutController.process - Cart cleared after successful checkout.', ['cart_id' => $cartId]);
                    }
                }
                
                // 发送订单确认邮件
                try {
                    $emailSent = $this->orderMailService->sendOrderConfirmationMail($order);
                    Log::info('CheckoutController.process - Order confirmation email status:', [
                        'order_id' => $order->id, 
                        'customer_email' => $customer->email,
                        'email_sent' => $emailSent ? 'Success' : 'Failed'
                    ]);
                } catch (\Exception $emailException) {
                    // 记录错误，但不影响订单处理
                    Log::error('CheckoutController.process - Failed to send order confirmation email:', [
                        'order_id' => $order->id,
                        'customer_id' => $customer->id,
                        'error' => $emailException->getMessage()
                    ]);
                }
                
                 DB::commit();
                 Log::info('CheckoutController.process - Order processed successfully.', ['order_id' => $order->id, 'customer_id' => $customer->id]);
                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully!',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]);
            } else {
                DB::rollBack();
                 Log::error('CheckoutController.process - Payment failed.', [
                    'order_id' => $order->id,
                    'payment_error' => $paymentResult['message'] ?? 'Unknown payment error'
                ]);
                // Optional: Update order status to payment_failed?
                 $order->status = 'payment_failed';
                 $order->payment_status = 'failed';
                 $order->save();
                return response()->json(['status' => 'error', 'message' => $paymentResult['message'] ?? 'Payment failed.'], 400);
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('CheckoutController.process - Model not found exception.', [
                'error' => $e->getMessage(),
                'customer_id' => $customer->id,
                'cart_id' => $request->input('cart_id'),
                'order_id' => $request->input('order_id')
             ]);
            return response()->json(['status' => 'error', 'message' => 'Error processing checkout: Required data not found.'], 404);
         } catch (\App\Exceptions\StockUnavailableException $e) {
            DB::rollBack();
            Log::warning('CheckoutController.process - Stock unavailable during checkout', [
                'error' => $e->getMessage(),
                'customer_id' => $customer->id,
                'cart_id' => $request->input('cart_id'),
                'order_id' => $request->input('order_id'),
                'product_id' => $e->getProductId(),
                'requested_qty' => $e->getRequestedQuantity()
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409); // 409 Conflict
        } catch (\Exception $e) {
                     DB::rollBack();
            Log::error('CheckoutController.process - General error processing checkout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $customer->id,
                'request_data' => $request->except(['card_number', 'cvv']) // Avoid logging sensitive data
            ]);
            return response()->json(['status' => 'error', 'message' => 'An error occurred while processing your order. Please try again.'], 500);
        }
    }
    
    /**
     * Handles the "Buy Now" scenario: Creates a temporary order.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyNow(Request $request)
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }
        if (!($customer instanceof Customer)) {
             return response()->json(['status' => 'error', 'message' => 'Invalid user type.'], 500);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'specifications' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Invalid product data', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $productId = $request->input('product_id');
            $quantity = $request->input('quantity');
            $specifications = $request->input('specifications');

            $product = Product::findOrFail($productId);
            
            // 获取产品价格
            $price = $product->selling_price ?? $product->price;
            $subtotal = $price * $quantity;
            
            // 计算税费、配送费和折扣（简化版）
            $tax = 0; 
            $shipping_cost = 0; 
            $discount = 0; 
            $total = $subtotal + $tax + $shipping_cost - $discount;

            // 获取默认仓库/商店ID
            $store = Warehouse::where('status', 1)->first();
            if (!$store) {
                Log::error('CheckoutController.buyNow - 找不到活跃的仓库');
                return response()->json(['status' => 'error', 'message' => '找不到活跃的仓库，无法创建订单'], 500);
            }
            $storeId = $store->id;
            
            // 创建临时订单 - 使用 'pending_checkout' 状态
            $order = SalesOrder::create([
                'customer_id' => $customer->id,
                'store_id' => $storeId, // 添加必要的 store_id 字段
                'order_number' => 'TEMP-' . strtoupper(Str::random(8)),
                'order_date' => now(),
                'status' => 'pending_checkout', // 使用字符串避免 OrderStatusUtil 依赖
                'shipping_status' => 'pending', 
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'shipping_cost' => $shipping_cost,
                'discount_amount' => $discount,
                'total_amount' => $total,
                // 地址将在实际结账过程中添加
                'estimated_arrival_date' => now()->addDays(3), // 添加预计送达日期
            ]);

            // 添加单个商品到订单
            $orderItem = SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name, // 添加产品名称
                'product_sku' => $product->sku, // 添加产品SKU
                'quantity' => $quantity,
                'unit_price' => $price,
                'subtotal' => $subtotal,
                'specifications' => $specifications,
            ]);
            
            // 在这个阶段我们不减少库存，只在最终结账时才减少
            // 库存检查和减少将在 process 方法中处理
            
            DB::commit();
            Log::info('CheckoutController.buyNow - 临时订单创建成功', ['order_id' => $order->id, 'customer_id' => $customer->id]);

            // 返回订单 ID，前端将使用它继续结账流程
            return response()->json([
                'success' => true,
                'message' => '临时订单已创建，准备结账',
                'order_id' => $order->id
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::warning('CheckoutController.buyNow - 产品未找到', ['product_id' => $request->input('product_id'), 'customer_id' => $customer->id]);
            return response()->json(['status' => 'error', 'message' => '产品未找到'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CheckoutController.buyNow - 创建临时订单时发生错误', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $customer->id,
                'request_data' => $request->all()
            ]);
            return response()->json(['status' => 'error', 'message' => '无法启动立即购买流程: ' . $e->getMessage()], 500);
        }
    }
} 