<?php

namespace SheaXiang\SmsAuth;

use Illuminate\Support\ServiceProvider;

class SmsAuthServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration files
        $this->publishes([
            __DIR__.'/../config/sms-auth.php' => config_path('sms-auth.php')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('SmsAuth', function()
        {
            return new SmsAuth();
        });
    }
}
