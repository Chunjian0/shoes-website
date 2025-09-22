<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

class ApiTokenServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 配置内部API请求的默认头部
        $token = env('API_TOKEN', '2|2rGzeCMF0UpwJn6RQ4Jpn0YRnofqPRrcto2fDQ0M0bd35f44');
        
        Http::macro('internal', function () use ($token) {
            return Http::withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-Internal-Request' => 'true'
                ]);
        });
    }
}