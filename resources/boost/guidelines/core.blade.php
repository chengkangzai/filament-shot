## Filament Shot

Filament Shot renders Filament v4/v5 UI components (Forms, Tables, Infolists, Stats Widgets, Modals, Notifications, Navigation) as PNG screenshots. It works **without a running application, Livewire, or a browser panel** — just PHP objects and Browsershot/Puppeteer.

### Entry points

```php
use CCK\FilamentShot\FilamentShot;

FilamentShot::form([...components...])    // Filament form fields
FilamentShot::table()                     // Table with columns + records
FilamentShot::infolist([...entries...])   // Infolist entries
FilamentShot::stats([...stats...])        // StatsOverviewWidget stats
FilamentShot::notification()              // Toast notification
FilamentShot::navigation()                // Sidebar navigation
FilamentShot::modal([...components...])   // Standalone modal
```

### Output methods (all renderers share these)

```php
$shot->save('/path/to/file.png');   // Save to disk
$shot->toBase64();                   // PNG as base64 string
$shot->toHtml();                     // Rendered HTML (for debugging)
$shot->toResponse();                 // HTTP response with PNG
```

### Viewport & theme options (all renderers)

```php
->width(1280)         // viewport width in px (default: 800)
->height(720)         // fixed height; omit to auto-fit content
->deviceScale(2)      // HiDPI / retina (default: 1)
->darkMode()          // enable dark mode
->primaryColor('#f59e0b')  // OKLCH primary accent color
->font('Inter')       // Google Fonts family name
```

### Forms

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

FilamentShot::form([
    TextInput::make('name')->label('Full Name'),
    Select::make('role')->options(['admin' => 'Admin', 'editor' => 'Editor']),
])
->state(['name' => 'Jane Doe', 'role' => 'admin'])  // populate field values
->save('form.png');
```

Supported fields: `TextInput`, `Select`, `Textarea`, `Toggle`, `Checkbox`, `Radio`, `DatePicker`, `DateTimePicker`, `FileUpload`, `ColorPicker`, `TagsInput`, `KeyValue`, `RichEditor`, `MarkdownEditor`, `Repeater`.

Layout components: `Section`, `Grid`, `Fieldset`, `Tabs` (use `->activeTab(2)`), `Wizard` (use `->startOnStep(2)`).

Modal wrapper: `->modal('Heading')->modalDescription(...)->modalSubmitLabel(...)->modalColor('danger')`.

Open a Select dropdown: `->openFields(['fieldName'])`.

### Tables

```php
use Filament\Tables\Columns\TextColumn;

FilamentShot::table()
    ->columns([
        TextColumn::make('name'),
        TextColumn::make('status')->badge()->color(fn ($state) => $state === 'Active' ? 'success' : 'danger'),
    ])
    ->records([
        ['name' => 'Alice', 'status' => 'Active'],
        ['name' => 'Bob', 'status' => 'Blocked'],
    ])
    ->heading('Users')
    ->striped()
    ->save('table.png');
```

Table extras: `->recordActions([Action, ActionGroup])`, `->bulkActions([BulkAction])`, `->selectedRows([0,2])`, `->reorderable()`, `->labeledActions()`.

### Stats

```php
use Filament\Widgets\StatsOverviewWidget\Stat;

FilamentShot::stats([
    Stat::make('Users', '1,234')->description('12% increase')->color('success'),
])->save('stats.png');
```

### Notifications

```php
FilamentShot::notification()
    ->title('Saved')
    ->body('Record updated.')
    ->success()   // or ->warning() ->danger() ->info()
    ->width(400)
    ->save('notification.png');
```

### Navigation

```php
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

FilamentShot::navigation()
    ->items([
        NavigationItem::make('Dashboard')->icon('heroicon-o-home'),
        NavigationGroup::make('Content')->items([
            NavigationItem::make('Posts')->icon('heroicon-o-document-text')->isActiveWhen(fn () => true),
        ]),
    ])
    ->width(320)
    ->save('nav.png');
```

### Extra CSS

Inject custom CSS into the screenshot:

```php
->css('.fi-ta-cell { padding: 12px; }')
```

### Highlight specific fields

```php
->highlight(['email', 'role'])  // draws attention border around those fields
```
