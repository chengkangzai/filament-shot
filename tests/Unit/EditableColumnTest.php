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
