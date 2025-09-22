<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\QualityInspection;
use App\Policies\PurchasePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\QualityInspectionPolicy;
use Illuminate\Support\Facades\Gate;
use App\Models\Coupon;
use App\Models\CouponTemplate;
use App\Policies\CouponPolicy;
use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Models\Category;
use App\Policies\CategoryPolicy;
use App\Models\Order;
use App\Policies\OrderPolicy;
use App\Models\Customer;
use App\Policies\CustomerPolicy;
use App\Models\ProductCategory;
use App\Policies\ProductCategoryPolicy;
use App\Models\SalesOrder;
use App\Policies\SalesOrderPolicy;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\Warehouse;
use App\Policies\WarehousePolicy;
use App\Models\PurchaseReturn;
use App\Policies\PurchaseReturnPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Purchase::class => PurchasePolicy::class,
        QualityInspection::class => QualityInspectionPolicy::class,
        User::class => UserPolicy::class,
        Warehouse::class => WarehousePolicy::class,
        PurchaseReturn::class => PurchaseReturnPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Purchase Order Permissions
        Gate::define('view_purchases', fn($user) => true);
        Gate::define('create_purchases', fn($user) => true);
        Gate::define('edit_purchases', fn($user) => true);
        Gate::define('delete_purchases', fn($user) => true);
        Gate::define('cancel_purchases', fn($user) => true);
        Gate::define('confirm_purchases', fn($user) => true);
        Gate::define('approve_purchases', fn($user) => true);

        // Payment history permissions
        Gate::define('view_payments', fn($user) => true);
        Gate::define('create_payments', fn($user) => true);
        Gate::define('delete_payments', fn($user) => true);

        // Quality inspection permissions
        Gate::define('view_quality_inspections', fn($user) => $user->can('view quality inspections'));
        Gate::define('create_quality_inspections', fn($user) => $user->can('create quality inspections'));
        Gate::define('edit_quality_inspections', fn($user) => $user->can('edit quality inspections'));
        Gate::define('delete_quality_inspections', fn($user) => $user->can('delete quality inspections'));
        Gate::define('approve_quality_inspections', fn($user) => $user->can('approve quality inspections'));
        Gate::define('reject_quality_inspections', fn($user) => $user->can('reject quality inspections'));
    }
}
