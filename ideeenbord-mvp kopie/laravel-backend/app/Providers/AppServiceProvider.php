<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\BrandOwner;
use App\Observers\BrandOwnerObserver;

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
        BrandOwner::observe(BrandOwnerObserver::class);
    }
}
