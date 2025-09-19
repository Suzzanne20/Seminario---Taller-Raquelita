<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
    }
}
