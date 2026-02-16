<?php

use CCK\FilamentShot\FilamentShot;
use CCK\FilamentShot\Renderers\FormRenderer;
use CCK\FilamentShot\Renderers\InfolistRenderer;
use CCK\FilamentShot\Renderers\StatsRenderer;
use CCK\FilamentShot\Renderers\TableRenderer;

it('creates a form renderer', function () {
    $renderer = FilamentShot::form([]);

    expect($renderer)->toBeInstanceOf(FormRenderer::class);
});

it('creates a table renderer', function () {
    $renderer = FilamentShot::table();

    expect($renderer)->toBeInstanceOf(TableRenderer::class);
});

it('creates an infolist renderer', function () {
    $renderer = FilamentShot::infolist([]);

    expect($renderer)->toBeInstanceOf(InfolistRenderer::class);
});

it('creates a stats renderer', function () {
    $renderer = FilamentShot::stats([]);

    expect($renderer)->toBeInstanceOf(StatsRenderer::class);
});
