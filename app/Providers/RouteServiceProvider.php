<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            Route::namespace($this->namespace)
                ->group(app_path('Http/Web/routes.php'));


            Route::prefix('api/v1')
                ->namespace($this->namespace)
                ->middleware('api')
                ->group(app_path('Http/ApiV1/routes.php'));
        });
    }
}
