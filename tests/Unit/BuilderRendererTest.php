<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

it('renders builder blocks with their content', function () {
    $html = FilamentShot::form([
        Builder::make('content')
            ->blocks([
                Block::make('heading')
                    ->label('Heading')
                    ->schema([TextInput::make('text')->label('Text')]),
                Block::make('paragraph')
                    ->label('Paragraph')
                    ->schema([Textarea::make('body')->label('Body')]),
            ]),
    ])
        ->state([
            'content' => [
                ['type' => 'heading', 'data' => ['text' => 'Hello World']],
                ['type' => 'paragraph', 'data' => ['body' => 'Some paragraph text.']],
            ],
        ])
        ->toHtml();

    expect($html)->toContain('fi-fo-builder');
    // Content should be visible
    expect($html)->toContain('Hello World');
    expect($html)->toContain('Some paragraph text.');
});

it('renders builder with block type labels visible', function () {
    $html = FilamentShot::form([
        Builder::make('blocks')
            ->blocks([
                Block::make('heading')->label('Heading Block')->schema([
                    TextInput::make('text'),
                ]),
            ]),
    ])
        ->state([
            'blocks' => [
                ['type' => 'heading', 'data' => ['text' => 'Test']],
            ],
        ])
        ->toHtml();

    expect($html)->toContain('Heading Block');
});

it('renders builder item content visible without x-show hiding it', function () {
    $html = FilamentShot::form([
        Builder::make('content')
            ->blocks([
                Block::make('heading')
                    ->label('Heading')
                    ->schema([TextInput::make('text')->label('Text')]),
            ]),
    ])
        ->state([
            'content' => [
                ['type' => 'heading', 'data' => ['text' => 'Visible Content']],
            ],
        ])
        ->toHtml();

    // The block content area should not have x-show="! isCollapsed" — we replace it
    // with a static visible style so content is visible even without Alpine.js
    expect($html)->not->toContain('x-show="! isCollapsed"');
    expect($html)->toContain('fi-fo-builder-item-content');
    expect($html)->toContain('Visible Content');
});

it('renders builder add-between-items as visible', function () {
    $html = FilamentShot::form([
        Builder::make('content')
            ->blocks([
                Block::make('heading')
                    ->label('Heading')
                    ->schema([TextInput::make('text')->label('Text')]),
                Block::make('paragraph')
                    ->label('Paragraph')
                    ->schema([Textarea::make('body')->label('Body')]),
            ]),
    ])
        ->state([
            'content' => [
                ['type' => 'heading', 'data' => ['text' => 'Hello']],
                ['type' => 'paragraph', 'data' => ['body' => 'World']],
            ],
        ])
        ->toHtml();

    // The "Insert between blocks" container is hidden by CSS (visibility:hidden, height:0)
    // Our fixBuilder() should inject an inline style to make it visible
    expect($html)->toContain('fi-fo-builder-add-between-items-ctn');
    // It should have a visible style injected
    expect($html)->toContain('fi-fo-builder-add-between-items-ctn" style="');
});

it('renders builder move and delete buttons', function () {
    $html = FilamentShot::form([
        Builder::make('content')
            ->blocks([
                Block::make('heading')
                    ->label('Heading')
                    ->schema([TextInput::make('text')->label('Text')]),
            ]),
    ])
        ->state([
            'content' => [
                ['type' => 'heading', 'data' => ['text' => 'Test']],
            ],
        ])
        ->toHtml();

    // Move button (sortable handle) should be present
    expect($html)->toContain('x-sortable-handle');
    // Delete button should be present
    expect($html)->toContain('fi-color-danger');
});

it('fixBuilder removes x-show when builder item content div has extra CSS classes (blockPreviews)', function () {
    // When ->blockPreviews() is enabled, Filament adds extra classes to the content div,
    // e.g. class="fi-fo-builder-item-content fi-fo-builder-item-content-has-preview".
    // The regex patterns must tolerate extra classes and still remove x-show.
    $renderer = FilamentShot::form([
        Builder::make('content')
            ->blocks([
                Block::make('heading')
                    ->label('Heading')
                    ->schema([TextInput::make('text')->label('Text')]),
            ]),
    ]);

    $reflection = new ReflectionClass($renderer);
    $method = $reflection->getMethod('fixBuilder');
    $method->setAccessible(true);

    // Simulate x-show before class (with extra class appended after fi-fo-builder-item-content)
    $htmlXShowBeforeClass = '<div' . "\n" .
        '    x-show="! isCollapsed"' . "\n" .
        '    class="fi-fo-builder-item-content fi-fo-builder-item-content-has-preview"' . "\n" .
        '>';
    $resultXShowBeforeClass = $method->invoke($renderer, $htmlXShowBeforeClass);
    expect($resultXShowBeforeClass)
        ->not->toContain('x-show="! isCollapsed"')
        ->toContain('style="display:block"');

    // Simulate class before x-show (with extra class appended after fi-fo-builder-item-content)
    $htmlClassBeforeXShow = '<div' . "\n" .
        '    class="fi-fo-builder-item-content fi-fo-builder-item-content-has-preview"' . "\n" .
        '    x-show="! isCollapsed"' . "\n" .
        '>';
    $resultClassBeforeXShow = $method->invoke($renderer, $htmlClassBeforeXShow);
    expect($resultClassBeforeXShow)
        ->not->toContain('x-show="! isCollapsed"')
        ->toContain('style="display:block"');
});
