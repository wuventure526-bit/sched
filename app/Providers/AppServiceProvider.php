<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        // Access gates. The administrator clears every gate — it is the
        // system-wide role and may reach anything a unit admin or a borrower
        // can. Use these to decide whether something is *allowed*; use the
        // User::isX() predicates to decide what a user *is*.
        Gate::define('administrator', function ($user) {
            return $user->isAdministrator();
        });

        Gate::define('unitadmin', function ($user) {
            return $user->isUnitAdmin() || $user->isAdministrator();
        });

        Gate::define('borrower', function ($user) {
            return $user->isBorrower() || $user->isAdministrator();
        });

        Gate::define('administratorOrBorrower', function ($user) {
            return $user->isAdministrator() || $user->isBorrower();
        });
    }
}