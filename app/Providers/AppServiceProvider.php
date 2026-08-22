<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;

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
        // Register additional view paths so that views from the platforma
        // codebase (copied into resources/views/platforma) can be resolved
        // using their original names such as 'offers.create' or 'profile.show'.
        $this->loadViewsFrom(resource_path('views/platforma'), '');
    }
}
