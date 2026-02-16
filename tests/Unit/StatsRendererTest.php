<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Widgets\StatsOverviewWidget\Stat;

it('renders stats with labels and values', function () {
    $html = FilamentShot::stats([
        Stat::make('Total Users', '1,234'),
        Stat::make('Revenue', '$56,789'),
    ])->toHtml();

    expect($html)
        ->toContain('Total Users')
        ->toContain('1,234')
        ->toContain('Revenue')
        ->toContain('$56,789')
        ->toContain('fi-wi-stats-overview');
});

it('renders stat with description', function () {
    $html = FilamentShot::stats([
        Stat::make('Users', '100')
            ->description('10% increase'),
    ])->toHtml();

    expect($html)
        ->toContain('10% increase')
        ->toContain('fi-wi-stats-overview-stat-description');
});

it('renders stat with color', function () {
    $html = FilamentShot::stats([
        Stat::make('Revenue', '$1,000')
            ->color('success'),
    ])->toHtml();

    expect($html)
        ->toContain('Revenue')
        ->toContain('$1,000');
});

it('renders multiple stats in a grid', function () {
    $html = FilamentShot::stats([
        Stat::make('A', '1'),
        Stat::make('B', '2'),
        Stat::make('C', '3'),
    ])->toHtml();

    expect($html)
        ->toContain('grid-template-columns: repeat(3')
        ->toContain('fi-wi-stats-overview-stat');
});
