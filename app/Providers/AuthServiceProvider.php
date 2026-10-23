<?php

namespace App\Providers;

use App\Models\UserSubMenu;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Gate::define('submenu-permission', function ($user, int $submenucode) {
            return UserSubMenu::where('userpin', $user->id)
                ->where('submenucode', $submenucode)
                ->exists();
        });

        Gate::define('billing-access', function ($user) {
            return in_array((int) ($user->accounttype ?? 0), [1, 99]);
        });

        Gate::define('billing-delete', function ($user) {
            return (int) ($user->accounttype ?? 0) === 99;
        });

        Gate::define('manager-collection', function ($user) {
            return in_array(
                (int) ($user->accounttype ?? 0),
                config('billing.manager_accounttypes', [1, 99])
            );
        });

        Gate::define('receipt-collection', function ($user) {
            return in_array(
                (int) ($user->accounttype ?? 0),
                config('billing.receipt_accounttypes', [1, 99])
            );
        });
    }
}
