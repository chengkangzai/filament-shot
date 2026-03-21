---
name: filament-shot-theme
description: Match filament-shot screenshots to the real Filament panel theme. Use when the developer wants screenshots to look like their actual app, wants to apply their panel's primary color or font to screenshots, asks why screenshots don't match their panel, or wants to extract theme config from their Filament panel provider. Trigger phrases include "match my panel theme", "use my panel colors", "primary color from panel", "same color as my app", "screenshots don't match", "use my filament theme", "apply panel font", "detect panel color", "what color is my panel", "copy my panel style", "theme from panel provider".
---

# Filament Shot — Match Your Panel Theme

You help developers make filament-shot screenshots look identical to their real Filament panel by reading their panel configuration and applying those settings.

## Step 1 — Find the panel provider

Look for the Filament panel provider in the project. Common locations:

```
app/Providers/Filament/AdminPanelProvider.php
app/Providers/Filament/AppPanelProvider.php
app/Providers/FilamentServiceProvider.php
app/Filament/Providers/PanelProvider.php
```

Search if not obvious:

```bash
grep -r "PanelProvider\|->colors\|->font\|FilamentColor" app/ --include="*.php" -l
```

## Step 2 — Extract theme settings

### Primary color

Filament panels configure color via `->colors([...])`. Look for:

```php
// Named Filament color
->colors(['primary' => Color::Amber])
->colors(['primary' => Color::Violet])
->colors(['primary' => Color::Indigo])

// Custom OKLCH array (full palette)
->colors([
    'primary' => [
        50  => '240 249 255',
        100 => '224 242 254',
        // ... 200–900
        500 => '14 165 233',   // <-- this is the main color
        // ...
    ],
])

// Custom hex via ColorManager
->colors(['primary' => Color::hex('#f59e0b')])
```

### Font

```php
->font('Inter')
->font('Nunito')
->font('DM Sans')
```

### Dark mode

```php
->darkMode(ThemeMode::Dark)    // always dark
->darkMode(ThemeMode::Light)   // always light (default)
->darkMode(ThemeMode::System)  // follows OS
```

### Custom theme CSS

```php
->viteTheme('resources/css/filament/admin/theme.css')
->theme(asset('css/filament-theme.css'))
```

## Step 3 — Apply to filament-shot

Once you've read the panel config, translate it to filament-shot options:

### Named Filament colors → hex

| Filament Color | Hex (500 shade) |
|---|---|
| `Color::Amber` | `#f59e0b` |
| `Color::Blue` | `#3b82f6` |
| `Color::Cyan` | `#06b6d4` |
| `Color::Emerald` | `#10b981` |
| `Color::Fuchsia` | `#d946ef` |
| `Color::Green` | `#22c55e` |
| `Color::Indigo` | `#6366f1` |
| `Color::Lime` | `#84cc16` |
| `Color::Orange` | `#f97316` |
| `Color::Pink` | `#ec4899` |
| `Color::Purple` | `#a855f7` |
| `Color::Red` | `#ef4444` |
| `Color::Rose` | `#f43f5e` |
| `Color::Sky` | `#0ea5e9` |
| `Color::Slate` | `#64748b` |
| `Color::Teal` | `#14b8a6` |
| `Color::Violet` | `#8b5cf6` |
| `Color::Yellow` | `#eab308` |
| `Color::Zinc` | `#71717a` |

### Full example — applying panel theme to filament-shot

```php
// Panel uses: ->colors(['primary' => Color::Violet]) ->font('Inter')
FilamentShot::form([...])
    ->primaryColor('#8b5cf6')   // Violet 500
    ->font('Inter')
    ->width(800)
    ->save('screenshot.png');
```

### Custom OKLCH palette

If the panel uses a custom OKLCH array, use the `500` shade value and convert to hex, or pass it directly as an OKLCH CSS value:

```php
// Panel: '500' => '14 165 233'  (Sky blue in OKLCH space)
->primaryColor('oklch(62.8% 0.259 243)')   // or find the hex equivalent
->primaryColor('#0ea5e9')                   // Sky 500 hex — easier
```

### Dark mode

```php
// Panel: ->darkMode(ThemeMode::Dark)
->darkMode()

// Panel: ->darkMode(ThemeMode::Light) — default, no change needed
// Panel: ->darkMode(ThemeMode::System) — match user's preference; pick one for screenshots
```

## Step 4 — Create a reusable helper (optional)

If the developer wants consistent theming across many screenshots, suggest a helper:

```php
// app/Support/FilamentShotTheme.php
class FilamentShotTheme
{
    public static function apply(\CCK\FilamentShot\Renderers\BaseRenderer $shot): \CCK\FilamentShot\Renderers\BaseRenderer
    {
        return $shot
            ->primaryColor('#8b5cf6')   // matches panel primary
            ->font('Inter')             // matches panel font
            ->width(800);
    }
}

// Usage
FilamentShotTheme::apply(
    FilamentShot::form([...])
)->save('screenshot.png');
```

Or use the config file (`config/filament-shot.php`) to set defaults:

```php
// config/filament-shot.php
return [
    // These apply to every screenshot without explicit ->primaryColor() / ->font()
    'primary_color' => '#8b5cf6',
    'font' => 'Inter',
];
```

## Common mismatches and fixes

| Screenshot looks different from panel | Fix |
|---|---|
| Wrong accent color (buttons, badges) | Read `->colors(['primary' => ...])` from panel provider and apply `->primaryColor(hex)` |
| Different font | Read `->font(...)` from panel provider and apply `->font(name)` |
| Light when panel is dark (or vice versa) | Add or remove `->darkMode()` |
| Custom color variables not applied | Use `->css(':root { --primary-500: ...; }')` to override specific shades |
| Plugin CSS (flags, icons) missing | Plugin fields auto-load CSS — if missing, check plugin ServiceProvider is booted |
