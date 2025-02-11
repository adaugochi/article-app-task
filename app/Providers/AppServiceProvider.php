<?php

namespace App\Providers;

use App\Repositories\ArticleRepository;
use App\Services\ArticleService;
use App\Services\GuardianApiService;
use App\Services\NewsApiService;
use App\Services\NewYorkTimeApiService;
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

        $this->app->singleton(ArticleService::class, function ($app) {
            $articleServices = [
                $app->make(GuardianApiService::class),
                $app->make(NewYorkTimeApiService::class),
                $app->make(NewsApiService::class),
            ];
            return new ArticleService(
                $app->make(ArticleRepository::class),
                ...$articleServices
            );
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
