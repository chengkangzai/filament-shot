---
name: filament-shot
description: Generate PNG screenshots of Filament UI components — forms, tables, infolists, stats, modals, notifications, and navigation — using Filament Shot.
---

# Filament Shot

## When to use this skill

Use when the user wants to:
- Capture a Filament form, table, infolist, stats widget, modal, or notification as a PNG image
- Generate screenshots for documentation, email previews, or UI testing
- Render Filament components outside of a browser or running application

## Key facts

- **No running app required** — Filament Shot renders standalone HTML and captures it with Browsershot/Puppeteer. No Livewire, no panel, no database.
- **Entry points**: `FilamentShot::form()`, `::table()`, `::infolist()`, `::stats()`, `::notification()`, `::navigation()`, `::modal()`
- **Output**: `->save($path)`, `->toBase64()`, `->toHtml()`, `->toResponse()`
- **Requires**: PHP 8.2+, Laravel 11+, Filament v4 or v5, Node.js 18+, Puppeteer (`npm install puppeteer`)

## Common patterns

### Capture a form with state

```php
use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

FilamentShot::form([
    TextInput::make('name')->label('Full Name'),
    Select::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive']),
])
->state(['name' => 'Jane Doe', 'status' => 'active'])
->width(800)
->save(storage_path('screenshots/form.png'));
```

### Capture a table

```php
use CCK\FilamentShot\FilamentShot;
use Filament\Tables\Columns\TextColumn;

FilamentShot::table()
    ->columns([
        TextColumn::make('name'),
        TextColumn::make('email'),
        TextColumn::make('status')->badge(),
    ])
    ->records(User::all()->toArray())  // or plain arrays
    ->heading('Users')
    ->striped()
    ->save(storage_path('screenshots/table.png'));
```

### Dark mode + custom width

```php
FilamentShot::form([...])
    ->darkMode()
    ->width(1280)
    ->save('form-dark.png');
```

### Return as base64 (e.g. for embedding in email)

```php
$base64 = FilamentShot::infolist([...])->state([...])->toBase64();
// use as: <img src="data:image/png;base64,{$base64}">
```

## Troubleshooting

- **Blank screenshot**: Puppeteer/Chrome may not be installed. Run `npm install puppeteer` and ensure Chrome is available.
- **Wrong font / styles**: Filament's theme CSS is auto-resolved from your vendor directory. Run `composer install` if styles look missing.
- **Timeout**: Increase via config: `filament-shot.browsershot.timeout` (default: 60 seconds).
- **Custom Chrome path**: Set `filament-shot.browsershot.chrome_path` in config.
- **CI/no-sandbox**: Set `filament-shot.browsershot.no_sandbox` to `true` for CI environments.
