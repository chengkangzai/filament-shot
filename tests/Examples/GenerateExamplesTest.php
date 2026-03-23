<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

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

it('generates table with record actions example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('email'),
            TextColumn::make('role')
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'Admin' => 'danger',
                    'Editor' => 'warning',
                    default => 'primary',
                }),
        ])
        ->records([
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'role' => 'Admin'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'role' => 'Editor'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'role' => 'Viewer'],
        ])
        ->recordActions([
            Action::make('perks')->label('Perks')->icon('heroicon-o-plus'),
            Action::make('automations')->label('Automations')->icon('heroicon-o-plus'),
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->heading('Team Members')
        ->width(900)
        ->save("$outputDir/table-record-actions.png");

    expect(file_exists("$outputDir/table-record-actions.png"))->toBeTrue();
})->group('examples');

it('generates form with open select dropdown example', function () use ($outputDir) {
    FilamentShot::form([
        TextInput::make('name')
            ->label('Customer Name'),
        Select::make('status')
            ->label('Status')
            ->options([
                'lead' => 'Lead',
                'open' => 'Open',
                'replied' => 'Replied',
                'opportunity' => 'Opportunity',
                'blocked' => 'Blocked',
                'do_not_contact' => 'Do Not Contact',
            ]),
    ])
        ->state(['name' => 'John Doe', 'status' => 'blocked'])
        ->openFields(['status'])
        ->width(500)
        ->save("$outputDir/form-select-open.png");

    expect(file_exists("$outputDir/form-select-open.png"))->toBeTrue();
})->group('examples');

it('generates table with labeled actions example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('email'),
        ])
        ->records([
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com'],
        ])
        ->recordActions([
            Action::make('perks')->label('Perks')->icon('heroicon-o-gift'),
            Action::make('automations')->label('Automations')->icon('heroicon-o-cog-6-tooth'),
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->labeledActions()
        ->heading('Team Members')
        ->width(900)
        ->save("$outputDir/table-labeled-actions.png");

    expect(file_exists("$outputDir/table-labeled-actions.png"))->toBeTrue();
})->group('examples');

it('generates table with bulk actions example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('email'),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'Active' => 'success',
                    'Blocked' => 'danger',
                    default => 'primary',
                }),
        ])
        ->records([
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'status' => 'Active'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'status' => 'Blocked'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'status' => 'Active'],
            ['name' => 'Diana Prince', 'email' => 'diana@example.com', 'status' => 'Active'],
        ])
        ->bulkActions([
            BulkAction::make('change_status')
                ->label('Change Status')
                ->icon('heroicon-o-bell')
                ->color('success'),
            BulkAction::make('delete')
                ->label('Delete')
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ])
        ->selectedRows([0, 2])
        ->heading('Customers')
        ->width(900)
        ->save("$outputDir/table-bulk-actions.png");

    expect(file_exists("$outputDir/table-bulk-actions.png"))->toBeTrue();
})->group('examples');

it('generates navigation example', function () use ($outputDir) {
    FilamentShot::navigation()
        ->items([
            NavigationItem::make('Dashboard')
                ->icon('heroicon-o-home'),
            NavigationGroup::make('Content')
                ->items([
                    NavigationItem::make('Posts')
                        ->icon('heroicon-o-document-text')
                        ->isActiveWhen(fn () => true)
                        ->badge('24', 'success'),
                    NavigationItem::make('Pages')
                        ->icon('heroicon-o-document'),
                    NavigationItem::make('Categories')
                        ->icon('heroicon-o-tag'),
                ]),
            NavigationGroup::make('Shop')
                ->items([
                    NavigationItem::make('Products')
                        ->icon('heroicon-o-shopping-bag'),
                    NavigationItem::make('Orders')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->badge('3', 'danger'),
                    NavigationItem::make('Customers')
                        ->icon('heroicon-o-users'),
                ]),
            NavigationGroup::make('Settings')
                ->items([
                    NavigationItem::make('General')
                        ->icon('heroicon-o-cog-6-tooth'),
                    NavigationItem::make('Roles & Permissions')
                        ->icon('heroicon-o-shield-check'),
                ]),
        ])
        ->heading('Admin Panel')
        ->width(320)
        ->save("$outputDir/navigation.png");

    expect(file_exists("$outputDir/navigation.png"))->toBeTrue();
})->group('examples');

it('generates form with rich editor example', function () use ($outputDir) {
    FilamentShot::form([
        TextInput::make('title')
            ->label('Title'),
        RichEditor::make('content')
            ->label('Content'),
    ])
        ->state([
            'title' => 'Getting Started with Filament',
        ])
        ->width(700)
        ->save("$outputDir/form-rich-editor.png");

    expect(file_exists("$outputDir/form-rich-editor.png"))->toBeTrue();
})->group('examples');

it('generates form with markdown editor example', function () use ($outputDir) {
    FilamentShot::form([
        TextInput::make('title')
            ->label('Title'),
        MarkdownEditor::make('body')
            ->label('Body'),
    ])
        ->state([
            'title' => 'Release Notes v2.0',
        ])
        ->width(700)
        ->save("$outputDir/form-markdown-editor.png");

    expect(file_exists("$outputDir/form-markdown-editor.png"))->toBeTrue();
})->group('examples');

it('generates form with tabs example', function () use ($outputDir) {
    FilamentShot::form([
        Tabs::make('Settings')
            ->tabs([
                Tab::make('General')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Site Name'),
                        TextInput::make('site_url')
                            ->label('Site URL'),
                        Toggle::make('maintenance')
                            ->label('Maintenance Mode'),
                    ]),
                Tab::make('Notifications')
                    ->icon('heroicon-o-bell')
                    ->schema([
                        Toggle::make('email_notifications')
                            ->label('Email Notifications'),
                        Toggle::make('sms_notifications')
                            ->label('SMS Notifications'),
                    ]),
                Tab::make('Security')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Toggle::make('two_factor')
                            ->label('Two Factor Authentication'),
                        Select::make('session_lifetime')
                            ->label('Session Lifetime')
                            ->options([
                                '30' => '30 minutes',
                                '60' => '1 hour',
                                '120' => '2 hours',
                                '480' => '8 hours',
                            ]),
                    ]),
            ]),
    ])
        ->state([
            'site_name' => 'My Application',
            'site_url' => 'https://myapp.example.com',
            'maintenance' => false,
            'email_notifications' => true,
            'sms_notifications' => false,
            'two_factor' => true,
            'session_lifetime' => '120',
        ])
        ->width(700)
        ->save("$outputDir/form-tabs.png");

    expect(file_exists("$outputDir/form-tabs.png"))->toBeTrue();
})->group('examples');

it('generates form with color picker example', function () use ($outputDir) {
    FilamentShot::form([
        TextInput::make('brand_name')
            ->label('Brand Name'),
        ColorPicker::make('primary_color')
            ->label('Primary Color'),
        ColorPicker::make('secondary_color')
            ->label('Secondary Color'),
    ])
        ->state([
            'brand_name' => 'Acme Corp',
            'primary_color' => '#3b82f6',
            'secondary_color' => '#22c55e',
        ])
        ->width(600)
        ->save("$outputDir/form-color-picker.png");

    expect(file_exists("$outputDir/form-color-picker.png"))->toBeTrue();
})->group('examples');

it('generates form with wizard example', function () use ($outputDir) {
    FilamentShot::form([
        Wizard::make([
            Step::make('Account')
                ->description('Your credentials')
                ->schema([
                    TextInput::make('email')
                        ->label('Email'),
                    TextInput::make('password')
                        ->label('Password'),
                ]),
            Step::make('Profile')
                ->description('Personal info')
                ->schema([
                    TextInput::make('name')
                        ->label('Full Name'),
                ]),
            Step::make('Review')
                ->description('Confirm details')
                ->schema([
                    Toggle::make('terms')
                        ->label('I accept the terms'),
                ]),
        ]),
    ])
        ->state(['email' => 'jane@example.com', 'password' => 'secret123'])
        ->width(900)
        ->save("$outputDir/form-wizard.png");

    expect(file_exists("$outputDir/form-wizard.png"))->toBeTrue();
})->group('examples');

it('generates table with reorderable example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('email'),
            TextColumn::make('role'),
        ])
        ->records([
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'role' => 'Admin'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'role' => 'Editor'],
            ['name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'role' => 'Viewer'],
        ])
        ->reorderable()
        ->heading('Reorderable Table')
        ->width(800)
        ->save("$outputDir/table-reorderable.png");

    expect(file_exists("$outputDir/table-reorderable.png"))->toBeTrue();
})->group('examples');

it('generates table with action group example', function () use ($outputDir) {
    FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
            TextColumn::make('email'),
            TextColumn::make('role'),
        ])
        ->records([
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'role' => 'Admin'],
        ])
        ->recordActions([
            Action::make('view')->label('View')->icon('heroicon-o-eye'),
            ActionGroup::make([
                Action::make('edit')->label('Edit')->icon('heroicon-o-pencil-square'),
                Action::make('duplicate')->label('Duplicate')->icon('heroicon-o-document-duplicate'),
                Action::make('delete')->label('Delete')->icon('heroicon-o-trash')->color('danger'),
            ]),
        ])
        ->heading('Action Group Dropdown')
        ->width(900)
        ->height(350)
        ->save("$outputDir/table-action-group.png");

    expect(file_exists("$outputDir/table-action-group.png"))->toBeTrue();
})->group('examples');

it('generates notification example', function () use ($outputDir) {
    FilamentShot::notification()
        ->title('Status Updated')
        ->body('The customer status has been changed to Blocked.')
        ->success()
        ->width(400)
        ->save("$outputDir/notification.png");

    expect(file_exists("$outputDir/notification.png"))->toBeTrue();
})->group('examples');

it('generates view blade string example', function () use ($outputDir) {
    FilamentShot::blade(<<<'BLADE'
<div style="display:flex; flex-direction:column; gap:16px; padding:24px; font-family:sans-serif; font-size:14px; color:#111827;">

    {{-- Tier card --}}
    <div style="border-radius:12px; padding:24px; background-color:#6B728014; border-left:4px solid #6B7280;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
            <div>
                <p style="margin:0 0 4px; font-size:12px; font-weight:500; color:#6B7280;">Current Tier</p>
                <h3 style="margin:0; font-size:24px; font-weight:700; color:#6B7280;">Silver</h3>
            </div>
            <div style="display:flex; gap:32px;">
                <div style="text-align:right;">
                    <p style="margin:0 0 4px; font-size:12px; font-weight:500; color:#6B7280;">Tier Points</p>
                    <p style="margin:0; font-size:24px; font-weight:700; color:#111827;">600</p>
                </div>
                <div style="text-align:right;">
                    <p style="margin:0 0 4px; font-size:12px; font-weight:500; color:#6B7280;">Redeemable Points</p>
                    <p style="margin:0; font-size:24px; font-weight:700; color:#111827;">500</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress bar --}}
    <div style="border-radius:12px; border:1px solid #e5e7eb; background:#fff; padding:16px;">
        <h4 style="margin:0 0 12px; font-size:13px; font-weight:600; color:#374151;">Tier Progress</h4>
        <div style="height:12px; width:100%; border-radius:999px; background:#e5e7eb; margin-bottom:10px;">
            <div style="height:12px; width:60%; border-radius:999px; background:#6B7280;"></div>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:12px; color:#6B7280;">
            <span style="color:#CD7F32;">Bronze (0)</span>
            <span style="color:#6B7280; font-weight:600;">Silver (500) ✓</span>
            <span>Gold (1,000)</span>
        </div>
    </div>

    {{-- Timeline table --}}
    <div style="border-radius:12px; border:1px solid #e5e7eb; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="text-align:left; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; color:#6B7280; letter-spacing:0.05em;">Date</th>
                    <th style="text-align:left; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; color:#6B7280; letter-spacing:0.05em;">Event</th>
                    <th style="text-align:right; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; color:#6B7280; letter-spacing:0.05em;">Points</th>
                    <th style="text-align:right; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; color:#6B7280; letter-spacing:0.05em;">Total</th>
                    <th style="text-align:left; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; color:#6B7280; letter-spacing:0.05em;">Tier</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:10px 16px; color:#6B7280;">2026-01-15</td>
                    <td style="padding:10px 16px;">Purchase</td>
                    <td style="padding:10px 16px; text-align:right; font-weight:500; color:#16a34a;">+200</td>
                    <td style="padding:10px 16px; text-align:right;">200</td>
                    <td style="padding:10px 16px; color:#CD7F32;">Bronze</td>
                </tr>
                <tr style="border-top:1px solid #f3f4f6; background:#f0fdf4;">
                    <td style="padding:10px 16px; color:#6B7280;">2026-02-10</td>
                    <td style="padding:10px 16px; font-weight:500; color:#15803d;">↑ Tier Upgrade: Bronze → Silver</td>
                    <td style="padding:10px 16px;"></td>
                    <td style="padding:10px 16px;"></td>
                    <td style="padding:10px 16px; color:#6B7280;">Silver</td>
                </tr>
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:10px 16px; color:#6B7280;">2026-02-10</td>
                    <td style="padding:10px 16px;">Purchase</td>
                    <td style="padding:10px 16px; text-align:right; font-weight:500; color:#16a34a;">+350</td>
                    <td style="padding:10px 16px; text-align:right;">550</td>
                    <td style="padding:10px 16px; color:#6B7280;">Silver</td>
                </tr>
                <tr style="border-top:1px solid #f3f4f6;">
                    <td style="padding:10px 16px; color:#6B7280;">2026-02-20</td>
                    <td style="padding:10px 16px;">Redemption</td>
                    <td style="padding:10px 16px; text-align:right; font-weight:500; color:#dc2626;">-50</td>
                    <td style="padding:10px 16px; text-align:right;">600</td>
                    <td style="padding:10px 16px; color:#6B7280;">Silver</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
BLADE)
        ->width(900)
        ->save("$outputDir/view-blade.png");

    expect(file_exists("$outputDir/view-blade.png"))->toBeTrue();
})->group('examples');

it('generates form with phone input (3rd-party plugin) example', function () use ($outputDir) {
    FilamentShot::form([
        TextInput::make('name')->label('Full Name'),
        PhoneInput::make('phone')->label('Phone Number'),
        TextInput::make('email')->label('Email'),
    ])
        ->state([
            'name' => 'Jane Doe',
            'phone' => '+60123456789',
            'email' => 'jane@example.com',
        ])
        ->width(600)
        ->save("$outputDir/form-phone-input.png");

    expect(file_exists("$outputDir/form-phone-input.png"))->toBeTrue();
})->group('examples');

it('generates header actions example', function () use ($outputDir) {
    FilamentShot::headerActions([
        Action::make('simulate')
            ->label('Simulate')
            ->icon('heroicon-o-play')
            ->color('primary'),
        Action::make('export')
            ->label('Export')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray'),
        Action::make('create')
            ->label('Create')
            ->icon('heroicon-o-plus')
            ->color('primary'),
    ])
        ->pageTitle('Tier Configurations')
        ->breadcrumbs(['Settings', 'Tier Configurations'])
        ->width(900)
        ->save("$outputDir/header-actions.png");

    expect(file_exists("$outputDir/header-actions.png"))->toBeTrue();
})->group('examples');

it('generates form with builder example', function () use ($outputDir) {
    FilamentShot::form([
        Builder::make('content')
            ->label('Page Content')
            ->blocks([
                Block::make('heading')
                    ->label('Heading')
                    ->schema([
                        TextInput::make('text')->label('Heading Text'),
                        Select::make('level')
                            ->label('Level')
                            ->options(['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3'])
                            ->default('h2'),
                    ]),
                Block::make('paragraph')
                    ->label('Paragraph')
                    ->schema([
                        Textarea::make('content')
                            ->label('Content')
                            ->rows(3),
                    ]),
            ]),
    ])
        ->state([
            'content' => [
                ['type' => 'heading', 'data' => ['text' => 'Welcome to Our Platform', 'level' => 'h1']],
                ['type' => 'paragraph', 'data' => ['content' => 'We help teams build better software together.']],
                ['type' => 'heading', 'data' => ['text' => 'Key Features', 'level' => 'h2']],
            ],
        ])
        ->width(700)
        ->save("$outputDir/form-builder.png");

    expect(file_exists("$outputDir/form-builder.png"))->toBeTrue();
})->group('examples');
