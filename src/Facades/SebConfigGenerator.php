<?php

namespace Ndum\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class SebConfigGenerator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'SebConfigGenerator';
    }
}
