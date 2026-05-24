<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Get the redirect path based on user role.
     *
     * @return string
     */
    public static function redirectPath()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('admin')) {
                return '/admin/dashboard';
            } elseif ($user->hasRole('store')) {
                return '/store/manage';
            } elseif ($user->hasRole('kitchen')) {
                return '/kitchen/dashboard';
            } elseif ($user->hasRole('waiter')) {
                return '/waiter/dashboard';
            } else {
                return '/home';
            }
        }
        return '/login';
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
