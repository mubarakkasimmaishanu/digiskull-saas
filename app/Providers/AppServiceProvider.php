<?php

namespace App\Providers;

use App\Helpers\Qs;
use App\Listeners\TenantEventSubscriber;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Payment gateway verifiers. Constructor-injected credentials so
        // controllers/services can type-hint the concrete class without
        // reaching into config() and so tests can bind fakes cleanly.
        $this->app->singleton(\App\Services\Payments\PaystackGateway::class, function () {
            return new \App\Services\Payments\PaystackGateway(
                secretKey: (string) config('services.paystack.secret_key', ''),
                verifyUrl: (string) config('services.paystack.verify_url'),
            );
        });

        $this->app->singleton(\App\Services\Payments\FlutterwaveGateway::class, function () {
            return new \App\Services\Payments\FlutterwaveGateway(
                secretKey: (string) config('services.flutterwave.secret_key', ''),
                verifyUrl: (string) config('services.flutterwave.verify_url'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require base_path('routes/channels.php');

        Route::bind('id', fn($value) => Qs::decodeHash($value));

        RateLimiter::for('api', fn(Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        LogViewer::auth(fn($request) => $request->user() && $request->user()->user_type === 'it_guy');

        Event::subscribe(TenantEventSubscriber::class);
        Event::listen(\App\Events\SubscriptionLifecycleEvent::class, \App\Listeners\SendSubscriptionEmailNotification::class);

        Paginator::useBootstrapFour();
    }
}
