---
name: filament-shot
description: Generate screenshots of Filament UI components using the filament-shot package. Use when the developer asks to screenshot, capture, render, or preview a Filament form, table, infolist, stats widget, modal, notification, or navigation — or when they want to generate PNG images of Filament components programmatically. Trigger phrases include "screenshot", "capture form", "render table as image", "generate PNG", "filament-shot", "take a screenshot of", "create an image of my form", "export as image".
---

# Filament Shot — Screenshot Generator

You help developers generate correct filament-shot code to capture Filament UI components as PNG screenshots.

## Core concept

Filament Shot renders Filament components to **standalone HTML**, then captures via Browsershot/Puppeteer.

**No running app required** — no Livewire, no panel, no database connection, no HTTP request.

```bash
composer require chengkangzai/filament-shot
npm install puppeteer
```

---

## Which renderer do I use?

| What to screenshot | Renderer |
|---|---|
| Form fields (inputs, selects, toggles…) | `FilamentShot::form([...])` |
| Data table with rows | `FilamentShot::table()` |
| Read-only data display | `FilamentShot::infolist([...])` |
| Dashboard stat cards | `FilamentShot::stats([...])` |
| Toast notification | `FilamentShot::notification()` |
| Sidebar navigation menu | `FilamentShot::navigation()` |
| Modal dialog (with or without form) | `FilamentShot::modal([...])` or `::form()->modal(...)` |

---

## Output API — which method to use?

```php
->save('/absolute/path/to/file.png')   // Write PNG to disk — most common
->toBase64()                            // PNG as base64 string — embed in HTML/email
->toHtml()                              // Return rendered HTML — USE FOR DEBUGGING
->toResponse()                          // Return HTTP response — use in routes/controllers
```

**When to use each:**
- `save()` — default choice; generates file in `storage/screenshots/` etc.
- `toBase64()` — embed directly in `<img src="data:image/png;base64,...">` without writing to disk
- `toHtml()` — debug layout issues first; open in browser before committing to PNG
- `toResponse()` — stream PNG directly from a controller/route for on-demand generation

---

## Viewport & appearance options (all renderers)

```php
->width(1280)           // viewport width in px (default: 800)
->height(720)           // fixed height — omit to auto-fit to content height
->deviceScale(2)        // HiDPI / retina rendering (default: 1)
->darkMode()            // enable Filament dark theme
->primaryColor('#f59e0b')   // custom accent color (hex or named)
->font('Inter')         // any Google Fonts family name
->css('.fi-btn { border-radius: 999px; }')  // inject extra CSS
->highlight(['email'])  // draw attention border around specific fields
```

---

## Forms

### Basic usage

```php
use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

FilamentShot::form([
    TextInput::make('name')->label('Full Name'),
    TextInput::make('email')->label('Email')->email(),
    Select::make('role')->options(['admin' => 'Admin', 'editor' => 'Editor']),
    Toggle::make('active')->label('Active'),
])
->state(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'role' => 'admin', 'active' => true])
->width(700)
->save(storage_path('screenshots/form.png'));
```

**Important:** `->state([])` populates field values. Without it, all fields appear empty.

### Supported field types

```
TextInput, Select, Textarea, Toggle, Checkbox, Radio, Placeholder,
DatePicker, DateTimePicker, FileUpload, ColorPicker,
TagsInput, KeyValue, RichEditor, MarkdownEditor, Repeater
```

### Layout components

```
Section, Grid, Fieldset, Tabs, Wizard
```

### Tabs — control which tab is active

```php
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

FilamentShot::form([
    Tabs::make()->tabs([
        Tab::make('General')->schema([TextInput::make('name')]),
        Tab::make('Notifications')->schema([Toggle::make('emails')]),
    ]),
])
->activeTab(2)   // show the second tab as active (1-indexed)
->save('tabs.png');
```

### Wizard — control which step is shown

```php
->startOnStep(2)  // start on step 2 (previous steps show as completed)
```

### Open a Select dropdown

```php
->openFields(['role'])  // renders the dropdown in its open/expanded state
```

### Modal wrapper

```php
FilamentShot::form([TextInput::make('name')])
    ->modal('Edit User')
    ->modalDescription('Update the user details below.')
    ->modalSubmitLabel('Save Changes')
    ->modalCancelLabel('Discard')
    ->modalColor('primary')   // 'primary', 'danger', 'warning', 'success'
    ->modalIcon('heroicon-o-pencil-square')
    ->save('modal-form.png');
```

Or a standalone modal without form fields:

```php
FilamentShot::modal([TextInput::make('reason')])
    ->heading('Confirm Deletion')
    ->color('danger')
    ->save('modal.png');
```

---

## Tables

### Two approaches: Filament column objects vs plain arrays

**Approach A — Filament TextColumn objects (recommended for rich styling):**

```php
use CCK\FilamentShot\FilamentShot;
use Filament\Tables\Columns\TextColumn;

FilamentShot::table()
    ->columns([
        TextColumn::make('name')->weight(\Filament\Support\Enums\FontWeight::Bold),
        TextColumn::make('status')
            ->badge()
            ->color(fn ($state) => match ($state) {
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

**Approach B — Plain arrays (simpler, no imports needed):**

```php
FilamentShot::table()
    ->columns([
        ['key' => 'name', 'label' => 'Name'],
        ['key' => 'email', 'label' => 'Email'],
        ['key' => 'role', 'label' => 'Role', 'badge' => true, 'color' => 'primary'],
    ])
    ->records([
        ['name' => 'Alice', 'email' => 'alice@example.com', 'role' => 'Admin'],
    ])
    ->save('table.png');
```

Use Filament column objects when you need badges, icons, descriptions, alignment, or font styling. Use plain arrays for quick data tables.

### Table extras

```php
// Per-row action buttons
->recordActions([
    Action::make('edit')->label('Edit')->icon('heroicon-o-pencil-square'),
    ActionGroup::make([
        Action::make('duplicate')->label('Duplicate'),
        Action::make('delete')->label('Delete')->color('danger'),
    ]),
])

// Bulk action toolbar with checked rows
->bulkActions([
    BulkAction::make('delete')->label('Delete Selected')->color('danger'),
])
->selectedRows([0, 2])   // indices of pre-checked rows

// Drag handles for reorderable tables
->reorderable()

// Show text labels next to action icons
->labeledActions()
```

---

## Infolists

```php
use CCK\FilamentShot\FilamentShot;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

FilamentShot::infolist([
    Section::make('Customer Profile')->schema([
        TextEntry::make('name')->label('Full Name'),
        TextEntry::make('email')->label('Email'),
        TextEntry::make('plan')->label('Plan')->badge()->color('success'),
        TextEntry::make('joined')->label('Member Since'),
    ]),
])
->state([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'plan' => 'Pro',
    'joined' => 'January 2024',
])
->width(700)
->save(storage_path('screenshots/infolist.png'));
```

---

## Stats widgets

```php
use CCK\FilamentShot\FilamentShot;
use Filament\Widgets\StatsOverviewWidget\Stat;

FilamentShot::stats([
    Stat::make('Total Users', '12,345')
        ->description('12% increase this month')
        ->descriptionIcon('heroicon-m-arrow-trending-up')
        ->color('success')
        ->chart([4, 5, 6, 7, 8, 7, 9, 11]),
    Stat::make('Revenue', '$56,789')
        ->description('8% increase')
        ->chart([7, 3, 4, 5, 6, 3, 5, 8]),
    Stat::make('Churn Rate', '2.4%')
        ->description('0.3% increase')
        ->descriptionIcon('heroicon-m-arrow-trending-down')
        ->color('danger'),
])
->width(960)
->save(storage_path('screenshots/stats.png'));
```

---

## Notifications

```php
FilamentShot::notification()
    ->title('Order Confirmed')
    ->body('Your order #1234 has been placed successfully.')
    ->success()     // ->warning() ->danger() ->info() also available
    ->width(400)
    ->save(storage_path('screenshots/notification.png'));
```

---

## Navigation

```php
use CCK\FilamentShot\FilamentShot;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

FilamentShot::navigation()
    ->items([
        NavigationItem::make('Dashboard')->icon('heroicon-o-home'),
        NavigationGroup::make('Content')
            ->items([
                NavigationItem::make('Posts')
                    ->icon('heroicon-o-document-text')
                    ->isActiveWhen(fn () => true)  // highlight as currently active
                    ->badge('24', 'success'),
                NavigationItem::make('Pages')
                    ->icon('heroicon-o-document'),
            ]),
        NavigationGroup::make('Settings')
            ->collapsible()
            ->items([
                NavigationItem::make('General')->icon('heroicon-o-cog-6-tooth'),
                NavigationItem::make('Team')->icon('heroicon-o-users'),
            ]),
    ])
    ->heading('My App')
    ->width(280)
    ->save(storage_path('screenshots/navigation.png'));
```

---

## Recommended widths by renderer

| Renderer | Recommended width |
|---|---|
| Form | 600–800px |
| Table | 900–1200px |
| Infolist | 600–800px |
| Stats | 900–1100px |
| Notification | 380–440px |
| Navigation | 260–320px |
| Modal | 560–700px (auto) |

---

## Common gotchas

| Problem | Fix |
|---|---|
| Fields appear empty | Use `->state(['field' => 'value'])` to populate values |
| Blank white screenshot | Run `npm install puppeteer`; ensure Chrome is installed |
| Layout looks wrong | Call `->toHtml()` first and open in browser to debug |
| Timeout in CI | Set `filament-shot.browsershot.no_sandbox => true` in config |
| Wrong theme colors | Run `php artisan filament:assets` or check Filament is configured |
| Auto height too tall | Set explicit `->height(px)` to override auto-fit |
| HiDPI / retina | Add `->deviceScale(2)` for 2× resolution output |

---

## How to help the developer

1. **Identify the renderer** — form? table? infolist? stats? notification? nav?
2. **Reuse their existing Filament definitions** — read their code, don't rewrite components
3. **Ask what data to show** — screenshots need realistic state/records to look good
4. **Suggest `->toHtml()` for iteration** — faster than waiting for a PNG each time
5. **Default to `storage_path()`** for the save path — it's always writable
6. **No state by default** — remind them fields will be blank without `->state()`
