<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind payment gateway interface to implementation
        $this->app->bind(
            \App\Services\Payment\PaymentGatewayInterface::class,
            \App\Services\Payment\MercadoPagoGateway::class
        );
        // Register AntiFraudService
        $this->app->singleton(
            \App\Services\Payment\AntiFraudService::class,
            function ($app) {
                return new \App\Services\Payment\AntiFraudService();
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
