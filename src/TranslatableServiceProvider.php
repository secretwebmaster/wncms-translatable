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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/translatable.php' => config_path('translatable.php'),
                __DIR__ . '/../migrations/' => database_path('migrations'),
            ], 'translatable-migrations');
        }
    }
}
