<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Listeners\SendOrderPaidEmail;
use App\Listeners\SendOrderPendingEmail;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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
        Order::observe(OrderObserver::class);
        Event::listen(OrderCreated::class, SendOrderPendingEmail::class);
        Event::listen(OrderPaid::class, SendOrderPaidEmail::class);
    }
}
