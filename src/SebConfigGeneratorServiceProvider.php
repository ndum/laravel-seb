<?php

/**
 * @copyright (c) the authors
 * @author Nicolas Dumermuth nd@nidum.org (2021-)
 * @license MIT License
 */

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
