<?php

use CCK\FilamentShot\Support\ColumnAdapter;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;

// --- Array source tests ---

it('reads name from array source', function () {
    $adapter = new ColumnAdapter(['name' => 'email']);

    expect($adapter['name'])->toBe('email');
});

it('auto-generates label from name when label is missing', function () {
    $adapter = new ColumnAdapter(['name' => 'first_name']);

    expect($adapter['label'])->toBe('First Name');
});

it('uses explicit label when provided', function () {
    $adapter = new ColumnAdapter(['name' => 'email', 'label' => 'E-Mail']);

    expect($adapter['label'])->toBe('E-Mail');
});

it('reads badge from array source', function () {
    $adapter = new ColumnAdapter(['name' => 'status', 'badge' => true]);

    expect($adapter['badge'])->toBeTrue();
});

it('resolves static color from array source', function () {
    $adapter = new ColumnAdapter(['name' => 'status', 'color' => 'danger']);

    expect($adapter->resolve('color', null, 'Active'))->toBe('danger');
});

it('resolves callable color with state from array source', function () {
    $adapter = new ColumnAdapter([
        'name' => 'status',
        'color' => fn (string $state) => match ($state) {
            'Active' => 'success',
            'Blocked' => 'danger',
            default => 'primary',
        },
    ]);

    expect($adapter->resolve('color', null, 'Active'))->toBe('success');
    expect($adapter->resolve('color', null, 'Blocked'))->toBe('danger');
});

it('resolves fontFamily from array source', function () {
    $adapter = new ColumnAdapter(['name' => 'code', 'fontFamily' => 'mono']);

    expect($adapter->resolve('fontFamily', null, 'abc'))->toBe('mono');
});

it('returns default for missing keys in array source', function () {
    $adapter = new ColumnAdapter(['name' => 'status']);

    expect($adapter->resolve('color', 'fallback', 'Active'))->toBe('fallback');
});

// --- Object source tests ---

it('reads name from TextColumn', function () {
    $column = TextColumn::make('email');
    $adapter = new ColumnAdapter($column);

    expect($adapter['name'])->toBe('email');
});

it('reads label from TextColumn', function () {
    $column = TextColumn::make('email')->label('E-Mail Address');
    $adapter = new ColumnAdapter($column);

    expect($adapter['label'])->toBe('E-Mail Address');
});

it('reads badge from TextColumn', function () {
    $column = TextColumn::make('status')->badge();
    $adapter = new ColumnAdapter($column);

    expect($adapter['badge'])->toBeTrue();
});

it('resolves fontFamily enum from TextColumn', function () {
    $column = TextColumn::make('code')->fontFamily(FontFamily::Mono);
    $adapter = new ColumnAdapter($column);

    expect($adapter->resolve('fontFamily', null, 'abc'))->toBe('mono');
});

it('resolves color with state from TextColumn', function () {
    $column = TextColumn::make('status')
        ->badge()
        ->color(fn (string $state) => match ($state) {
            'Active' => 'success',
            default => 'primary',
        });
    $adapter = new ColumnAdapter($column);

    expect($adapter->resolve('color', null, 'Active'))->toBe('success');
    expect($adapter->resolve('color', null, 'Other'))->toBe('primary');
});

it('returns default for unknown property on TextColumn', function () {
    $column = TextColumn::make('name');
    $adapter = new ColumnAdapter($column);

    expect($adapter->resolve('nonExistentProperty', 'default_val'))->toBe('default_val');
});

it('resolves wrap via canWrap on TextColumn', function () {
    $column = TextColumn::make('notes')->wrap();
    $adapter = new ColumnAdapter($column);

    expect($adapter->resolve('wrap'))->toBeTrue();
});

// --- renderCell tests ---

it('renders cell with TextColumn via toEmbeddedHtml', function () {
    $column = TextColumn::make('name');
    $adapter = new ColumnAdapter($column);

    $html = $adapter->renderCell(['name' => 'Alice']);

    expect($html)
        ->toContain('Alice')
        ->toContain('fi-ta-text');
});

it('renders cell with TextColumn badge and color', function () {
    $column = TextColumn::make('status')->badge()->color('danger');
    $adapter = new ColumnAdapter($column);

    $html = $adapter->renderCell(['status' => 'Blocked']);

    expect($html)
        ->toContain('Blocked')
        ->toContain('fi-badge')
        ->toContain('fi-color-danger');
});

it('renders cell with TextColumn fontFamily Mono', function () {
    $column = TextColumn::make('code')->fontFamily(FontFamily::Mono);
    $adapter = new ColumnAdapter($column);

    $html = $adapter->renderCell(['code' => 'abc123']);

    expect($html)
        ->toContain('abc123')
        ->toContain('fi-font-mono');
});

it('renders cell with TextColumn weight Bold', function () {
    $column = TextColumn::make('name')->weight(\Filament\Support\Enums\FontWeight::Bold);
    $adapter = new ColumnAdapter($column);

    $html = $adapter->renderCell(['name' => 'Alice']);

    expect($html)
        ->toContain('Alice')
        ->toContain('fi-font-bold');
});

it('renders cell from array source', function () {
    $adapter = new ColumnAdapter(['name' => 'email']);

    $html = $adapter->renderCell(['email' => 'test@example.com']);

    expect($html)
        ->toContain('test@example.com')
        ->toContain('fi-ta-text')
        ->toContain('fi-ta-text-item');
});

it('renders cell from array source with badge and color callback', function () {
    $adapter = new ColumnAdapter([
        'name' => 'status',
        'badge' => true,
        'color' => fn (string $state) => match ($state) {
            'Active' => 'success',
            default => 'danger',
        },
    ]);

    $html = $adapter->renderCell(['status' => 'Active']);

    expect($html)
        ->toContain('Active')
        ->toContain('fi-badge')
        ->toContain('fi-color-success');
});

it('renders array source with fontFamily class', function () {
    $adapter = new ColumnAdapter(['name' => 'code', 'fontFamily' => 'mono']);

    $html = $adapter->renderCell(['code' => 'abc']);

    expect($html)->toContain('fi-font-mono');
});

it('renders array source with callable fontFamily', function () {
    $adapter = new ColumnAdapter([
        'name' => 'code',
        'fontFamily' => fn () => 'serif',
    ]);

    $html = $adapter->renderCell(['code' => 'abc']);

    expect($html)->toContain('fi-font-serif');
});

// --- renderCell edge cases ---

it('does not produce trailing whitespace in class attributes', function () {
    $adapter = new ColumnAdapter(['name' => 'email']);

    $html = $adapter->renderCell(['email' => 'test@example.com']);

    expect($html)->not->toMatch('/class="[^"]*\s"/');
});

it('does not produce trailing whitespace in badge class attributes', function () {
    $adapter = new ColumnAdapter(['name' => 'status', 'badge' => true, 'color' => 'success']);

    $html = $adapter->renderCell(['status' => 'Active']);

    expect($html)->not->toMatch('/class="[^"]*\s"/');
});

it('escapes HTML entities in cell values', function () {
    $adapter = new ColumnAdapter(['name' => 'content']);

    $html = $adapter->renderCell(['content' => '<script>alert("xss")</script>']);

    expect($html)
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;');
});

it('renders missing record key as empty cell', function () {
    $adapter = new ColumnAdapter(['name' => 'missing_field']);

    $html = $adapter->renderCell(['other_field' => 'value']);

    expect($html)->toContain('fi-ta-text-item');
});

it('renders numeric record value correctly', function () {
    $adapter = new ColumnAdapter(['name' => 'price']);

    $html = $adapter->renderCell(['price' => 42.5]);

    expect($html)->toContain('42.5');
});

it('applies fontFamily to outer div for array badge columns', function () {
    $adapter = new ColumnAdapter([
        'name' => 'code',
        'badge' => true,
        'color' => 'primary',
        'fontFamily' => 'mono',
    ]);

    $html = $adapter->renderCell(['code' => 'ABC']);

    // fi-font-mono should be on the outer div with fi-ta-text-item,
    // not on the inner fi-badge span (Filament CSS targets .fi-ta-text-item.fi-font-mono)
    expect($html)
        ->toContain('fi-ta-text-item')
        ->toMatch('/fi-ta-text-item[^"]*fi-font-mono/');
});
