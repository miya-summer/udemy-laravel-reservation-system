<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::define('admin', function($user){
            return $user->role === 1;
        });
        Gate::define('manager-higher', function($user){
            return $user->role > 0 && $user->role <= 5;
        });
        Gate::define('user-higher', function($user){
            return $user->role > 0 && $user->role <= 9;
        });
    }
}
