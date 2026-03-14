<?php

use CCK\FilamentShot\Renderers\FormRenderer;

it('returns empty string when no highlights set', function () {
    $renderer = new FormRenderer([]);
    expect($renderer->getHighlightCss())->toBe('');
});

it('generates outline CSS for a simple field key', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('minimum_amount');
    $css = $renderer->getHighlightCss();
    expect($css)->toContain('#form\\.minimum_amount');
    expect($css)->toContain('#ef4444');
    expect($css)->toContain('!important');
    expect($css)->toContain('[data-field-wrapper]');
});

it('escapes dots in nested field keys', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('distribution_configuration.amount');
    $css = $renderer->getHighlightCss();
    expect($css)->toContain('#form\\.distribution_configuration\\.amount');
});

it('supports custom color', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('email', '#3b82f6');
    expect($renderer->getHighlightCss())->toContain('#3b82f6');
});

it('supports multiple highlights', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('name')->highlight('email', '#3b82f6');
    $css = $renderer->getHighlightCss();
    expect($css)->toContain('form\\.name');
    expect($css)->toContain('form\\.email');
});

it('supports box style', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('name', '#ef4444', 'box');
    expect($renderer->getHighlightCss())->toContain('background-color');
});

it('supports underline style', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('name', '#ef4444', 'underline');
    expect($renderer->getHighlightCss())->toContain('border-bottom');
});

it('supports fluent chaining', function () {
    $renderer = new FormRenderer([]);
    $result = $renderer->highlight('name');
    expect($result)->toBeInstanceOf(FormRenderer::class);
});
