<?php

namespace USO\SsoClient;

use Illuminate\Support\ServiceProvider;
use USO\SsoClient\Services\SsoClient;
use Illuminate\Support\Facades\Route;

class SSOServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sso.php', 'sso');
        $this->app->singleton(SsoClient::class, function($app) {
            return new SsoClient($app['config']->get('sso'));
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([ __DIR__.'/../config/sso.php' => config_path('sso.php') ], 'config');
        }

        Route::middleware(['web'])->group(function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        $this->loadViewsFrom(__DIR__.'/resources/views', 'sso');
    }
}
