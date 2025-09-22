<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CheckPermission
{
    // 豁免权限检查的路由列表
    private array $exemptRoutes = [
        'api/products'
    ];

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        try {
            // 检查是否是豁免路由
            $path = $request->path();
            if (in_array($path, $this->exemptRoutes)) {
                Log::info('跳过权限检查，豁免路由', ['route' => $path]);
                return $next($request);
            }

            // 检查用户是否已登录
            if (!Auth::check()) {
                Log::warning('Unauthorized access attempt', [
                    'path' => $path,
                    'ip' => $request->ip()
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 401);
            }

            $user = Auth::user();
            // 确保用户对象存在
            if (!$user) {
                Log::error('User authenticated but user object is null', [
                    'route' => $path,
                    'permission' => $permission
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authentication error'
                ], 401);
            }

            // 直接使用 spatie 的方法检查权限
            if (!$user->hasRole('super-admin') && !$user->hasAnyPermission($permission)) {
                Log::error('Unauthorized access', [
                    'route' => $path,
                    'permission' => $permission
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'No operation permission'
                ], 403);
            }
            
            return $next($request);
        } catch (\Exception $e) {
            Log::error('Permission check error', [
                'error' => $e->getMessage(),
                'route' => $request->path(),
                'permission' => $permission
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Authorization error'
            ], 500);
        }
    }
} 