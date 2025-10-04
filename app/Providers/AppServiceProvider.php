<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });
        Paginator::useBootstrapFive();
    }
}
