<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

it('adds fi-collapsed class to collapsed navigation groups', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationGroup::make('Open Group')
                ->items([
                    NavigationItem::make('Item 1')->icon('heroicon-o-home'),
                ]),
            NavigationGroup::make('Closed Group')
                ->collapsed()
                ->items([
                    NavigationItem::make('Item 2')->icon('heroicon-o-cog-6-tooth'),
                ]),
        ])
        ->heading('Test')
        ->renderHtml();

    // The collapsed group's <li> should have fi-collapsed class
    expect($html)->toMatch('/<li[^>]*fi-sidebar-group[^>]*fi-collapsed/');
});

it('does not add fi-collapsed class to expanded groups', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationGroup::make('Open Group')
                ->items([
                    NavigationItem::make('Item 1')->icon('heroicon-o-home'),
                ]),
        ])
        ->heading('Test')
        ->renderHtml();

    // No group <li> should have fi-collapsed
    expect($html)->not->toMatch('/<li[^>]*fi-sidebar-group[^>]*fi-collapsed/');
});

it('hides items of collapsed groups', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationGroup::make('Closed Group')
                ->collapsed()
                ->items([
                    NavigationItem::make('Hidden Item')->icon('heroicon-o-home'),
                ]),
        ])
        ->heading('Test')
        ->renderHtml();

    // The group label should be visible
    expect($html)->toContain('Closed Group');

    // The items should NOT be rendered (isCollapsed hides them)
    expect($html)->not->toContain('Hidden Item');
});

it('shows only expanded group in mixed state', function () {
    $html = FilamentShot::navigation()
        ->items([
            NavigationGroup::make('Expanded')
                ->items([
                    NavigationItem::make('Visible Item')->icon('heroicon-o-home'),
                ]),
            NavigationGroup::make('Collapsed')
                ->collapsed()
                ->items([
                    NavigationItem::make('Hidden Item')->icon('heroicon-o-cog-6-tooth'),
                ]),
        ])
        ->heading('Test')
        ->renderHtml();

    expect($html)
        ->toContain('Visible Item')
        ->not->toContain('Hidden Item')
        ->toContain('Expanded')
        ->toContain('Collapsed');

    // Exactly one group <li> should have fi-collapsed
    preg_match_all('/<li[^>]*fi-sidebar-group[^>]*fi-collapsed/', $html, $matches);
    expect(count($matches[0]))->toBe(1);
});
