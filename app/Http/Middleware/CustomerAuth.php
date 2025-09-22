<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CustomerAuth
{
    /**
     * 处理顾客认证请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查session中是否有顾客ID和会话标识
        if (!session('customer_id') || !session('customer_session')) {
            Log::warning('Unauthorized customer access attempt', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'session_data' => session()->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Please login.',
            ], 401);
        }
        
        // 顾客已认证，继续请求
        return $next($request);
    }
} 