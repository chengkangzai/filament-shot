<?php

use CCK\FilamentShot\FilamentShot;

it('saves blade screenshot as PNG', function () {
    $path = sys_get_temp_dir() . '/filament-shot-view-test.png';

    FilamentShot::blade('<div style="padding: 2rem; font-family: sans-serif;"><h1 style="color: #3b82f6;">{{ $title }}</h1><p>{{ $body }}</p></div>')
        ->data([
            'title' => 'Hello from FilamentShot::blade()',
            'body' => 'Rendering arbitrary Blade templates as screenshots.',
        ])
        ->width(600)
        ->save($path);

    expect(file_exists($path))->toBeTrue();

    $header = file_get_contents($path, false, null, 0, 8);
    expect(str_starts_with($header, "\x89PNG"))->toBeTrue();

    @unlink($path);
})->group('integration');

it('saves named view screenshot as PNG', function () {
    app('view')->addNamespace('test-fixtures', __DIR__ . '/../fixtures/views');

    $path = sys_get_temp_dir() . '/filament-shot-named-view-test.png';

    FilamentShot::view('test-fixtures::tier-card')
        ->data([
            'tier' => ['name' => 'Gold', 'color' => '#FFD700'],
            'tierPoints' => 1500,
            'redeemablePoints' => 1200,
        ])
        ->width(700)
        ->save($path);

    expect(file_exists($path))->toBeTrue();

    $header = file_get_contents($path, false, null, 0, 8);
    expect(str_starts_with($header, "\x89PNG"))->toBeTrue();

    @unlink($path);
})->group('integration');
