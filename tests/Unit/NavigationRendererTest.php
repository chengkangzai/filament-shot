<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

it('renders navigation with groups and items', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationGroup::make('Content')
                ->items([
                    NavigationItem::make('Posts')
                        ->icon('heroicon-o-document-text'),
                    NavigationItem::make('Categories')
                        ->icon('heroicon-o-tag'),
                ]),
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-sidebar')
        ->toContain('fi-sidebar-group')
        ->toContain('fi-sidebar-group-label')
        ->toContain('Content')
        ->toContain('fi-sidebar-item')
        ->toContain('Posts')
        ->toContain('Categories');
});

it('renders navigation with heading', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationGroup::make('Main')
                ->items([
                    NavigationItem::make('Dashboard')
                        ->icon('heroicon-o-home'),
                ]),
        ])
        ->heading('My App')
        ->toHtml();

    expect($html)
        ->toContain('My App')
        ->toContain('fi-sidebar-header');
});

it('renders active navigation item', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationGroup::make('Main')
                ->items([
                    NavigationItem::make('Dashboard')
                        ->icon('heroicon-o-home')
                        ->isActiveWhen(fn () => true),
                    NavigationItem::make('Settings')
                        ->icon('heroicon-o-cog-6-tooth'),
                ]),
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-active')
        ->toContain('Dashboard')
        ->toContain('Settings');
});

it('renders navigation item with badge', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationGroup::make('Main')
                ->items([
                    NavigationItem::make('Orders')
                        ->icon('heroicon-o-shopping-bag')
                        ->badge('12', 'danger'),
                ]),
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-badge')
        ->toContain('12')
        ->toContain('fi-color-danger');
});

it('renders multiple navigation groups', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationGroup::make('Content')
                ->items([
                    NavigationItem::make('Posts')
                        ->icon('heroicon-o-document-text'),
                ]),
            NavigationGroup::make('Settings')
                ->items([
                    NavigationItem::make('General')
                        ->icon('heroicon-o-cog-6-tooth'),
                ]),
        ])
        ->toHtml();

    expect($html)
        ->toContain('Content')
        ->toContain('Settings')
        ->toContain('Posts')
        ->toContain('General');
});

it('renders ungrouped navigation items', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationItem::make('Dashboard')
                ->icon('heroicon-o-home'),
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-sidebar-item')
        ->toContain('Dashboard');
});

it('renders navigation from array definitions', function () {
    $html = FilamentShot::navigation()
        ->items([
            [
                'label' => 'Main',
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'heroicon-o-home', 'isActive' => true],
                    ['label' => 'Users', 'icon' => 'heroicon-o-users'],
                ],
            ],
        ])
        ->toHtml();

    expect($html)
        ->toContain('Main')
        ->toContain('Dashboard')
        ->toContain('Users')
        ->toContain('fi-active');
});

it('renders grouped items without icons with border indicators', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationGroup::make('Blog')
                ->items([
                    NavigationItem::make('All Posts'),
                    NavigationItem::make('Drafts'),
                ]),
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-sidebar-item-grouped-border')
        ->toContain('All Posts')
        ->toContain('Drafts');
});
