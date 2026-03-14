<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\StatsOverviewWidget\Stat;

it('image snapshot: table with badges', function () {
    $path = tempnam(sys_get_temp_dir(), 'fs_') . '.png';

    FilamentShot::table()
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
        ->width(800)
        ->save($path);

    assertImageMatchesSnapshot('table-badges', $path);

    @unlink($path);
})->group('snapshot', 'integration');

it('image snapshot: table striped', function () {
    $path = tempnam(sys_get_temp_dir(), 'fs_') . '.png';

    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('email'),
        ])
        ->records([
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
            ['name' => 'Charlie', 'email' => 'charlie@example.com'],
        ])
        ->striped()
        ->heading('Team')
        ->width(800)
        ->save($path);

    assertImageMatchesSnapshot('table-striped', $path);

    @unlink($path);
})->group('snapshot', 'integration');

it('image snapshot: form with inputs', function () {
    $path = tempnam(sys_get_temp_dir(), 'fs_') . '.png';

    FilamentShot::form([
        TextInput::make('name')->label('Full Name'),
        TextInput::make('email')->label('Email'),
        Select::make('role')
            ->label('Role')
            ->options(['admin' => 'Admin', 'editor' => 'Editor']),
    ])
        ->state(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'role' => 'editor'])
        ->width(600)
        ->save($path);

    assertImageMatchesSnapshot('form-basic', $path);

    @unlink($path);
})->group('snapshot', 'integration');

it('image snapshot: infolist', function () {
    $path = tempnam(sys_get_temp_dir(), 'fs_') . '.png';

    FilamentShot::infolist([
        Section::make('Profile')
            ->schema([
                TextEntry::make('name')->label('Name'),
                TextEntry::make('email')->label('Email'),
                TextEntry::make('role')->label('Role'),
            ]),
    ])
        ->state(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'role' => 'Administrator'])
        ->width(600)
        ->save($path);

    assertImageMatchesSnapshot('infolist-basic', $path);

    @unlink($path);
})->group('snapshot', 'integration');

it('image snapshot: stats', function () {
    $path = tempnam(sys_get_temp_dir(), 'fs_') . '.png';

    FilamentShot::stats([
        Stat::make('Total Users', '1,234')
            ->description('12% increase')
            ->color('success'),
        Stat::make('Revenue', '$56,789')
            ->description('8% increase'),
        Stat::make('Orders', '456')
            ->description('3% decrease')
            ->color('danger'),
    ])
        ->width(900)
        ->save($path);

    assertImageMatchesSnapshot('stats-basic', $path);

    @unlink($path);
})->group('snapshot', 'integration');

it('image snapshot: dark mode table', function () {
    $path = tempnam(sys_get_temp_dir(), 'fs_') . '.png';

    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state) => $state === 'Active' ? 'success' : 'danger'),
        ])
        ->records([
            ['name' => 'Alice', 'status' => 'Active'],
            ['name' => 'Bob', 'status' => 'Inactive'],
        ])
        ->darkMode()
        ->width(800)
        ->save($path);

    assertImageMatchesSnapshot('table-dark', $path);

    @unlink($path);
})->group('snapshot', 'integration');
