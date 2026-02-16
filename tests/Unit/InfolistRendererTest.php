<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Infolists\Components\TextEntry;

it('renders text entries with labels', function () {
    $html = FilamentShot::infolist([
        TextEntry::make('name')->label('Full Name'),
        TextEntry::make('email')->label('Email Address'),
    ])->state([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ])->toHtml();

    expect($html)
        ->toContain('Full Name')
        ->toContain('Email Address')
        ->toContain('John Doe')
        ->toContain('john@example.com')
        ->toContain('fi-in-entry');
});

it('renders entry labels from name when not set', function () {
    $html = FilamentShot::infolist([
        TextEntry::make('full_name'),
    ])->state([
        'full_name' => 'Jane Doe',
    ])->toHtml();

    expect($html)
        ->toContain('Jane Doe')
        ->toContain('fi-in-entry-label');
});

it('renders entries with state override', function () {
    $html = FilamentShot::infolist([
        TextEntry::make('status'),
    ])->state([
        'status' => 'Active',
    ])->toHtml();

    expect($html)->toContain('Active');
});

it('renders entries with constant state', function () {
    $html = FilamentShot::infolist([
        TextEntry::make('greeting')->state('Hello World'),
    ])->toHtml();

    expect($html)->toContain('Hello World');
});
