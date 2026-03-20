<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\ColorPicker;

it('renders color picker with color swatch preview', function () {
    $html = FilamentShot::form([
        ColorPicker::make('color')->label('Brand Color'),
    ])
        ->state(['color' => '#3b82f6'])
        ->renderHtml();

    // Should have the color picker class
    expect($html)->toContain('fi-fo-color-picker');

    // Preview swatch should have inline background-color
    expect($html)->toMatch('/fi-fo-color-picker-preview[^>]*style="[^"]*background-color:\s*#3b82f6/');
});

it('renders color picker without fi-empty class when color is set', function () {
    $html = FilamentShot::form([
        ColorPicker::make('color')->label('Color'),
    ])
        ->state(['color' => '#ef4444'])
        ->renderHtml();

    // The preview should NOT have fi-empty when color is set
    expect($html)->not->toMatch('/fi-fo-color-picker-preview[^"]*fi-empty[^"]*"[^>]*style="[^"]*background-color/');
});

it('renders color picker with fi-empty class when no color', function () {
    $html = FilamentShot::form([
        ColorPicker::make('color')->label('Color'),
    ])
        ->state([])
        ->renderHtml();

    // Should have fi-fo-color-picker-preview (may have fi-empty from x-bind default)
    expect($html)->toContain('fi-fo-color-picker-preview');
});

it('renders color picker input with value', function () {
    $html = FilamentShot::form([
        ColorPicker::make('color')->label('Color'),
    ])
        ->state(['color' => '#22c55e'])
        ->renderHtml();

    // Input should have the color value
    expect($html)->toContain('#22c55e');
});
