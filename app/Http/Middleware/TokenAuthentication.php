<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Customer;

class TokenAuthentication
{
    /**
     * 验证客户端token
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 获取Authorization头部
        $token = $request->bearerToken();
        
        // 如果Authorization头部不存在，从查询参数中获取
        if (!$token) {
            $token = $request->query('token');
        }
        
        // 如果仍然没有找到token，从请求体中获取
        if (!$token && $request->has('token')) {
            $token = $request->input('token');
        }
        
        // 如果没有找到token，返回未授权
        if (!$token) {
            Log::warning('API访问未提供token', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'method' => $request->method()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please login.',
            ], 401);
        }
        
        // 在缓存中查找token
        $tokenData = Cache::get('customer_token_'.$token);
        
        // 如果token不存在或已过期
        if (!$tokenData || now()->gt($tokenData['expires_at'])) {
            Log::warning('无效或过期的token', [
                'token' => substr($token, 0, 10) . '...',
                'ip' => $request->ip()
            ]);
            
            // 清除无效的token
            if ($tokenData) {
                Cache::forget('customer_token_'.$token);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Your session has expired. Please login again.',
            ], 401);
        }
        
        // 查找顾客
        $customer = Customer::find($tokenData['customer_id']);
        
        if (!$customer) {
            Log::error('Token对应的顾客不存在', [
                'customer_id' => $tokenData['customer_id'],
                'token' => substr($token, 0, 10) . '...'
            ]);
            
            // 清除无效的token
            Cache::forget('customer_token_'.$token);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Customer account not found.',
            ], 401);
        }
        
        // 将顾客信息注入到请求中
        $request->merge(['customer' => $customer]);
        $request->attributes->set('customer_id', $customer->id);
        $request->attributes->set('customer_token', $token);
        
        // 记录access log
        Log::info('API令牌验证成功', [
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'path' => $request->path(),
            'method' => $request->method()
        ]);
        
        // 继续请求
        return $next($request);
    }
} 