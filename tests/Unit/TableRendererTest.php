<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Tables\Columns\TextColumn;

it('renders table with columns and records', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->records([
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('Name')
        ->toContain('Email')
        ->toContain('Alice')
        ->toContain('alice@example.com')
        ->toContain('Bob')
        ->toContain('bob@example.com')
        ->toContain('fi-ta');
});

it('renders column headers', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name')->label('Full Name'),
        ])
        ->records([])
        ->toHtml();

    expect($html)
        ->toContain('Full Name')
        ->toContain('fi-ta-header-cell');
});

it('renders empty state', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([])
        ->toHtml();

    expect($html)->toContain('No records found');
});

it('supports striped rows', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([
            ['name' => 'First'],
            ['name' => 'Second'],
        ])
        ->striped()
        ->toHtml();

    expect($html)->toContain('background-color: #f9fafb');
});

it('renders table with heading', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([])
        ->heading('Users List')
        ->toHtml();

    expect($html)
        ->toContain('Users List')
        ->toContain('fi-ta-header');
});
