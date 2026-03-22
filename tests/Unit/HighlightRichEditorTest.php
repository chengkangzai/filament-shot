<?php

use CCK\FilamentShot\Renderers\FormRenderer;

it('generates a selector that matches the RichEditor data-field-wrapper via wire:partial', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('description');
    $css = $renderer->getHighlightCss();

    // The CSS should contain a selector that targets the rich editor field wrapper
    // via wire:partial attribute which is present on all field types
    expect($css)->toContain('schema-component::form.description');
    expect($css)->toContain('[data-field-wrapper]');
    expect($css)->toContain('wire\\:partial');
});

it('applies outline highlight to RichEditor field', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('description');
    $css = $renderer->getHighlightCss();

    expect($css)->toContain('#ef4444');
    expect($css)->toContain('schema-component::form.description');
    expect($css)->toContain('[data-field-wrapper]');
    expect($css)->not()->toContain('background-color');
});

it('applies box highlight style to RichEditor', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('content', '#3b82f6', 'box');
    $css = $renderer->getHighlightCss();

    expect($css)->toContain('#3b82f6');
    expect($css)->toContain('schema-component::form.content');
    expect($css)->toContain('background-color');
});

it('applies underline highlight style to RichEditor via wrapper', function () {
    $renderer = new FormRenderer([]);
    $renderer->highlight('body', '#10b981', 'underline');
    $css = $renderer->getHighlightCss();

    expect($css)->toContain('#10b981');
    // underline for rich editor should target the content area via wire:partial
    expect($css)->toContain('schema-component::form.body');
    expect($css)->toContain('border-bottom');
});

it('wire:partial CSS selector contains escaped colon for attribute name', function () {
    // In CSS, the colon in an attribute name must be escaped as \:
    // This verifies that wire\:partial is correctly escaped in the generated CSS
    $renderer = new FormRenderer([]);
    $renderer->highlight('description');
    $css = $renderer->getHighlightCss();

    // CSS attribute selector for wire:partial must escape the colon in the attribute name
    expect($css)->toContain('wire\\:partial=');
    // The attribute value contains :: (schema-component separator) which needs no escaping in quoted values
    expect($css)->toContain('"schema-component::form.description"');
});
