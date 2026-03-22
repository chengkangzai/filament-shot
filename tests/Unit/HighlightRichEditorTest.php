<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;

it('generates a selector that matches the RichEditor data-field-wrapper via wire:partial', function () {
    $html = FilamentShot::form([
        RichEditor::make('description')->label('Description'),
    ])
        ->state(['description' => '<p>Content</p>'])
        ->highlight('description')
        ->toHtml();

    // Extract all <style> blocks
    preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $html, $styles);
    $allCss = implode('', $styles[1]);

    // The CSS should contain a selector that targets the rich editor field wrapper
    // via wire:partial attribute which is present on all field types
    expect($allCss)->toContain('schema-component::form.description');
});

it('applies outline highlight to RichEditor field', function () {
    $html = FilamentShot::form([
        TextInput::make('name')->label('Name'),
        RichEditor::make('description')->label('Description')->columnSpanFull(),
    ])
        ->state(['name' => 'Test', 'description' => '<p>Content</p>'])
        ->highlight('description')
        ->toHtml();

    expect($html)->toContain('fi-fo-rich-editor');
    expect($html)->toContain('#ef4444');

    preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $html, $styles);
    $allCss = implode('', $styles[1]);
    expect($allCss)->toContain('schema-component::form.description');
});

it('applies box highlight style to RichEditor', function () {
    $html = FilamentShot::form([
        RichEditor::make('content')->label('Content'),
    ])
        ->state(['content' => '<p>Hello</p>'])
        ->highlight('content', '#3b82f6', 'box')
        ->toHtml();

    expect($html)->toContain('#3b82f6');
    expect($html)->toContain('fi-fo-rich-editor');

    preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $html, $styles);
    $allCss = implode('', $styles[1]);
    expect($allCss)->toContain('schema-component::form.content');
});

it('applies underline highlight style to RichEditor via wrapper', function () {
    $html = FilamentShot::form([
        RichEditor::make('body')->label('Body'),
    ])
        ->state(['body' => '<p>Text</p>'])
        ->highlight('body', '#10b981', 'underline')
        ->toHtml();

    preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $html, $styles);
    $allCss = implode('', $styles[1]);

    expect($allCss)->toContain('#10b981');
    // underline for rich editor should target the wrapper or content area, not a missing id
    expect($allCss)->toContain('schema-component::form.body');
});

it('wire:partial selector is present in rendered html for rich editor', function () {
    $html = FilamentShot::form([
        RichEditor::make('description')->label('Description'),
    ])->state(['description' => '<p>Content</p>'])->toHtml();

    expect($html)->toContain('wire:partial="schema-component::form.description"');
});
