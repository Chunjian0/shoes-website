<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 用户登录API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // 创建日志文件
        $debugLogFile = storage_path('logs/auth-debug.log');
        file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - AuthController.login method called\n", FILE_APPEND);
        file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Request data: " . json_encode($request->all()) . "\n", FILE_APPEND);
        
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            // 检查Customer表
            $customer = Customer::where('email', $request->email)->first();
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Checking customer: " . ($customer ? "Found ID: {$customer->id}" : "Not found") . "\n", FILE_APPEND);

            if (!$customer || !Hash::check($request->password, $customer->customer_password)) {
                file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Invalid credentials\n", FILE_APPEND);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid login credentials',
                ], 401);
            }

            // 客户登录成功
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Customer password check: success\n", FILE_APPEND);
            
            // 由于sanctum需要User对象来创建token，我们需要获取或创建关联的用户
            $user = User::where('email', $customer->email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'password' => $customer->customer_password,
                    'employee_id' => 'CUST-' . $customer->id,
                    'is_active' => true
                ]);
                file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Created new user for customer: {$user->id}\n", FILE_APPEND);
            }
            
            // 创建token
            $token = $user->createToken('auth_token')->plainTextToken;
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Token created\n", FILE_APPEND);
            
            // 更新客户最后登录时间
            $customer->last_login_ip = $request->ip();
            $customer->last_visit_at = now();
            $customer->save();
            
            // 获取cookie中的购物车会话ID
            $sessionId = $request->cookie('cart_session_id');
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Session ID from cookie: " . ($sessionId ?: "Not found") . "\n", FILE_APPEND);

            // 如果找到会话ID，更新购物车信息
            if ($sessionId) {
                $this->updateGuestCartToUser($sessionId, $customer->id, $debugLogFile);
            }

            // 返回Customer对象，而不是User对象
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'contact_number' => $customer->contact_number,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Login error: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);

            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 将访客购物车关联到用户
     *
     * @param string $sessionId 会话ID
     * @param int $customerId 客户ID
     * @param string $debugLogFile 调试日志文件路径
     * @return void
     */
    protected function updateGuestCartToUser($sessionId, $customerId, $debugLogFile)
    {
        try {
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Updating guest cart for session: $sessionId to customer: $customerId\n", FILE_APPEND);
            
            // 获取访客购物车 - 使用session_id查找未关联到任何客户的购物车
            $guestCarts = Cart::where('session_id', $sessionId)
                ->where('customer_id', null)
                ->get();
                
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Found " . $guestCarts->count() . " guest carts\n", FILE_APPEND);
                
            // 获取用户购物车 - 使用customer_id查找
            $userCarts = Cart::where('customer_id', $customerId)->get();
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Found " . $userCarts->count() . " user carts\n", FILE_APPEND);
            
            foreach ($guestCarts as $guestCart) {
                // 检查用户是否已有相同类型的购物车
                $userCart = $userCarts->where('type', $guestCart->type)->first();
                file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Processing guest cart #" . $guestCart->id . " of type " . $guestCart->type . "\n", FILE_APPEND);
                
                if ($userCart) {
                    file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Found matching user cart #" . $userCart->id . "\n", FILE_APPEND);
                    
                    // 如果用户已有相同类型的购物车，则合并商品
                    $guestItems = CartItem::where('cart_id', $guestCart->id)->get();
                    file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Found " . $guestItems->count() . " items in guest cart\n", FILE_APPEND);
                    
                    foreach ($guestItems as $guestItem) {
                        // 检查用户购物车中是否已有相同商品
                        $userItem = CartItem::where('cart_id', $userCart->id)
                            ->where('product_id', $guestItem->product_id)
                            ->get()
                            ->filter(function ($item) use ($guestItem) {
                                return json_encode($item->specifications) === json_encode($guestItem->specifications);
                            })
                            ->first();
                            
                        if ($userItem) {
                            // 更新数量
                            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Updating quantity for user cart item #" . $userItem->id . " from " . $userItem->quantity . " to " . ($userItem->quantity + $guestItem->quantity) . "\n", FILE_APPEND);
                            $userItem->quantity += $guestItem->quantity;
                            $userItem->save();
                            // 删除访客商品
                            $guestItem->delete();
                        } else {
                            // 移动商品到用户购物车
                            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Moving guest cart item #" . $guestItem->id . " to user cart #" . $userCart->id . "\n", FILE_APPEND);
                            $guestItem->cart_id = $userCart->id;
                            $guestItem->save();
                        }
                    }
                    
                    // 删除访客购物车
                    file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Deleting guest cart #" . $guestCart->id . "\n", FILE_APPEND);
                    $guestCart->delete();
                } else {
                    // 如果用户没有相同类型的购物车，则直接关联访客购物车到用户
                    file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Associating guest cart #" . $guestCart->id . " with customer #" . $customerId . "\n", FILE_APPEND);
                    $guestCart->customer_id = $customerId;
                    
                    // 保留session_id，但更新customer_id
                    // 这样可以确保两种方式都能找到购物车
                    $guestCart->save();
                }
            }
            
            // 额外的保险措施：确保所有现有的购物车项目都可以通过customer_id访问
            // 这一步可能会在很短的时间内减少403错误
            $customerCarts = Cart::where('customer_id', $customerId)->pluck('id')->toArray();
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Found " . count($customerCarts) . " customer carts after merging\n", FILE_APPEND);
            
            if (!empty($customerCarts)) {
                // 查找可能遗漏的购物车项目
                $orphanedItems = CartItem::whereIn('cart_id', $customerCarts)
                    ->whereHas('cart', function($q) use ($customerId) {
                        $q->where('customer_id', '!=', $customerId)->orWhereNull('customer_id');
                    })
                    ->get();
                
                if ($orphanedItems->count() > 0) {
                    file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Found " . $orphanedItems->count() . " orphaned cart items to fix\n", FILE_APPEND);
                    
                    foreach ($orphanedItems as $item) {
                        $cart = Cart::find($item->cart_id);
                        if ($cart) {
                            $cart->customer_id = $customerId;
                            $cart->save();
                            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Fixed orphaned cart #" . $cart->id . "\n", FILE_APPEND);
                        }
                    }
                }
            }
            
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Cart update completed successfully\n", FILE_APPEND);
        } catch (\Exception $e) {
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Error updating cart: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents($debugLogFile, date('Y-m-d H:i:s') . " - Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
        }
    }

    /**
     * 用户登出API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // 删除当前token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }
} 