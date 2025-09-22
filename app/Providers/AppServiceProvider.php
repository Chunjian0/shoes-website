<?php

namespace App\Providers;

use App\Http\Controllers\CartController;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\ProductTemplate;
use App\Observers\ProductTemplateObserver;
use App\Models\Product;
use App\Observers\ProductObserver;
use App\Models\Banner;
use App\Observers\BannerObserver;
use App\Models\Setting;
use App\Observers\SettingObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 添加多态映射关系
        Relation::morphMap([
            'products' => 'App\\Models\\Product',
            'App\\Models\\User' => 'App\\Models\\User',  // 添加User模型的映射
            'quality_inspections' => 'App\\Models\\QualityInspection', // 添加QualityInspection模型的映射
        ]);

        // 共享购物车数量到所有视图
        View::composer('*', function ($view) {
            try {
                $cartController = app(CartController::class);
                $cartCount = $cartController->getCartCount()->getData()->count;
                $view->with('cartCount', $cartCount);
            } catch (\Exception $e) {
                $view->with('cartCount', 0);
            }
        });

        // 共享仓库（店铺）数据到所有视图
        try {
            if (Schema::hasTable('warehouses')) {
                View::share('stores', \App\Models\Warehouse::where('is_store', true)->where('status', true)->get());
            }
        } catch (\Exception $e) {
            View::share('stores', collect([]));
        }
        
        // 创建自定义校验规则
        Validator::extend('unique_composite', function($attribute, $value, $parameters, $validator) {
            // 获取校验数据
            $data = $validator->getData();
            
            // 构建查询
            $query = DB::table($parameters[0]);
            
            // 添加条件
            $query->where($parameters[1], $value);
            
            for($i = 2; $i < count($parameters) - 1; $i += 2) {
                $fieldName = $parameters[$i];
                $paramName = $parameters[$i + 1];
                $query->where($fieldName, $data[$paramName] ?? null);
            }
            
            // 如果是编辑操作，需要排除当前记录
            $lastParameter = end($parameters);
            if(isset($data[$lastParameter])) {
                $query->where('id', '!=', $data[$lastParameter]);
            }
            
            // 执行查询并返回结果
            return $query->count() === 0;
        });
        
        // 添加自定义校验规则消息
        Validator::replacer('unique_composite', function($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The :attribute has already been taken for this combination.');
        });
        
        // 确保HTTPS链接在生产环境中正确生成
        if (config('app.env') === 'production') {
            URL::forceScheme('http');
        }

        ProductTemplate::observe(ProductTemplateObserver::class);
        Product::observe(ProductObserver::class); 
        Banner::observe(BannerObserver::class);
        Setting::observe(SettingObserver::class);
    }
}
