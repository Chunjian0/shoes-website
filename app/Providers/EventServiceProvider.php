<?php

namespace App\Providers;

use App\Events\PaymentStatusChanged;
use App\Events\PurchaseStatusChanged;
use App\Events\QualityInspectionCreated;
use App\Events\QualityInspectionStatusChanged;
use App\Events\StockChangedEvent;
use App\Listeners\HandlePaymentStatusChanged;
use App\Listeners\HandlePurchaseStatusChanged;
use App\Listeners\HandleQualityInspectionCreated;
use App\Listeners\HandleQualityInspectionStatusChanged;
use App\Listeners\HomepageStockListener;
use App\Listeners\LogSentMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Mail\Events\MessageSent;
use App\Events\HomepageUpdatedEvent;
use App\Listeners\SendHomepageUpdateNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PurchaseStatusChanged::class => [
            HandlePurchaseStatusChanged::class,
        ],
        PaymentStatusChanged::class => [
            HandlePaymentStatusChanged::class,
        ],
        QualityInspectionCreated::class => [
            HandleQualityInspectionCreated::class,
        ],
        QualityInspectionStatusChanged::class => [
            HandleQualityInspectionStatusChanged::class,
        ],
        \App\Events\PurchaseStatusChanged::class => [
            \App\Listeners\PurchaseStatusChangedListener::class,
        ],
        // Add listener for mail sent events to log them in notification_logs table
        MessageSent::class => [
            LogSentMail::class,
        ],
        // 库存变动事件监听器，用于处理首页低库存产品自动移除
        StockChangedEvent::class => [
            HomepageStockListener::class,
        ],
        HomepageUpdatedEvent::class => [
            SendHomepageUpdateNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Coupon::observe(CouponObserver::class); // Removed this line
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
