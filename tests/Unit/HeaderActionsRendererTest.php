<?php

use CCK\FilamentShot\FilamentShot;
use CCK\FilamentShot\Renderers\HeaderActionsRenderer;
use Filament\Actions\Action;

it('FilamentShot::headerActions() returns a HeaderActionsRenderer', function () {
    expect(FilamentShot::headerActions([]))->toBeInstanceOf(HeaderActionsRenderer::class);
});

it('renders header actions as buttons', function () {
    $html = FilamentShot::headerActions([
        Action::make('simulate')->label('Simulate'),
        Action::make('export')->label('Export'),
    ])->toHtml();

    expect($html)
        ->toContain('Simulate')
        ->toContain('Export');
});

it('renders with page title', function () {
    $html = FilamentShot::headerActions([
        Action::make('create')->label('Create'),
    ])
        ->pageTitle('Tier Configurations')
        ->toHtml();

    expect($html)->toContain('Tier Configurations');
});

it('renders with breadcrumbs', function () {
    $html = FilamentShot::headerActions([
        Action::make('simulate')->label('Simulate'),
    ])
        ->breadcrumbs(['Settings', 'Tier Configurations', 'Silver Tier'])
        ->pageTitle('Silver Tier')
        ->toHtml();

    expect($html)
        ->toContain('Settings')
        ->toContain('Tier Configurations')
        ->toContain('Silver Tier');
});

it('renders action with icon and color', function () {
    $html = FilamentShot::headerActions([
        Action::make('simulate')
            ->label('Simulate')
            ->icon('heroicon-o-play')
            ->color('primary'),
    ])->toHtml();

    expect($html)->toContain('Simulate');
});

it('wraps in filament base layout', function () {
    $html = FilamentShot::headerActions([])->toHtml();
    expect($html)->toContain('<html');
});
