<?php

namespace CCK\FilamentShot;

use CCK\FilamentShot\Renderers\FormRenderer;
use CCK\FilamentShot\Renderers\InfolistRenderer;
use CCK\FilamentShot\Renderers\StatsRenderer;
use CCK\FilamentShot\Renderers\TableRenderer;

class FilamentShot
{
    public static function form(array $components): FormRenderer
    {
        return new FormRenderer($components);
    }

    public static function table(): TableRenderer
    {
        return new TableRenderer;
    }

    public static function infolist(array $entries): InfolistRenderer
    {
        return new InfolistRenderer($entries);
    }

    public static function stats(array $stats): StatsRenderer
    {
        return new StatsRenderer($stats);
    }
}
