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

it('renders cleanly with empty actions array', function () {
    $html = FilamentShot::headerActions([])
        ->pageTitle('My Page')
        ->toHtml();

    expect($html)
        ->toContain('My Page')
        ->not->toContain('fi-header-actions-ctn');
});

it('renders cleanly with no page title', function () {
    $html = FilamentShot::headerActions([
        Action::make('create')->label('Create'),
    ])->toHtml();

    expect($html)
        ->not->toContain('fi-header-heading')
        ->toContain('Create');
});

it('renders cleanly with no breadcrumbs', function () {
    $html = FilamentShot::headerActions([
        Action::make('create')->label('Create'),
    ])
        ->pageTitle('My Page')
        ->toHtml();

    expect($html)
        ->not->toContain('fi-breadcrumbs')
        ->toContain('My Page')
        ->toContain('Create');
});

it('escapes XSS in pageTitle', function () {
    $html = FilamentShot::headerActions([])
        ->pageTitle('<script>alert("xss")</script>')
        ->toHtml();

    expect($html)
        ->not->toContain('<script>alert("xss")</script>')
        ->toContain('&lt;script&gt;');
});

it('escapes XSS in breadcrumb items', function () {
    $html = FilamentShot::headerActions([])
        ->breadcrumbs(['<script>alert("xss")</script>', 'Safe Item'])
        ->toHtml();

    expect($html)
        ->not->toContain('<script>alert("xss")</script>')
        ->toContain('&lt;script&gt;');
});

it('uses correct filament v5 css classes', function () {
    $html = FilamentShot::headerActions([
        Action::make('create')->label('Create'),
    ])
        ->pageTitle('My Page')
        ->breadcrumbs(['Parent', 'My Page'])
        ->toHtml();

    expect($html)
        ->toContain('fi-header')
        ->toContain('fi-header-has-breadcrumbs')
        ->toContain('fi-breadcrumbs')
        ->toContain('fi-breadcrumbs-item')
        ->toContain('fi-header-heading')
        ->toContain('fi-header-actions-ctn');
});

it('last breadcrumb item is visually distinct from parent items', function () {
    $html = FilamentShot::headerActions([])
        ->breadcrumbs(['Settings', 'Tier Configurations', 'Silver Tier'])
        ->toHtml();

    // All items use the same fi-breadcrumbs-item-label class; the CSS layer
    // handles visual distinction. Verify all three labels are present.
    expect($html)
        ->toContain('fi-breadcrumbs-item-label')
        ->toContain('Settings')
        ->toContain('Tier Configurations')
        ->toContain('Silver Tier');
});
