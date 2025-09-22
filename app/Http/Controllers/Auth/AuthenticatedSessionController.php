<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        
        // 更新购物车，将访客购物车关联到已登录用户
        $this->updateGuestCartToUser($request);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * 将访客购物车关联到已登录用户
     *
     * @param Request $request
     * @return void
     */
    protected function updateGuestCartToUser($request)
    {
        try {
            $sessionId = $request->cookie('cart_session_id');
            if ($sessionId) {
                $user = $request->user();
                if ($user) {
                    $customer = \App\Models\Customer::where('email', $user->email)->first();
                    if ($customer) {
                        // 获取访客购物车
                        $guestCarts = \App\Models\Cart::where('session_id', $sessionId)
                            ->where('customer_id', null)
                            ->get();
                            
                        // 获取用户购物车
                        $userCarts = \App\Models\Cart::where('customer_id', $customer->id)->get();
                        
                        foreach ($guestCarts as $guestCart) {
                            // 检查用户是否已有相同类型的购物车
                            $userCart = $userCarts->where('type', $guestCart->type)->first();
                            
                            if ($userCart) {
                                // 如果用户已有相同类型的购物车，则合并商品
                                $guestItems = \App\Models\CartItem::where('cart_id', $guestCart->id)->get();
                                
                                foreach ($guestItems as $guestItem) {
                                    // 检查用户购物车中是否已有相同商品
                                    $userItem = \App\Models\CartItem::where('cart_id', $userCart->id)
                                        ->where('product_id', $guestItem->product_id)
                                        ->where('specifications', $guestItem->specifications)
                                        ->first();
                                        
                                    if ($userItem) {
                                        // 更新数量
                                        $userItem->quantity += $guestItem->quantity;
                                        $userItem->save();
                                        // 删除访客商品
                                        $guestItem->delete();
                                    } else {
                                        // 移动商品到用户购物车
                                        $guestItem->cart_id = $userCart->id;
                                        $guestItem->save();
                                    }
                                }
                                
                                // 删除访客购物车
                                $guestCart->delete();
                            } else {
                                // 如果用户没有相同类型的购物车，则直接关联访客购物车到用户
                                $guestCart->customer_id = $customer->id;
                                $guestCart->save();
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update guest cart to user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
} 