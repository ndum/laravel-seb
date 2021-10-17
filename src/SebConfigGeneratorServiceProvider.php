<?php

namespace Ndum\Laravel;

use Illuminate\Support\ServiceProvider;

class SebConfigGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('SebConfigGenerator', function ($app) {
            return new SebConfigGenerator();
        });
    }
}
