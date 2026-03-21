<?php

namespace App\Providers;
use App\Repositories\InventoryRepository;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $repositories = [
            \App\Repositories\ProductRepositoryInterface::class => \App\Repositories\ProductRepository::class,
            \App\Repositories\InventoryRepositoryInterface::class => \App\Repositories\InventoryRepository::class,
            \App\Repositories\StockMovementRepositoryInterface::class => \App\Repositories\StockMovementRepository::class,
        ];
        foreach ($repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
            $email = urlencode((string) $notifiable->getEmailForPasswordReset());

            return $frontendUrl.'/reset-password?token='.$token.'&email='.$email;
        });
    }
}
