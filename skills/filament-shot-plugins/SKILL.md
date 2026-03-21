---
name: filament-shot-plugins
description: Help with using filament-shot alongside third-party Filament plugins, or making a Filament plugin compatible with filament-shot. Use when the developer is trying to screenshot a form that contains a third-party plugin field (PhoneInput, MoneyInput, SlugInput, etc.), when a plugin component renders blank or incorrectly in screenshots, or when a plugin author wants to ensure their component renders properly in filament-shot. Trigger phrases include "third party plugin", "plugin component blank", "plugin not rendering", "PhoneInput screenshot", "make my plugin work with filament-shot", "plugin screenshot", "custom component screenshot", "Alpine component not showing", "plugin CSS not loading".
---

# Filament Shot — Third-Party Plugin Compatibility

You help developers:
1. Screenshot forms that contain third-party Filament plugin components
2. Debug why a plugin component renders blank or incorrectly
3. Make a Filament plugin compatible with filament-shot (plugin author perspective)

---

## How filament-shot handles plugin components

Filament Shot renders standalone HTML — no running server, no Livewire. Plugin components that depend on JavaScript (Alpine.js), custom CSS, or server-side state need special handling.

Filament Shot automatically handles:
- **Plugin CSS**: Auto-discovered via `FilamentAsset::register()` and inlined into the screenshot
- **CSS `url()` references** (flag sprites, icons): Rewritten to base64 data URIs
- **Alpine.js**: Bootstrapped from `livewire.min.js` — runs in the screenshot browser
- **Plugin Alpine components**: Extracted from `x-load-src`, transformed from ES module to `Alpine.data()` registration, injected before Alpine starts
- **`$wire.$entangle()`**: A stub `$wire` magic is registered; state is seeded from `->state([...])`

---

## For users: screenshot a form with a third-party plugin field

### Basic usage — just use the field normally

```php
use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\TextInput;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

FilamentShot::form([
    TextInput::make('name')->label('Full Name'),
    PhoneInput::make('phone')->label('Phone Number'),
])
->state([
    'name' => 'Jane Doe',
    'phone' => '+60123456789',   // pass the value via state()
])
->width(700)
->save(storage_path('screenshots/form-with-phone.png'));
```

filament-shot auto-discovers the plugin's CSS and JS via the plugin's service provider. No extra configuration needed in most cases.

### If the plugin component renders blank

**Step 1 — Verify the plugin is registered**

The plugin's service provider must be loaded. In your filament-shot context (queue job, command, test), make sure the Filament panel/plugin is booted. If you're using filament-shot in a test, ensure the plugin's `ServiceProvider` is in your `TestCase::getPackageProviders()`.

**Step 2 — Pass state correctly**

Plugin fields often use `$wire.$entangle('data.fieldName')` internally. Make sure to pass the value via `->state()`:

```php
->state(['phone' => '+60123456789'])
```

**Step 3 — Debug with `->toHtml()`**

```php
$html = FilamentShot::form([PhoneInput::make('phone')])->state(['phone' => '+60123456789'])->toHtml();
file_put_contents('/tmp/debug.html', $html);
// Open /tmp/debug.html in Chrome to inspect
```

**Step 4 — Check if plugin CSS was loaded**

The plugin must register its CSS via `FilamentAsset::register()` in its service provider. If the CSS is missing, check that the plugin's `ServiceProvider::boot()` has been called.

**Step 5 — If Alpine component doesn't initialize**

filament-shot supports `x-load-src` Alpine components (the mechanism Filament uses for lazy-loading plugin JS). Check that the plugin's JS asset is registered via `FilamentAsset::register([AlpineComponent::make(...)])`. If so, filament-shot will auto-transform and inject it.

---

## For plugin authors: make your plugin screenshot-compatible

### Register CSS and JS assets correctly

Use `FilamentAsset::register()` in your plugin's `boot()` method. filament-shot reads from this registry directly:

```php
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\AlpineComponent;

public function boot(): void
{
    FilamentAsset::register([
        Css::make('my-plugin', __DIR__ . '/../dist/my-plugin.css'),
        AlpineComponent::make('my-plugin', __DIR__ . '/../dist/my-component.js'),
    ], package: 'vendor/my-plugin');
}
```

### Structure your Alpine component as an ES module with a default export

filament-shot transforms `export{FunctionName as default}` into `Alpine.data()`. Your compiled JS should end with:

```js
export { MyComponent as default }
```

If your bundler uses a different export format, check that the output includes `export{... as default}`.

### Use `$wire.$entangle()` for reactive state, not direct wire calls

filament-shot stubs `$wire` with:
- `$entangle(path)` → reads from `window.__filamentShotWireState[path]`
- `$commit()` → no-op
- `callSchemaComponentMethod()` → never-resolving Promise (prevents async side effects)

So your component can use `this.$wire.$entangle('data.myField')` and it will receive the value the user passes via `->state(['myField' => $value])`.

**Avoid** calling `this.$wire.get()` or `this.$wire.set()` — these won't work. Use `$entangle` instead.

### Initialize your component from state, not async calls

If your component makes async API calls during initialization (e.g. geolocation, remote data fetch), the screenshot may complete before the call resolves. Design so the component renders correctly with synchronous state alone.

### Test your plugin with filament-shot

```php
use CCK\FilamentShot\FilamentShot;

$html = FilamentShot::form([
    MyPluginField::make('field')->label('Test'),
])->state(['field' => 'test-value'])->toHtml();

// Assert your component's expected HTML is present
expect($html)->toContain('my-plugin-wrapper');
```

### Add a filament-shot example to your README

Show users how to screenshot your field:

```php
FilamentShot::form([
    MyPluginField::make('value')->label('My Field'),
])
->state(['value' => 'example value'])
->save('my-plugin-field.png');
```

---

## Common plugin issues and fixes

| Symptom | Likely cause | Fix |
|---|---|---|
| Component renders but looks unstyled | Plugin CSS not registered or not found | Check `FilamentAsset::register([Css::make(...)])` in plugin ServiceProvider |
| Images/icons missing (flags, sprites) | CSS `url()` paths can't be resolved | filament-shot rewrites relative `url()` and `/vendor/...` paths to base64 — check that the image file exists at the expected path relative to the CSS file |
| Alpine component shows empty/default state | `$wire.$entangle()` returning null | Pass the value via `->state(['fieldName' => $value])` |
| Component initializes but shows wrong country/value | Async call overriding state | `callSchemaComponentMethod` returns never-resolving Promise — the async side effect never fires; this is intentional |
| Component not initializing at all | `x-load-src` ES module not transformed | Check that the JS asset is registered via `AlpineComponent::make(...)` and ends with `export{... as default}` |
| Custom component with no `x-load` | Direct `x-data` without lazy loading | This works fine — Alpine runs and initializes `x-data` components normally |
| Plugin triggers a network request during init | Geolocation, IP lookup, remote data | These requests will time out or silently fail — design component to initialize from synchronous state |
