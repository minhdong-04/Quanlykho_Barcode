<?php

namespace Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Foundation\Bootstrap\RegisterFacades;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Bootstrap\BootProviders;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        // Sau khi bootstrap xong, override cấu hình Sanctum cho môi trường test
        $app['config']->set('sanctum.guard', env('SANCTUM_GUARD', 'api'));

        // Đảm bảo có binding `session.store` trong môi trường test để tránh lỗi
        if (! $app->bound('session.store')) {
            $handler = new \Illuminate\Session\ArraySessionHandler(120);
            $store = new \Illuminate\Session\Store('testing', $handler);
            $app->instance('session.store', $store);
        }

        return $app;
    }
}
