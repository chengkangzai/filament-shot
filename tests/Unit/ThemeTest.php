<?php

use CCK\FilamentShot\Renderers\FormRenderer;

it('defaults to light mode', function () {
    config()->set('filament-shot.theme.dark_mode', false);

    $renderer = new FormRenderer([]);

    expect($renderer->isDarkMode())->toBeFalse();
});

it('can toggle dark mode', function () {
    $renderer = new FormRenderer([]);
    $renderer->darkMode();

    expect($renderer->isDarkMode())->toBeTrue();
});

it('can toggle back to light mode', function () {
    $renderer = new FormRenderer([]);
    $renderer->darkMode()->lightMode();

    expect($renderer->isDarkMode())->toBeFalse();
});

it('uses config default for primary color', function () {
    config()->set('filament-shot.theme.primary_color', '#ff0000');

    $renderer = new FormRenderer([]);

    expect($renderer->getPrimaryColor())->toBe('#ff0000');
});

it('allows custom primary color', function () {
    $renderer = new FormRenderer([]);
    $renderer->primaryColor('#00ff00');

    expect($renderer->getPrimaryColor())->toBe('#00ff00');
});
