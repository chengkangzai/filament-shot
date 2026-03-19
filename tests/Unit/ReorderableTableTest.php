<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Tables\Columns\TextColumn;

it('renders a reorderable table with drag handle cells', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->records([
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ])
        ->reorderable()
        ->toHtml();

    // Should contain the reorder handle button elements (one per row)
    expect($html)->toMatch('/<button[^>]*fi-ta-reorder-handle/');

    // Should have drag handle buttons in each row (2 records = 2 buttons)
    preg_match_all('/<button[^>]*fi-ta-reorder-handle/', $html, $matches);
    expect(count($matches[0]))->toBe(2);

    // Should still render normal columns
    expect($html)
        ->toContain('Alice')
        ->toContain('Bob')
        ->toContain('alice@example.com');
});

it('does not render drag handles when reorderable is not set', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([['name' => 'Alice']])
        ->toHtml();

    expect($html)->not->toMatch('/<button[^>]*fi-ta-reorder-handle/');
});

it('renders drag handle as first cell in each row', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([['name' => 'Alice']])
        ->reorderable()
        ->toHtml();

    // The reorder handle button should appear before the data cell content
    preg_match('/<button[^>]*fi-ta-reorder-handle/', $html, $handleMatch, PREG_OFFSET_CAPTURE);
    $handlePos = $handleMatch[0][1];
    $namePos = strpos($html, 'Alice');

    expect($handlePos)->toBeLessThan($namePos);
});

it('renders drag handle icon with bars SVG', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([['name' => 'Alice']])
        ->reorderable()
        ->toHtml();

    // Should contain a button with both fi-ta-reorder-handle and fi-icon-btn
    expect($html)->toMatch('/<button[^>]*fi-ta-reorder-handle fi-icon-btn/');

    // Should contain an SVG inside the handle
    expect($html)->toMatch('/<button[^>]*fi-ta-reorder-handle[^>]*>.*?<svg/s');
});
