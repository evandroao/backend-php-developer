<?php

namespace App\Providers;

use App\Integration\Client\AbstractRequest;
use App\Integration\Client\RequestService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AbstractRequest::class, function () {
            return new AbstractRequest();
        });

        $this->app->singleton(RequestService::class, function ($app) {
            return new RequestService($app->make(AbstractRequest::class));
        });
    }

    public function boot(): void
    {
        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('sync', function ($request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
