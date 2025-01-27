<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use jcobhams\NewsApi\NewsApi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NewsApi::class, function ($app) {
            return new NewsApi(config('services.news_api.key'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
