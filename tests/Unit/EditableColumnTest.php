<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;

it('renders TextInputColumn with input field visible', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            TextInputColumn::make('email')->label('Email'),
        ])
        ->records([
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-ta-text-input')
        ->toContain('type="text"')
        ->toContain('alice@example.com')
        ->toContain('bob@example.com');
});

it('renders TextInputColumn value in the input field', function () {
    $html = FilamentShot::table()
        ->columns([
            TextInputColumn::make('email')->label('Email'),
        ])
        ->records([
            ['email' => 'test@example.com'],
        ])
        ->toHtml();

    expect($html)->toContain('value="test@example.com"');
});

it('renders SelectColumn with select element visible', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            SelectColumn::make('status')
                ->options(['active' => 'Active', 'blocked' => 'Blocked']),
        ])
        ->records([
            ['name' => 'Alice', 'status' => 'active'],
            ['name' => 'Bob', 'status' => 'blocked'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-ta-select')
        ->toContain('<select')
        ->toContain('Active')
        ->toContain('Blocked');
});

it('renders SelectColumn with correct option selected', function () {
    $html = FilamentShot::table()
        ->columns([
            SelectColumn::make('status')
                ->options(['active' => 'Active', 'blocked' => 'Blocked']),
        ])
        ->records([
            ['status' => 'blocked'],
        ])
        ->toHtml();

    expect($html)->toContain('selected');
    // The selected option value should match the record value
    expect($html)->toContain('value="blocked" selected');
});

it('renders SelectColumn with correct options for each row', function () {
    $html = FilamentShot::table()
        ->columns([
            SelectColumn::make('role')
                ->options(['admin' => 'Admin', 'editor' => 'Editor', 'viewer' => 'Viewer']),
        ])
        ->records([
            ['role' => 'admin'],
            ['role' => 'viewer'],
        ])
        ->toHtml();

    // Both rows should have the full options list
    expect(substr_count($html, '<select'))->toBe(2);
    // Admin should be selected in first row
    expect($html)->toContain('value="admin" selected');
    // Viewer should be selected in second row
    expect($html)->toContain('value="viewer" selected');
});

it('renders TextInputColumn without breaking other columns', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name')->label('Name'),
            TextInputColumn::make('email')->label('Email'),
            SelectColumn::make('status')
                ->options(['active' => 'Active', 'blocked' => 'Blocked']),
        ])
        ->records([
            ['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('Alice')
        ->toContain('alice@example.com')
        ->toContain('Active')
        ->toContain('fi-ta-ctn');
});

it('escapes XSS in TextInputColumn value', function () {
    $html = FilamentShot::table()
        ->columns([
            TextInputColumn::make('name')->label('Name'),
        ])
        ->records([
            ['name' => '<script>alert("xss")</script>'],
        ])
        ->toHtml();

    expect($html)
        ->not->toContain('<script>alert("xss")</script>')
        ->toContain('&lt;script&gt;');
});

it('escapes XSS in SelectColumn option labels', function () {
    $html = FilamentShot::table()
        ->columns([
            SelectColumn::make('status')
                ->options(['<script>alert(1)</script>' => '<b>Bold Label</b>']),
        ])
        ->records([
            ['status' => '<script>alert(1)</script>'],
        ])
        ->toHtml();

    expect($html)
        ->not->toContain('<script>alert(1)</script>')
        ->not->toContain('<b>Bold Label</b>')
        ->toContain('&lt;script&gt;')
        ->toContain('&lt;b&gt;Bold Label&lt;/b&gt;');
});

it('renders SelectColumn with closure options without crashing', function () {
    $html = FilamentShot::table()
        ->columns([
            SelectColumn::make('status')
                ->options(fn () => ['active' => 'Active', 'blocked' => 'Blocked']),
        ])
        ->records([
            ['status' => 'active'],
        ])
        ->toHtml();

    // Should render a select without crashing (options may be empty if closure
    // cannot be evaluated outside of a Livewire context)
    expect($html)
        ->toContain('fi-ta-select')
        ->toContain('<select');
});

it('renders SelectColumn with value not in options without selecting any option', function () {
    $html = FilamentShot::table()
        ->columns([
            SelectColumn::make('status')
                ->options(['active' => 'Active', 'blocked' => 'Blocked']),
        ])
        ->records([
            ['status' => 'unknown_value'],
        ])
        ->toHtml();

    // No option should have the selected attribute
    expect($html)->not->toContain(' selected');
});

it('renders TextInputColumn with null value without crashing', function () {
    $html = FilamentShot::table()
        ->columns([
            TextInputColumn::make('email')->label('Email'),
        ])
        ->records([
            ['email' => null],
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-ta-text-input')
        ->toContain('value=""');
});
