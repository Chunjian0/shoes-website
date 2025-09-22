<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthDebugMiddleware
{
    // 免除检查的路由列表
    private $exemptRoutes = [
        'api/products'
    ];

    public function handle(Request $request, Closure $next)
    {
        // 检查当前路径是否在免除列表中
        $path = $request->path();
        if (in_array($path, $this->exemptRoutes)) {
            Log::info("Skipping auth debug for exempt route: {$path}");
            return $next($request);
        }

        Log::info('AuthDebug', [
            'route' => $path,
            'auth_check' => Auth::check(),
            'auth_guard' => Auth::getDefaultDriver(),
            'authenticated_user_id' => Auth::id(),
            'authenticated_user_class' => null,
            'user_name' => null,
            'user_roles' => [],
            'user_permissions' => [],
            'cookies' => array_keys($request->cookies->all()),
            'headers' => $request->headers->all(),
            'authorization_header' => $request->header('Authorization')
        ]);

        if (Auth::check()) {
            $authenticatedUser = Auth::user();
            $logContext = Log::getFacadeRoot()->channel()->getContext();
            $logContext['authenticated_user_class'] = get_class($authenticatedUser);

            if ($authenticatedUser instanceof \App\Models\User) {
                $logContext['user_name'] = $authenticatedUser->name;
                if (method_exists($authenticatedUser, 'getRoleNames')) {
                    $logContext['user_roles'] = $authenticatedUser->getRoleNames()->toArray();
                }
                if (method_exists($authenticatedUser, 'getAllPermissions')) {
                    $logContext['user_permissions'] = $authenticatedUser->getAllPermissions()->pluck('name')->toArray();
                }
            }
            Log::info('AuthDebug - User Details', $logContext);
        }

        return $next($request);
    }
} 