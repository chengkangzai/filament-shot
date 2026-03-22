<?php

namespace CCK\FilamentShot;

use CCK\FilamentShot\Renderers\FormRenderer;
use CCK\FilamentShot\Renderers\HeaderActionsRenderer;
use CCK\FilamentShot\Renderers\InfolistRenderer;
use CCK\FilamentShot\Renderers\ModalRenderer;
use CCK\FilamentShot\Renderers\NavigationRenderer;
use CCK\FilamentShot\Renderers\NotificationRenderer;
use CCK\FilamentShot\Renderers\StatsRenderer;
use CCK\FilamentShot\Renderers\TableRenderer;
use CCK\FilamentShot\Renderers\ViewRenderer;

class FilamentShot
{
    public static function form(array $components): FormRenderer
    {
        return new FormRenderer($components);
    }

    public static function table(array $columns = []): TableRenderer
    {
        return (new TableRenderer)->columns($columns);
    }

    public static function infolist(array $entries): InfolistRenderer
    {
        return new InfolistRenderer($entries);
    }

    public static function stats(array $stats): StatsRenderer
    {
        return new StatsRenderer($stats);
    }

    public static function modal(): ModalRenderer
    {
        return new ModalRenderer;
    }

    public static function notification(): NotificationRenderer
    {
        return new NotificationRenderer;
    }

    public static function navigation(): NavigationRenderer
    {
        return new NavigationRenderer;
    }

    public static function view(string $view): ViewRenderer
    {
        return new ViewRenderer(viewName: $view);
    }

    public static function blade(string $template): ViewRenderer
    {
        return new ViewRenderer(bladeTemplate: $template);
    }

    /**
     * @param  array<\Filament\Actions\Action|\Filament\Actions\ActionGroup>  $actions
     */
    public static function headerActions(array $actions): HeaderActionsRenderer
    {
        return new HeaderActionsRenderer($actions);
    }
}
