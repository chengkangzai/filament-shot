<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Infolists\Components\TextEntry;

it('saves infolist screenshot as PNG', function () {
    $path = sys_get_temp_dir() . '/filament-shot-infolist-test.png';

    FilamentShot::infolist([
        TextEntry::make('name')->label('Name'),
        TextEntry::make('email')->label('Email'),
    ])->state([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ])->save($path);

    expect(file_exists($path))->toBeTrue();

    $header = file_get_contents($path, false, null, 0, 8);
    expect(str_starts_with($header, "\x89PNG"))->toBeTrue();

    @unlink($path);
})->group('integration');
