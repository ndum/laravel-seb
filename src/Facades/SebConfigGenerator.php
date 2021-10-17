<?php

/**
 * @copyright (c) the authors
 * @author Nicolas Dumermuth nd@nidum.org (2021-)
 * @license MIT License
 */

namespace Ndum\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class SebConfigGenerator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'SebConfigGenerator';
    }
}
