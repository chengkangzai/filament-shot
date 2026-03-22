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

it('handles field names with hyphens correctly', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('first-name');
    $css = $renderer->getHighlightCss();

    // Hyphens are valid in CSS IDs without escaping
    expect($css)->toContain('#form\\.first-name');
    // wire:partial attribute value includes the hyphen as-is (quoted string, no escaping needed)
    expect($css)->toContain('schema-component::form.first-name');
    expect($css)->toContain('[data-field-wrapper]');
});

it('outline style generates both wire:partial and :has selectors', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('title', '#ef4444', 'outline');
    $css = $renderer->getHighlightCss();

    expect($css)->toContain('[wire\\:partial="schema-component::form.title"] [data-field-wrapper]');
    expect($css)->toContain('[data-field-wrapper]:has(#form\\.title)');
});

it('box style generates both wire:partial and :has selectors', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('title', '#ef4444', 'box');
    $css = $renderer->getHighlightCss();

    expect($css)->toContain('[wire\\:partial="schema-component::form.title"] [data-field-wrapper]');
    expect($css)->toContain('[data-field-wrapper]:has(#form\\.title)');
});

it('underline style generates wire:partial selector for rich editor content area', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('body', '#ef4444', 'underline');
    $css = $renderer->getHighlightCss();

    // Underline targets [x-ref="editor"] inside the wire:partial container for RichEditor
    expect($css)->toContain('[wire\\:partial="schema-component::form.body"] [data-field-wrapper] [x-ref="editor"]');
    // Fallback selector uses the field CSS id
    expect($css)->toContain('#form\\.body');
});

it('nested field key dots in wire:partial value are unescaped (quoted attribute value)', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('address.street');
    $css = $renderer->getHighlightCss();

    // In an attribute value string, dots need no CSS escaping
    expect($css)->toContain('"schema-component::form.address.street"');
    // But in the CSS ID selector, dots must be escaped
    expect($css)->toContain('#form\\.address\\.street');
});
