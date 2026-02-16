<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\TextInput;

it('saves form screenshot as PNG', function () {
    $path = sys_get_temp_dir() . '/filament-shot-form-test.png';

    FilamentShot::form([
        TextInput::make('name')->label('Name'),
        TextInput::make('email')->label('Email'),
    ])->save($path);

    expect(file_exists($path))->toBeTrue();

    $header = file_get_contents($path, false, null, 0, 8);
    expect(str_starts_with($header, "\x89PNG"))->toBeTrue();

    @unlink($path);
})->group('integration');

it('returns base64 screenshot', function () {
    $base64 = FilamentShot::form([
        TextInput::make('name')->label('Name'),
    ])->toBase64();

    expect($base64)->toBeString()->not->toBeEmpty();

    $decoded = base64_decode($base64, true);
    expect($decoded)->not->toBeFalse();
    expect(str_starts_with($decoded, "\x89PNG"))->toBeTrue();
})->group('integration');
