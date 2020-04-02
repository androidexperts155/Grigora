<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Braintree_Configuration::environment('sandbox');
        \Braintree_Configuration::merchantId('5m7rmdnrbqz3vhq5');
        \Braintree_Configuration::publicKey('zg6q6d2d65gzb2jp');
        \Braintree_Configuration::privateKey('5875bff2a36152909b6cdc3b75af00e1');
    }
}
