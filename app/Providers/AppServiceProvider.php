<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('OrderProcessor', function ($app) 
        {
            return new OrderProcessor(
                $app->make('BillerInterface'),
                $app->make('OrderRepository'),
                [
                    $app->make('RecentOrderValidator'),
                    $app->make('OrderOverflowValidator')
                ]
            );
        });
    }

    public function boot()
    {
        // TODO Something
    }
}
