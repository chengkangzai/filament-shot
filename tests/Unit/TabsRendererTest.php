<?php

use CCK\FilamentShot\FilamentShot;
use Composer\InstalledVersions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

function isFilamentV4(): bool
{
    return str_starts_with(InstalledVersions::getVersion('filament/filament') ?? '', '4.');
}

it('renders tabs with first tab active by default', function () {
    $html = FilamentShot::form([
        Tabs::make('Settings')
            ->tabs([
                Tab::make('General')
                    ->schema([
                        TextInput::make('name')->label('Name'),
                    ]),
                Tab::make('Advanced')
                    ->schema([
                        TextInput::make('debug')->label('Debug Mode'),
                    ]),
            ]),
    ])
        ->state(['name' => 'Test', 'debug' => 'On'])
        ->renderHtml();

    // Tab bar should be visible (no x-cloak)
    expect($html)->not->toMatch('/<nav[^>]*fi-tabs[^>]*x-cloak/');

    if (! isFilamentV4()) {
        // First tab button should have fi-active (v5 class)
        expect($html)->toContain('fi-tabs-item fi-active');

        // First tab pane should have fi-active (v5 class)
        expect($html)->toContain('fi-sc-tabs-tab fi-active');
    }

    // Tab labels should be visible
    expect($html)->toContain('General');
    expect($html)->toContain('Advanced');
});

it('renders tabs with specific tab active via activeTab()', function () {
    $html = FilamentShot::form([
        Tabs::make('Settings')
            ->tabs([
                Tab::make('General')
                    ->schema([
                        TextInput::make('name')->label('Name'),
                    ]),
                Tab::make('Advanced')
                    ->schema([
                        TextInput::make('debug')->label('Debug Mode'),
                    ]),
            ])
            ->activeTab(2),
    ])
        ->state(['name' => 'Test', 'debug' => 'On'])
        ->renderHtml();

    // Tab labels should always be present
    expect($html)->toContain('General')->toContain('Advanced');

    if (! isFilamentV4()) {
        // Second tab button should have fi-active (v5 class)
        expect($html)->toMatch('/fi-tabs-item fi-active"[^>]*data-tab-key="advanced/s');

        // First tab button should NOT have fi-active (v5 class)
        expect($html)->not->toMatch('/fi-tabs-item fi-active"[^>]*data-tab-key="general/s');

        // Second tab pane should have fi-active (v5 class)
        expect($html)->toMatch('/fi-sc-tabs-tab fi-active"[^>]*id="[^"]*advanced/s');
    }
});

it('renders tabs with contained styling', function () {
    $html = FilamentShot::form([
        Tabs::make('Settings')
            ->tabs([
                Tab::make('Tab One')
                    ->schema([
                        TextInput::make('field1'),
                    ]),
            ]),
    ])
        ->state([])
        ->renderHtml();

    expect($html)->toContain('fi-sc-tabs fi-contained');
    expect($html)->toContain('fi-tabs fi-contained');
});

it('renders tabs with icons on tab labels', function () {
    $html = FilamentShot::form([
        Tabs::make('Settings')
            ->tabs([
                Tab::make('General')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        TextInput::make('name'),
                    ]),
                Tab::make('Notifications')
                    ->icon('heroicon-o-bell')
                    ->schema([
                        Toggle::make('enabled'),
                    ]),
            ]),
    ])
        ->state([])
        ->renderHtml();

    if (! isFilamentV4()) {
        expect($html)->toContain('fi-tabs-item fi-active');
    }

    expect($html)
        ->toContain('General')
        ->toContain('Notifications');
});
