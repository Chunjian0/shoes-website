<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Coupon;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Payment;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * 显示结账页面
     */
    public function index()
    {
        // 获取当前用户的购物车
        $cart = $this->getCart();
        
        // 如果购物车为空，重定向回购物车页面
        if ($cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }
        
        // 获取客户列表（用于选择客户）
        $customers = Customer::orderBy('name')->get();
        
        // 获取可用的优惠券
        $availableCoupons = Coupon::where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();
        
        return view('checkout.index', compact('cart', 'customers', 'availableCoupons'));
    }
    
    /**
     * 确认订单信息
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:cash,bank_transfer,check,credit_card',
            'coupon_code' => 'nullable|exists:coupons,code',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // 获取当前用户的购物车
        $cart = $this->getCart();
        
        // 如果购物车为空，重定向回购物车页面
        if ($cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }
        
        // 获取客户信息
        $customer = Customer::findOrFail($request->customer_id);
        
        // 处理优惠券
        $coupon = null;
        $discount = 0;
        
        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();
                
            if ($coupon) {
                // 计算折扣金额
                if ($coupon->discount_type === 'percentage') {
                    $discount = $cart->total() * ($coupon->discount_value / 100);
                } else {
                    $discount = min($coupon->discount_value, $cart->total());
                }
            }
        }
        
        // 计算订单总额
        $subtotal = $cart->total();
        $total = $subtotal - $discount;
        
        // 存储结账信息到会话
        Session::put('checkout', [
            'customer_id' => $customer->id,
            'payment_method' => $request->payment_method,
            'coupon_id' => $coupon ? $coupon->id : null,
            'discount' => $discount,
            'subtotal' => $subtotal,
            'total' => $total,
            'notes' => $request->notes,
        ]);
        
        return view('checkout.confirm', compact('cart', 'customer', 'coupon', 'discount', 'subtotal', 'total'));
    }
    
    /**
     * 处理支付并创建订单
     */
    public function processPayment(Request $request)
    {
        // 获取结账信息
        $checkout = Session::get('checkout');
        
        if (!$checkout) {
            return redirect()->route('checkout.index')->with('error', 'Checkout information not found');
        }
        
        // 获取当前用户的购物车
        $cart = $this->getCart();
        
        // 如果购物车为空，重定向回购物车页面
        if ($cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }
        
        // 开始数据库事务
        DB::beginTransaction();
        
        try {
            // 创建销售订单
            $order = new SalesOrder();
            $order->order_number = 'SO-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            $order->customer_id = $checkout['customer_id'];
            $order->user_id = Auth::id();
            $order->store_id = $cart->store_id;
            $order->status = 'pending';
            $order->payment_status = 'pending';
            $order->subtotal = $checkout['subtotal'];
            $order->discount = $checkout['discount'];
            $order->total = $checkout['total'];
            $order->notes = $checkout['notes'];
            $order->coupon_id = $checkout['coupon_id'];
            $order->save();
            
            // 创建订单项
            foreach ($cart->items as $item) {
                // 检查库存
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $cart->store_id)
                    ->first();
                
                if (!$stock || $stock->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for product: {$item->product->name}");
                }
                
                // 创建订单项
                $orderItem = new SalesOrderItem();
                $orderItem->sales_order_id = $order->id;
                $orderItem->product_id = $item->product_id;
                $orderItem->quantity = $item->quantity;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price * $item->quantity;
                $orderItem->specifications = $item->specifications;
                $orderItem->save();
                
                // 减少库存
                $stock->quantity -= $item->quantity;
                $stock->save();
                
                // 记录库存移动
                $stockMovement = new StockMovement();
                $stockMovement->product_id = $item->product_id;
                $stockMovement->warehouse_id = $cart->store_id;
                $stockMovement->quantity = -$item->quantity;
                $stockMovement->type = 'sale';
                $stockMovement->reference_id = $order->id;
                $stockMovement->reference_type = SalesOrder::class;
                $stockMovement->notes = "Sale order: {$order->order_number}";
                $stockMovement->save();
            }
            
            // 创建支付记录
            $payment = new Payment();
            $payment->payment_number = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            $payment->payable_id = $order->id;
            $payment->payable_type = SalesOrder::class;
            $payment->amount = $checkout['total'];
            $payment->payment_method = $checkout['payment_method'];
            $payment->status = 'completed';
            $payment->payment_date = now();
            $payment->notes = "Payment for order: {$order->order_number}";
            $payment->save();
            
            // 更新订单支付状态
            $order->payment_status = 'paid';
            $order->status = 'processing';
            $order->save();
            
            // 如果使用了优惠券，标记为已使用
            if ($checkout['coupon_id']) {
                $coupon = Coupon::find($checkout['coupon_id']);
                if ($coupon) {
                    $coupon->used_count += 1;
                    $coupon->save();
                }
            }
            
            // 清空购物车
            $cart->items()->delete();
            
            // 提交事务
            DB::commit();
            
            // 清除结账会话
            Session::forget('checkout');
            
            // 存储订单ID到会话，用于完成页面
            Session::put('completed_order_id', $order->id);
            
            return redirect()->route('checkout.complete');
            
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            
            return redirect()->route('checkout.index')->with('error', 'Error processing order: ' . $e->getMessage());
        }
    }
    
    /**
     * 显示订单完成页面
     */
    public function complete()
    {
        $orderId = Session::get('completed_order_id');
        
        if (!$orderId) {
            return redirect()->route('cart.index');
        }
        
        $order = SalesOrder::with(['items.product', 'customer', 'payment'])->findOrFail($orderId);
        
        // 清除会话中的订单ID
        Session::forget('completed_order_id');
        
        return view('checkout.complete', compact('order'));
    }
    
    /**
     * 应用优惠券
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|exists:coupons,code',
        ]);
        
        $coupon = Coupon::where('code', $request->coupon_code)
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
            
        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired coupon code'
            ]);
        }
        
        // 获取当前用户的购物车
        $cart = $this->getCart();
        
        // 计算折扣金额
        $discount = 0;
        if ($coupon->discount_type === 'percentage') {
            $discount = $cart->total() * ($coupon->discount_value / 100);
        } else {
            $discount = min($coupon->discount_value, $cart->total());
        }
        
        // 计算应付金额
        $total = $cart->total() - $discount;
        
        return response()->json([
            'success' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'total' => $total,
            'message' => 'Coupon applied successfully'
        ]);
    }
    
    /**
     * 获取当前用户的购物车
     */
    protected function getCart()
    {
        $cartController = app(CartController::class);
        // 由于CartController中的getCart是protected方法，不能直接调用
        // 我们需要使用反射来调用它，或者直接复制其实现
        
        // 获取默认商店
        $store = \App\Models\Warehouse::where('is_store', true)->where('is_default', true)->first();
        if (!$store) {
            $store = \App\Models\Warehouse::where('is_store', true)->first();
        }
        
        if (!$store) {
            abort(500, 'No store found in the system');
        }

        // 检查是否有customer_id参数
        $customer_id = request('customer_id');
        
        // 如果提供了customer_id，获取或创建该客户的购物车
        if ($customer_id) {
            $cart = \App\Models\Cart::firstOrCreate(
                ['customer_id' => $customer_id, 'store_id' => $store->id],
                []
            );
            return $cart;
        }

        if (Auth::check()) {
            // 用户已登录，获取或创建他们的购物车
            $cart = \App\Models\Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['store_id' => $store->id]
            );
        } else {
            // 用户未登录，使用会话ID
            $sessionId = Session::getId();
            $cart = \App\Models\Cart::firstOrCreate(
                ['session_id' => $sessionId],
                ['store_id' => $store->id]
            );
        }

        return $cart;
    }
} 