<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            if (config('app.env') === 'local' || config('app.env')  === 'testing') {
                Route::domain(env('FRONTEND_LOCAL_DOMAIN'))
                    ->middleware('api')
                    ->namespace('App\\Http\\Controllers\\App')
                    ->group(base_path('routes/api.php'));

                Route::domain(env('BACKEND_LOCAL_DOMAIN'))
                    ->middleware('api_admin')
                    ->namespace('App\\Http\\Controllers\\Admin')
                    ->group(base_path('routes/api_admin.php'));
            } elseif (config('app.domain') === 'frontend') {
                Route::middleware('api')
                    ->namespace('App\\Http\\Controllers\\App')
                    ->group(base_path('routes/api.php'));
            } elseif (config('app.domain') === 'backend') {
                Route::middleware('api_admin')
                    ->namespace('App\\Http\\Controllers\\Admin')
                    ->group(base_path('routes/api_admin.php'));
            } else {
                Route::middleware('web')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/web.php'));
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(200)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
