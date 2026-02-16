<?php

namespace CCK\FilamentShot\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CCK\FilamentShot\FilamentShot
 */
class FilamentShot extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CCK\FilamentShot\FilamentShot::class;
    }
}
