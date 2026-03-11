<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;

it('renders table with columns and records', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->records([
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('Name')
        ->toContain('Email')
        ->toContain('Alice')
        ->toContain('alice@example.com')
        ->toContain('Bob')
        ->toContain('bob@example.com')
        ->toContain('fi-ta-ctn');
});

it('renders column headers', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name')->label('Full Name'),
        ])
        ->records([])
        ->toHtml();

    expect($html)
        ->toContain('Full Name')
        ->toContain('fi-ta-header-cell');
});

it('renders empty state', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([])
        ->toHtml();

    expect($html)->toContain('No records found');
});

it('supports striped rows', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([
            ['name' => 'First'],
            ['name' => 'Second'],
        ])
        ->striped()
        ->toHtml();

    expect($html)->toContain('fi-striped');
});

it('renders table with heading', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([])
        ->heading('Users List')
        ->toHtml();

    expect($html)
        ->toContain('Users List')
        ->toContain('fi-ta-header');
});

it('renders badge column from TextColumn', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('status')->badge(),
        ])
        ->records([
            ['status' => 'Active'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('Active')
        ->toContain('fi-badge');
});

it('renders badge column with color from TextColumn', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('status')->badge()->color('danger'),
        ])
        ->records([
            ['status' => 'Blocked'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('Blocked')
        ->toContain('fi-badge')
        ->toContain('fi-color-danger');
});

it('renders badge column from array definition', function () {
    $html = FilamentShot::table()
        ->columns([
            ['name' => 'status', 'badge' => true, 'color' => 'success'],
        ])
        ->records([
            ['status' => 'Active'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('Active')
        ->toContain('fi-badge')
        ->toContain('fi-color-success');
});

it('renders non-badge columns as plain text', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
        ])
        ->records([
            ['name' => 'Alice'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('Alice')
        ->toContain('fi-ta-text-item')
        ->not->toContain('class="fi-badge');
});

it('renders badge with default primary color when no color specified', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('status')->badge(),
        ])
        ->records([
            ['status' => 'Pending'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-badge')
        ->toContain('fi-color-primary');
});

it('renders array column with color callback for dynamic per-record badge colors', function () {
    $html = FilamentShot::table()
        ->columns([
            [
                'name' => 'status',
                'badge' => true,
                'color' => fn (string $state): string => match ($state) {
                    'Blocked' => 'danger',
                    'Active' => 'success',
                    default => 'primary',
                },
            ],
        ])
        ->records([
            ['status' => 'Blocked'],
            ['status' => 'Active'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-badge')
        ->toContain('fi-color-danger')
        ->toContain('fi-color-success');
});

it('renders mono fontFamily from TextColumn', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('code')->fontFamily(FontFamily::Mono),
        ])
        ->records([
            ['code' => 'abc123'],
        ])
        ->toHtml();

    expect($html)->toContain('fi-font-mono');
});

it('renders mono fontFamily from array definition', function () {
    $html = FilamentShot::table()
        ->columns([
            ['name' => 'code', 'fontFamily' => 'mono'],
        ])
        ->records([
            ['code' => 'abc123'],
        ])
        ->toHtml();

    expect($html)->toContain('fi-font-mono');
});

it('renders custom global font in renderHtml output', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([])
        ->font('JetBrains Mono')
        ->renderHtml();

    expect($html)
        ->toContain('JetBrains Mono')
        ->toContain('JetBrains+Mono');
});

it('renders TextColumn with weight Bold', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name')->weight(FontWeight::Bold),
        ])
        ->records([['name' => 'Alice']])
        ->toHtml();

    expect($html)->toContain('fi-font-bold');
});

it('renders TextColumn with alignment End', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('price')->alignment(Alignment::End),
        ])
        ->records([['price' => '99.99']])
        ->toHtml();

    expect($html)->toContain('fi-align-end');
});

it('renders TextColumn with wrap', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('notes')->wrap(),
        ])
        ->records([['notes' => 'Long text']])
        ->toHtml();

    expect($html)->toContain('fi-wrapped');
});

it('renders TextColumn with size Large', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('title')->size(TextSize::Large),
        ])
        ->records([['title' => 'Hello']])
        ->toHtml();

    expect($html)->toContain('fi-size-lg');
});

it('renders record actions as icon buttons', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
        ])
        ->records([
            ['name' => 'Alice'],
        ])
        ->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-ta-actions')
        ->toContain('fi-icon-btn');
});

it('renders record actions with custom icon and label', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
        ])
        ->records([
            ['name' => 'Alice'],
        ])
        ->recordActions([
            Action::make('perks')->label('Perks')->icon('heroicon-o-plus'),
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-icon-btn')
        ->toContain('title="Perks"');
});

it('renders record actions from array definitions', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
        ])
        ->records([
            ['name' => 'Alice'],
        ])
        ->recordActions([
            ['icon' => 'heroicon-o-pencil-square', 'label' => 'Edit', 'color' => 'primary'],
            ['icon' => 'heroicon-o-trash', 'label' => 'Delete', 'color' => 'danger'],
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-ta-actions')
        ->toContain('fi-icon-btn')
        ->toContain('title="Edit"')
        ->toContain('title="Delete"');
});

it('renders table without actions column when no actions', function () {
    $html = FilamentShot::table()
        ->columns([
            TextColumn::make('name'),
        ])
        ->records([
            ['name' => 'Alice'],
        ])
        ->toHtml();

    expect($html)->not->toContain('<div class="fi-ta-actions"');
});

it('injects OKLCH color CSS variables', function () {
    $html = FilamentShot::table()
        ->columns([TextColumn::make('name')])
        ->records([])
        ->renderHtml();

    expect($html)
        ->toContain('--danger-50:')
        ->toContain('--success-50:')
        ->toContain('--primary-50:')
        ->toContain('--gray-50:')
        ->toContain('oklch');
});
