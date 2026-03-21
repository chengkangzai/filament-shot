---
name: filament-shot
description: Generate screenshots of Filament UI components using the filament-shot package. Use when the developer asks to screenshot, capture, render, or preview a Filament form, table, infolist, stats widget, modal, notification, or navigation — or when they want to generate PNG images of Filament components programmatically. Trigger phrases include "screenshot", "capture form", "render table as image", "generate PNG", "filament-shot", "take a screenshot of", "create an image of my form".
---

# Filament Shot — Screenshot Generator

You help developers generate correct filament-shot code to capture Filament UI components as PNG screenshots.

## Key facts (internalize these)

- **No running app required** — filament-shot renders standalone HTML and captures via Browsershot/Puppeteer. No Livewire, no panel, no database connection needed.
- **Install**: `composer require chengkangzai/filament-shot` + `npm install puppeteer`
- **Entry point**: `CCK\FilamentShot\FilamentShot` static class
- **All methods are fluent** (chainable), ending with an output method

## Entry points

```php
FilamentShot::form([...components...])    // Filament form fields
FilamentShot::table()                     // Table with ->columns() + ->records()
FilamentShot::infolist([...entries...])   // Infolist entries
FilamentShot::stats([...stats...])        // StatsOverviewWidget stats
FilamentShot::notification()              // Toast notification
FilamentShot::navigation()                // Sidebar navigation
FilamentShot::modal([...components...])   // Standalone modal dialog
```

## Output methods

```php
->save('/absolute/path/to/file.png')   // Save to disk
->toBase64()                            // PNG as base64 string (for embedding)
->toHtml()                              // Rendered HTML — USE THIS FOR DEBUGGING
->toResponse()                          // HTTP response (for routes/controllers)
```

## Common options (all renderers)

```php
->width(1280)          // viewport width in px (default: 800)
->height(720)          // fixed height — omit to auto-fit to content
->deviceScale(2)       // HiDPI / retina (default: 1)
->darkMode()           // enable dark mode
->primaryColor('#f59e0b')  // custom primary accent color
->font('Inter')        // Google Fonts family name
->css('.my-class { color: red; }')  // inject extra CSS
->highlight(['fieldName'])          // draw attention border around specific fields
```

## Forms

```php
use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

FilamentShot::form([
    TextInput::make('name')->label('Full Name'),
    Select::make('role')->options(['admin' => 'Admin', 'editor' => 'Editor']),
])
->state(['name' => 'Jane Doe', 'role' => 'admin'])  // populate field values
->width(800)
->save(storage_path('screenshots/form.png'));
```

**Supported fields:** `TextInput`, `Select`, `Textarea`, `Toggle`, `Checkbox`, `Radio`, `DatePicker`, `DateTimePicker`, `FileUpload`, `ColorPicker`, `TagsInput`, `KeyValue`, `RichEditor`, `MarkdownEditor`, `Repeater`

**Layout components:** `Section`, `Grid`, `Fieldset`, `Tabs`, `Wizard`

**Form extras:**
- `->openFields(['fieldName'])` — render a Select dropdown in open/expanded state
- `->activeTab(2)` — render a specific tab as active (1-indexed)
- `->startOnStep(2)` — render a Wizard starting from a specific step
- `->modal('Heading')` — wrap form in a modal dialog
- `->modalDescription(...)`, `->modalColor('danger')`, `->modalSubmitLabel('Delete')`

## Tables

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;

FilamentShot::table()
    ->columns([
        TextColumn::make('name'),
        TextColumn::make('status')
            ->badge()
            ->color(fn ($state) => match($state) {
                'Active' => 'success',
                'Blocked' => 'danger',
                default => 'gray',
            }),
    ])
    ->records([
        ['name' => 'Alice', 'status' => 'Active'],
        ['name' => 'Bob', 'status' => 'Blocked'],
    ])
    ->heading('Users')
    ->striped()
    ->save(storage_path('screenshots/table.png'));
```

**Table extras:**
- `->recordActions([Action::make('edit'), ActionGroup::make([...])])` — per-row action buttons
- `->bulkActions([BulkAction::make('delete')])` + `->selectedRows([0, 2])` — bulk action toolbar with checked rows
- `->reorderable()` — show drag handles
- `->labeledActions()` — show action labels alongside icons

## Infolists

```php
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

FilamentShot::infolist([
    Section::make('Profile')->schema([
        TextEntry::make('name')->label('Name'),
        TextEntry::make('email')->label('Email'),
    ]),
])
->state(['name' => 'Jane Doe', 'email' => 'jane@example.com'])
->save(storage_path('screenshots/infolist.png'));
```

## Stats

```php
use Filament\Widgets\StatsOverviewWidget\Stat;

FilamentShot::stats([
    Stat::make('Total Users', '1,234')
        ->description('12% increase')
        ->descriptionIcon('heroicon-m-arrow-trending-up')
        ->color('success')
        ->chart([4, 5, 6, 7, 8, 7, 9]),
    Stat::make('Revenue', '$56,789')->description('8% increase'),
])
->width(900)
->save(storage_path('screenshots/stats.png'));
```

## Notifications

```php
FilamentShot::notification()
    ->title('Order Confirmed')
    ->body('Your order #1234 has been placed.')
    ->success()   // or ->warning() ->danger() ->info()
    ->width(400)
    ->save(storage_path('screenshots/notification.png'));
```

## Navigation

```php
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

FilamentShot::navigation()
    ->items([
        NavigationItem::make('Dashboard')->icon('heroicon-o-home'),
        NavigationGroup::make('Content')->items([
            NavigationItem::make('Posts')
                ->icon('heroicon-o-document-text')
                ->isActiveWhen(fn () => true)  // highlight as active
                ->badge('24', 'success'),
        ]),
    ])
    ->heading('My App')
    ->width(320)
    ->save(storage_path('screenshots/nav.png'));
```

## Gotchas & troubleshooting

- **Blank screenshot**: Run `npm install puppeteer` and ensure Chrome/Chromium is installed
- **Field values not showing**: Use `->state(['fieldName' => $value])` — field values are NOT populated automatically
- **Debugging layout issues**: Use `->toHtml()` and inspect in a browser before saving PNG
- **Timeout in CI**: Set `FILAMENT_SHOT_NO_SANDBOX=true` env var, or add to config: `'no_sandbox' => true`
- **Auto-fit height**: Height auto-fits to content by default — only set `->height()` if you need a fixed size
- **Dark screenshots**: Chain `->darkMode()` before the output method

## How to help the developer

When a developer asks to generate a screenshot:

1. Identify which renderer they need (form? table? infolist? stats?)
2. Use their existing Filament component definitions if visible in the codebase — don't rewrite them
3. Ask about state/records if not provided — screenshots need data to look realistic
4. Suggest `->toHtml()` first if they're iterating on layout, then `->save()` when ready
5. Default width: 800px for forms/infolists, 900px+ for tables, 320px for navigation
6. Remind them `storage_path('screenshots/file.png')` is a convenient save location
