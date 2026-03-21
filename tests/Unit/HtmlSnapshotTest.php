<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\StatsOverviewWidget\Stat;

use function Spatie\Snapshots\assertMatchesSnapshot;

/**
 * Normalize volatile parts of Filament HTML before snapshotting:
 * - replaces random Livewire component IDs with a fixed placeholder
 * - strips embedded <style> tag contents (Filament's compiled CSS changes
 *   between minor releases and is not part of our rendering logic)
 * - normalizes absolute file:// paths to be machine-independent
 */
function normalizeHtml(string $html): string
{
    $html = preg_replace('/shot-(form|infolist|stats|table)-[A-Za-z0-9]{8}/', 'shot-$1-SNAPSHOT_ID', $html);
    $html = preg_replace('/<style[^>]*>.*?<\/style>/s', '<style>/* CSS stripped */</style>', $html);
    $html = preg_replace('#file:///[^"]*vendor/#', 'file:///[path]/vendor/', $html);

    return $html;
}

it('html snapshot: table with badges', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'Active' => 'success',
                    'Blocked' => 'danger',
                    default => 'warning',
                }),
        ])
        ->records([
            ['name' => 'Alice', 'status' => 'Active'],
            ['name' => 'Bob', 'status' => 'Blocked'],
        ])
        ->heading('Users')
        ->toHtml();

    assertMatchesSnapshot(normalizeHtml($html));
})->group('snapshot');

it('html snapshot: form with inputs', function () {
    $html = FilamentShot::form([
        TextInput::make('name')->label('Full Name'),
        TextInput::make('email')->label('Email'),
        Select::make('role')
            ->label('Role')
            ->options(['admin' => 'Admin', 'editor' => 'Editor']),
    ])
        ->state(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'role' => 'editor'])
        ->toHtml();

    assertMatchesSnapshot(normalizeHtml($html));
})->group('snapshot');

it('html snapshot: infolist', function () {
    $html = FilamentShot::infolist([
        Section::make('Profile')
            ->schema([
                TextEntry::make('name')->label('Name'),
                TextEntry::make('email')->label('Email'),
                TextEntry::make('role')->label('Role'),
            ]),
    ])
        ->state(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'role' => 'Administrator'])
        ->toHtml();

    assertMatchesSnapshot(normalizeHtml($html));
})->group('snapshot');

it('html snapshot: stats', function () {
    $html = FilamentShot::stats([
        Stat::make('Total Users', '1,234')
            ->description('12% increase')
            ->color('success'),
        Stat::make('Revenue', '$56,789')
            ->description('8% increase'),
        Stat::make('Orders', '456')
            ->description('3% decrease')
            ->color('danger'),
    ])
        ->toHtml();

    assertMatchesSnapshot(normalizeHtml($html));
})->group('snapshot');

it('html snapshot: table striped', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->records([
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
            ['name' => 'Charlie', 'email' => 'charlie@example.com'],
        ])
        ->striped()
        ->toHtml();

    assertMatchesSnapshot(normalizeHtml($html));
})->group('snapshot');
