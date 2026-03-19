<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;

it('renders action group as dropdown in table record actions', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([['name' => 'Alice']])
        ->recordActions([
            ActionGroup::make([
                Action::make('edit')->label('Edit')->icon('heroicon-o-pencil-square'),
                Action::make('delete')->label('Delete')->icon('heroicon-o-trash')->color('danger'),
            ]),
        ])
        ->toHtml();

    // Should contain the dropdown structure
    expect($html)->toContain('fi-dropdown');
    expect($html)->toContain('fi-dropdown-list');

    // Should contain the action items in the dropdown
    expect($html)->toContain('Edit');
    expect($html)->toContain('Delete');
});

it('renders action group trigger button', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([['name' => 'Alice']])
        ->recordActions([
            ActionGroup::make([
                Action::make('edit')->label('Edit'),
            ]),
        ])
        ->toHtml();

    // Should have a trigger button
    expect($html)->toContain('fi-dropdown-trigger');
});

it('renders dropdown panel without x-cloak', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([['name' => 'Alice']])
        ->recordActions([
            ActionGroup::make([
                Action::make('edit')->label('Edit'),
            ]),
        ])
        ->toHtml();

    // The dropdown panel should NOT have x-cloak (so it's visible)
    expect($html)->not->toMatch('/<div[^>]*fi-dropdown-panel[^>]*x-cloak/s');
});

it('renders mixed actions and action groups', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([['name' => 'Alice']])
        ->recordActions([
            Action::make('view')->label('View')->icon('heroicon-o-eye'),
            ActionGroup::make([
                Action::make('edit')->label('Edit')->icon('heroicon-o-pencil-square'),
                Action::make('delete')->label('Delete')->icon('heroicon-o-trash'),
            ]),
        ])
        ->toHtml();

    // Should have both standalone action and dropdown
    expect($html)
        ->toContain('title="View"')
        ->toContain('fi-dropdown')
        ->toContain('Edit')
        ->toContain('Delete');
});
