<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Tables\Columns\TextColumn;

it('saves table screenshot as PNG', function () {
    $path = sys_get_temp_dir() . '/filament-shot-table-test.png';

    FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->records([
            ['name' => 'Alice', 'email' => 'alice@example.com'],
        ])
        ->save($path);

    expect(file_exists($path))->toBeTrue();

    $header = file_get_contents($path, false, null, 0, 8);
    expect(str_starts_with($header, "\x89PNG"))->toBeTrue();

    @unlink($path);
})->group('integration');

it('saves table with badge columns as PNG', function () {
    $path = sys_get_temp_dir() . '/filament-shot-table-badge-test.png';

    FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('status')->badge()->color('danger'),
        ])
        ->records([
            ['name' => 'Alice', 'status' => 'Blocked'],
            ['name' => 'Bob', 'status' => 'Active'],
        ])
        ->save($path);

    expect(file_exists($path))->toBeTrue();

    $header = file_get_contents($path, false, null, 0, 8);
    expect(str_starts_with($header, "\x89PNG"))->toBeTrue();

    @unlink($path);
})->group('integration');
