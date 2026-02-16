<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Widgets\StatsOverviewWidget\Stat;

it('saves stats screenshot as PNG', function () {
    $path = sys_get_temp_dir() . '/filament-shot-stats-test.png';

    FilamentShot::stats([
        Stat::make('Users', '1,234'),
        Stat::make('Revenue', '$56K'),
    ])->save($path);

    expect(file_exists($path))->toBeTrue();

    $header = file_get_contents($path, false, null, 0, 8);
    expect(str_starts_with($header, "\x89PNG"))->toBeTrue();

    @unlink($path);
})->group('integration');
