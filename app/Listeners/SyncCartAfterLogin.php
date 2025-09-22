<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class SyncCartAfterLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Authenticated $event): void
    {
        $user = $event->user;
        
        // 只处理普通用户登录，不处理API登录
        if (request()->is('api/*')) {
            return;
        }
        
        try {
            // 查找对应的客户
            $customer = Customer::where('email', $user->email)->first();
            if (!$customer) {
                Log::info('用户登录但没有找到对应的客户记录', ['user_id' => $user->id, 'email' => $user->email]);
                return;
            }
            
            Log::info('用户登录，开始同步购物车', ['user_id' => $user->id, 'customer_id' => $customer->id]);
            
            // 获取会话ID
            $sessionId = session()->getId();
            $cookieSessionId = Cookie::get('cart_session_id');
            
            if ($cookieSessionId) {
                $sessionId = $cookieSessionId;
            }
            
            if (!$sessionId) {
                Log::info('用户登录但没有会话ID', ['user_id' => $user->id]);
                return;
            }
            
            Log::info('用户登录会话信息', [
                'user_id' => $user->id, 
                'session_id' => $sessionId, 
                'cookie_session_id' => $cookieSessionId
            ]);
            
            // 获取访客购物车 - 使用session_id查找未关联到任何客户的购物车
            $guestCarts = Cart::where('session_id', $sessionId)
                ->whereNull('customer_id')
                ->get();
                
            if ($guestCarts->isEmpty()) {
                Log::info('没有找到需要关联的访客购物车', ['session_id' => $sessionId]);
                
                // 确保客户有默认购物车
                $hasCart = Cart::where('customer_id', $customer->id)->exists();
                if (!$hasCart) {
                    Log::info('为客户创建默认购物车', ['customer_id' => $customer->id]);
                    
                    $cart = new Cart();
                    $cart->customer_id = $customer->id;
                    $cart->type = 'default';
                    $cart->is_default = true;
                    $cart->name = '默认购物车';
                    $cart->save();
                }
                
                return;
            }
                
            Log::info('找到访客购物车', ['session_id' => $sessionId, 'count' => $guestCarts->count()]);
                
            // 获取用户购物车 - 使用customer_id查找
            $userCarts = Cart::where('customer_id', $customer->id)->get();
            Log::info('找到客户购物车', ['customer_id' => $customer->id, 'count' => $userCarts->count()]);
            
            // 处理购物车合并
            foreach ($guestCarts as $guestCart) {
                // 检查用户是否已有相同类型的购物车
                $userCart = $userCarts->where('type', $guestCart->type)->first();
                Log::info('处理访客购物车', [
                    'guest_cart_id' => $guestCart->id, 
                    'type' => $guestCart->type,
                    'matched_user_cart' => $userCart ? $userCart->id : null
                ]);
                
                if ($userCart) {
                    // 如果用户已有相同类型的购物车，则合并商品
                    $guestItems = CartItem::where('cart_id', $guestCart->id)->get();
                    Log::info('合并购物车项目', [
                        'guest_cart_id' => $guestCart->id,
                        'user_cart_id' => $userCart->id,
                        'items_count' => $guestItems->count()
                    ]);
                    
                    foreach ($guestItems as $guestItem) {
                        // 检查用户购物车中是否已有相同商品
                        $userItem = CartItem::where('cart_id', $userCart->id)
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
                            
                        if ($userItem) {
                            // 更新数量
                            Log::info('合并购物车项目数量', [
                                'user_item_id' => $userItem->id,
                                'guest_item_id' => $guestItem->id,
                                'old_quantity' => $userItem->quantity,
                                'add_quantity' => $guestItem->quantity
                            ]);
                            
                            $userItem->quantity += $guestItem->quantity;
                            $userItem->save();
                            
                            // 删除访客商品
                            $guestItem->delete();
                        } else {
                            // 移动商品到用户购物车
                            Log::info('移动购物车项目', [
                                'item_id' => $guestItem->id,
                                'from_cart_id' => $guestCart->id,
                                'to_cart_id' => $userCart->id
                            ]);
                            
                            $guestItem->cart_id = $userCart->id;
                            $guestItem->save();
                        }
                    }
                    
                    // 删除访客购物车
                    Log::info('删除访客购物车', ['cart_id' => $guestCart->id]);
                    $guestCart->delete();
                } else {
                    // 如果用户没有相同类型的购物车，则直接关联访客购物车到用户
                    Log::info('关联访客购物车到客户', [
                        'cart_id' => $guestCart->id,
                        'customer_id' => $customer->id
                    ]);
                    
                    $guestCart->customer_id = $customer->id;
                    // 保留session_id，确保两种方式都能找到购物车
                    $guestCart->save();
                }
            }
            
            Log::info('购物车同步完成', ['customer_id' => $customer->id]);
        } catch (\Exception $e) {
            Log::error('购物车同步失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
