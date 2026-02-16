<?php

use CCK\FilamentShot\Renderers\FormRenderer;

it('uses config defaults for viewport', function () {
    config()->set('shot.viewport.width', 800);
    config()->set('shot.viewport.height', 600);
    config()->set('shot.viewport.device_scale_factor', 3);

    $renderer = new FormRenderer([]);

    expect($renderer->getWidth())->toBe(800)
        ->and($renderer->getHeight())->toBe(600)
        ->and($renderer->getDeviceScale())->toBe(3);
});

it('allows custom viewport values', function () {
    $renderer = new FormRenderer([]);
    $renderer->width(1920)->height(1080)->deviceScale(1);

    expect($renderer->getWidth())->toBe(1920)
        ->and($renderer->getHeight())->toBe(1080)
        ->and($renderer->getDeviceScale())->toBe(1);
});

it('supports fluent chaining', function () {
    $renderer = new FormRenderer([]);
    $result = $renderer->width(1024)->height(768)->deviceScale(2);

    expect($result)->toBeInstanceOf(FormRenderer::class);
});
