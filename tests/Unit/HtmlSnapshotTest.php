<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\StatsOverviewWidget\Stat;

use function Spatie\Snapshots\assertMatchesSnapshot;

/**
 * Normalize volatile parts of Filament HTML before snapshotting:
 * replaces random Livewire component IDs with a fixed placeholder.
 */
function normalizeHtml(string $html): string
{
    return preg_replace('/shot-(form|infolist|stats|table)-[A-Za-z0-9]{8}/', 'shot-$1-SNAPSHOT_ID', $html);
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
        \Filament\Forms\Components\TextInput::make('name')->label('Full Name'),
        \Filament\Forms\Components\TextInput::make('email')->label('Email'),
        \Filament\Forms\Components\Select::make('role')
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
