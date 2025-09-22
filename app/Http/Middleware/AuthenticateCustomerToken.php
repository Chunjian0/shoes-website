<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class AuthenticateCustomerToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /* // Comment out the custom cache-based logic
        $token = $request->bearerToken();
        $customer = null;

        if ($token) {
            // 尝试从缓存获取token数据
            $cacheKey = 'customer_token_' . $token;
            $tokenData = Cache::get($cacheKey);

            if ($tokenData && isset($tokenData['customer_id'])) {
                // 检查token是否过期
                $expiresAt = $tokenData['expires_at'] ?? null;
                if ($expiresAt && now()->gt($expiresAt)) {
                    Log::info('Customer token expired', ['token' => substr($token, 0, 10).'...', 'cache_key' => $cacheKey]);
                    Cache::forget($cacheKey); // 删除过期token
                } else {
                    $customerId = $tokenData['customer_id'];
                    $customer = Customer::find($customerId);
                    
                    if ($customer) {
                        // 将认证的customer对象附加到请求属性
                        $request->attributes->set('customer', $customer);
                        // 也可以附加customer_id方便使用
                        $request->attributes->set('customer_id', $customer->id);
                        Log::debug('Customer authenticated via token', ['customer_id' => $customer->id, 'token' => substr($token, 0, 10).'...']);
                        
                        // 可选：更新最后访问时间
                        // $customer->last_visit_at = now();
                        // $customer->save();
                    } else {
                        Log::warning('Customer not found for valid token', ['customer_id' => $customerId, 'token' => substr($token, 0, 10).'...']);
                        Cache::forget($cacheKey); // 如果找不到用户，也删除token缓存
                    }
                }
            } else {
                Log::debug('Invalid or missing token in cache', ['token' => substr($token, 0, 10).'...', 'cache_key' => $cacheKey]);
            }
        } else {
             Log::debug('No bearer token found in request');
        }
        */

        // Always proceed to the next middleware/controller
        // Sanctum's middleware (via auth:customer guard) will handle the actual authentication
        return $next($request);
    }
}
