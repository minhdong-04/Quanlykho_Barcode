<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/api.php'));
        if (file_exists(base_path('routes/web.php'))) {
            $this->loadRoutesFrom(base_path('routes/web.php'));
        }
    }
}
