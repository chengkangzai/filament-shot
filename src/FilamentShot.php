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
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;

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

    /**
     * @param  array<Component>  $components  Optional body components rendered inside the modal
     */
    public static function modal(array $components = []): ModalRenderer
    {
        return new ModalRenderer($components);
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
     * @param  array<Action|ActionGroup>  $actions
     */
    public static function headerActions(array $actions): HeaderActionsRenderer
    {
        return new HeaderActionsRenderer($actions);
    }
}
