<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\StatsOverviewWidget\Stat;

$outputDir = __DIR__ . '/../../examples/images';

beforeAll(function () use ($outputDir) {
    if (! is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
});

it('generates form light example', function () use ($outputDir) {
    FilamentShot::form([
        TextInput::make('name')
            ->label('Full Name')
            ->placeholder('Enter your name'),
        TextInput::make('email')
            ->label('Email Address')
            ->placeholder('you@example.com'),
        Select::make('role')
            ->label('Role')
            ->options([
                'admin' => 'Administrator',
                'editor' => 'Editor',
                'viewer' => 'Viewer',
            ]),
        Toggle::make('active')->label('Active'),
    ])
        ->state(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'role' => 'editor', 'active' => true])
        ->width(600)
        ->save("$outputDir/form-light.png");

    expect(file_exists("$outputDir/form-light.png"))->toBeTrue();
})->group('examples');

it('generates form dark example', function () use ($outputDir) {
    FilamentShot::form([
        TextInput::make('name')
            ->label('Full Name')
            ->placeholder('Enter your name'),
        TextInput::make('email')
            ->label('Email Address')
            ->placeholder('you@example.com'),
        Textarea::make('bio')
            ->label('Bio')
            ->rows(3),
        Checkbox::make('terms')->label('I agree to the terms'),
    ])
        ->state(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'bio' => 'Software engineer and open source contributor.', 'terms' => true])
        ->darkMode()
        ->width(600)
        ->save("$outputDir/form-dark.png");

    expect(file_exists("$outputDir/form-dark.png"))->toBeTrue();
})->group('examples');

it('generates table basic example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
            TextColumn::make('role'),
        ])
        ->records([
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'role' => 'Admin'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'role' => 'Editor'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'role' => 'Viewer'],
        ])
        ->heading('Team Members')
        ->striped()
        ->width(800)
        ->save("$outputDir/table-basic.png");

    expect(file_exists("$outputDir/table-basic.png"))->toBeTrue();
})->group('examples');

it('generates table with badges example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'Active' => 'success',
                    'Pending' => 'warning',
                    'Blocked' => 'danger',
                    default => 'primary',
                }),
            TextColumn::make('role')
                ->badge()
                ->color('info'),
        ])
        ->records([
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'status' => 'Active', 'role' => 'Admin'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'status' => 'Pending', 'role' => 'Editor'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'status' => 'Blocked', 'role' => 'Viewer'],
        ])
        ->heading('Users')
        ->width(900)
        ->save("$outputDir/table-badges.png");

    expect(file_exists("$outputDir/table-badges.png"))->toBeTrue();
})->group('examples');

it('generates table styled example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('code')->fontFamily(FontFamily::Mono),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'Shipped' => 'success',
                    'Processing' => 'warning',
                    default => 'primary',
                }),
        ])
        ->records([
            ['name' => 'Order #1001', 'code' => 'ORD-2024-1001', 'status' => 'Shipped'],
            ['name' => 'Order #1002', 'code' => 'ORD-2024-1002', 'status' => 'Processing'],
            ['name' => 'Order #1003', 'code' => 'ORD-2024-1003', 'status' => 'Pending'],
        ])
        ->heading('Orders')
        ->striped()
        ->width(800)
        ->save("$outputDir/table-styled.png");

    expect(file_exists("$outputDir/table-styled.png"))->toBeTrue();
})->group('examples');

it('generates table dark example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('email'),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'Active' => 'success',
                    'Inactive' => 'danger',
                    default => 'primary',
                }),
        ])
        ->records([
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'status' => 'Active'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'status' => 'Inactive'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'status' => 'Active'],
        ])
        ->heading('Users')
        ->darkMode()
        ->width(800)
        ->save("$outputDir/table-dark.png");

    expect(file_exists("$outputDir/table-dark.png"))->toBeTrue();
})->group('examples');

it('generates infolist example', function () use ($outputDir) {
    FilamentShot::infolist([
        Section::make('User Profile')
            ->schema([
                TextEntry::make('name')->label('Name'),
                TextEntry::make('email')->label('Email'),
                TextEntry::make('role')->label('Role'),
                TextEntry::make('joined')->label('Member Since'),
            ]),
    ])
        ->state([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role' => 'Administrator',
            'joined' => 'January 2024',
        ])
        ->width(600)
        ->save("$outputDir/infolist.png");

    expect(file_exists("$outputDir/infolist.png"))->toBeTrue();
})->group('examples');

it('generates infolist dark example', function () use ($outputDir) {
    FilamentShot::infolist([
        Section::make('User Profile')
            ->schema([
                TextEntry::make('name')->label('Name'),
                TextEntry::make('email')->label('Email'),
                TextEntry::make('role')->label('Role'),
                TextEntry::make('joined')->label('Member Since'),
            ]),
    ])
        ->state([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role' => 'Administrator',
            'joined' => 'January 2024',
        ])
        ->darkMode()
        ->width(600)
        ->save("$outputDir/infolist-dark.png");

    expect(file_exists("$outputDir/infolist-dark.png"))->toBeTrue();
})->group('examples');

it('generates stats example', function () use ($outputDir) {
    FilamentShot::stats([
        Stat::make('Total Users', '1,234')
            ->description('12% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success'),
        Stat::make('Revenue', '$56,789')
            ->description('8% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 3, 4, 5, 6, 3, 5, 8]),
        Stat::make('Orders', '456')
            ->description('3% decrease')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger'),
    ])
        ->width(1000)
        ->save("$outputDir/stats.png");

    expect(file_exists("$outputDir/stats.png"))->toBeTrue();
})->group('examples');

it('generates stats dark example', function () use ($outputDir) {
    FilamentShot::stats([
        Stat::make('Total Users', '1,234')
            ->description('12% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success'),
        Stat::make('Revenue', '$56,789')
            ->description('8% increase')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 3, 4, 5, 6, 3, 5, 8]),
        Stat::make('Orders', '456')
            ->description('3% decrease')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger'),
    ])
        ->darkMode()
        ->width(1000)
        ->save("$outputDir/stats-dark.png");

    expect(file_exists("$outputDir/stats-dark.png"))->toBeTrue();
})->group('examples');

it('generates table with icon column example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('email'),
            IconColumn::make('is_active')->boolean()->label('Active'),
        ])
        ->records([
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'is_active' => 1],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'is_active' => 0],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'is_active' => 1],
        ])
        ->heading('Users')
        ->width(800)
        ->save("$outputDir/table-icon-column.png");

    expect(file_exists("$outputDir/table-icon-column.png"))->toBeTrue();
})->group('examples');

it('generates form with section layout example', function () use ($outputDir) {
    FilamentShot::form([
        Section::make('Personal Information')
            ->schema([
                TextInput::make('name')
                    ->label('Full Name')
                    ->placeholder('Enter your name'),
                TextInput::make('email')
                    ->label('Email Address')
                    ->placeholder('you@example.com'),
            ]),
        Section::make('Settings')
            ->schema([
                Toggle::make('active')->label('Active'),
                Checkbox::make('notifications')->label('Enable notifications'),
            ]),
    ])
        ->state(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'active' => true, 'notifications' => true])
        ->width(600)
        ->save("$outputDir/form-section.png");

    expect(file_exists("$outputDir/form-section.png"))->toBeTrue();
})->group('examples');

it('generates form with grid layout example', function () use ($outputDir) {
    FilamentShot::form([
        Grid::make()
            ->columns(['default' => 2])
            ->schema([
                TextInput::make('first_name')
                    ->label('First Name')
                    ->placeholder('First'),
                TextInput::make('last_name')
                    ->label('Last Name')
                    ->placeholder('Last'),
            ]),
        Textarea::make('bio')
            ->label('Biography')
            ->rows(3),
    ])
        ->state(['first_name' => 'Jane', 'last_name' => 'Doe', 'bio' => 'Software engineer and open source contributor.'])
        ->width(600)
        ->save("$outputDir/form-grid.png");

    expect(file_exists("$outputDir/form-grid.png"))->toBeTrue();
})->group('examples');

it('generates form with additional field types example', function () use ($outputDir) {
    FilamentShot::form([
        Section::make('Event Details')
            ->schema([
                TextInput::make('event_name')
                    ->label('Event Name'),
                DatePicker::make('event_date')
                    ->label('Date'),
                Select::make('category')
                    ->label('Category')
                    ->options([
                        'conference' => 'Conference',
                        'workshop' => 'Workshop',
                        'meetup' => 'Meetup',
                    ]),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(2),
                Toggle::make('published')
                    ->label('Published'),
            ]),
    ])
        ->state([
            'event_name' => 'Laravel Meetup 2024',
            'event_date' => '2024-06-15',
            'category' => 'meetup',
            'description' => 'Monthly Laravel community gathering with talks and networking.',
            'published' => true,
        ])
        ->width(600)
        ->save("$outputDir/form-fields.png");

    expect(file_exists("$outputDir/form-fields.png"))->toBeTrue();
})->group('examples');

it('generates table with color column example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('hex'),
            ColorColumn::make('color'),
        ])
        ->records([
            ['name' => 'Primary', 'hex' => '#3b82f6', 'color' => '#3b82f6'],
            ['name' => 'Success', 'hex' => '#22c55e', 'color' => '#22c55e'],
            ['name' => 'Danger', 'hex' => '#ef4444', 'color' => '#ef4444'],
            ['name' => 'Warning', 'hex' => '#f59e0b', 'color' => '#f59e0b'],
        ])
        ->heading('Brand Colors')
        ->width(600)
        ->save("$outputDir/table-color-column.png");

    expect(file_exists("$outputDir/table-color-column.png"))->toBeTrue();
})->group('examples');

it('generates form with multi-select example', function () use ($outputDir) {
    FilamentShot::form([
        TextInput::make('name')
            ->label('Recipe Name'),
        Select::make('ingredients')
            ->label('Ingredients')
            ->multiple()
            ->options([
                'flour' => 'Flour',
                'sugar' => 'Sugar',
                'butter' => 'Butter',
                'eggs' => 'Eggs',
                'milk' => 'Milk',
                'vanilla' => 'Vanilla Extract',
            ]),
        Select::make('category')
            ->label('Category')
            ->options([
                'appetizer' => 'Appetizer',
                'main' => 'Main Course',
                'dessert' => 'Dessert',
            ]),
    ])
        ->state([
            'name' => 'Classic Vanilla Cake',
            'ingredients' => ['flour', 'sugar', 'butter', 'eggs', 'vanilla'],
            'category' => 'dessert',
        ])
        ->width(600)
        ->save("$outputDir/form-multi-select.png");

    expect(file_exists("$outputDir/form-multi-select.png"))->toBeTrue();
})->group('examples');
