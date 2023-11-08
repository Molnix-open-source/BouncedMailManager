<?php

namespace Molnix\BouncedMailManager;

use Illuminate\Support\ServiceProvider;
use Molnix\BouncedMailManager\Console\RunBounceManager;

class BounceManagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'bouncemanager');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'bouncemanager');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/bouncemanager'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../config/bouncemanager.php' => config_path('bouncemanager.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('lang/vendor/bouncemanager'),
        ], 'translations');


        if ($this->app->runningInConsole()) {
            $this->commands([
                RunBounceManager::class,
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/bouncemanager.php',
            'bouncemanager'
        );
    }


}
