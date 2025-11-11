<?php

namespace Wncms\Translatable;

use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/translatable.php', 'translatable');
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/translatable.php' => config_path('translatable.php'),
            ], 'translatable-config');
        }
    }
}
