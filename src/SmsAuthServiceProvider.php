<?php

namespace Sheaxiang\SmsAuth;

use Illuminate\Support\ServiceProvider;
use Sheaxiang\SmsAuth\Facades\SmsAuth;

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
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configs
        $this->mergeConfigFrom(
            __DIR__.'/../config/sms-auth.php', 'sms-auth'
        );

        $this->app->singleton('sms-auth', function ($app) {
            return new SmsAuth($app['request']->server->all());
        });
    }
}
