# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Filament Shot renders Filament v4/v5 UI components (Forms, Tables, Infolists, Stats Widgets) as PNG screenshots. It generates standalone HTML with Filament's CSS classes, then captures via Browsershot/Puppeteer — no Livewire or running application needed.

## Commands

```bash
vendor/bin/pest --no-coverage          # Run all tests
vendor/bin/pest --filter="test name"   # Run a single test
vendor/bin/pest tests/Unit             # Run only unit tests (no Chrome needed)
vendor/bin/pest tests/Feature          # Run feature tests (requires Chrome + Puppeteer)
vendor/bin/phpstan analyse             # Static analysis (level 4)
vendor/bin/pint                        # Fix code style (Laravel Pint)
vendor/bin/pint --test                 # Check code style without fixing
```

## Architecture

### Rendering Pipeline

`FilamentShot` (entry point) → `*Renderer` → Blade template → HTML string → `Browsershot` → PNG

1. **`FilamentShot`** — static factory: `::form()`, `::table()`, `::infolist()`, `::stats()`
2. **Renderers** (`src/Renderers/`) — each extends `BaseRenderer`, implements `renderContent()` which renders a Blade view
3. **`BaseRenderer`** — wraps content in `layouts/base.blade.php` (full HTML document with Filament theme CSS, color variables, font imports); also provides shared `safeCall()` for safe property access on Filament objects
4. **`HasOutput` trait** — provides `save()`, `toBase64()`, `toHtml()`, `toResponse()` via `BrowsershotFactory`; auto-fits screenshot height to content by default (`select('body')`), uses fixed viewport when `->height()` is explicitly set

### Table Column Rendering

`ColumnAdapter` wraps both Filament `TextColumn` objects and plain arrays with a unified interface:

- **TextColumn objects**: `renderCell()` delegates to Filament's `toEmbeddedHtml()` — gets all CSS classes for free (badges, colors, font, weight, size, alignment, icons, descriptions, wrap, etc.)
- **Array sources**: `renderCell()` generates HTML manually using `resolveClasses()` registry (maps properties to CSS class prefixes)
- **`resolve()`** tries `get*`, `is*`, `can*` method prefixes on objects; supports callables on arrays

### CSS Considerations

- Filament's theme CSS uses `@layer` — any unlayered rules override all Filament styles regardless of specificity. Never add unlayered `*` selectors.
- Font CSS variables (`--font-family`, `--mono-font-family`, `--serif-font-family`) must be defined in `:root` for Filament's `var()` references to work
- For array-path non-badge columns: extra CSS classes (font, weight, size) go on the inner `<span class="fi-ta-text-item">`, matching Filament's `.fi-ta-text-item.fi-font-mono` selectors
- For array-path badge columns: extra CSS classes go on the outer `<div>` with `fi-ta-text-item`, since font cascades down to the badge `<span>`

### Key Files

- `src/Support/ColumnAdapter.php` — unified column interface with `renderCell()` and `resolveBadgeClasses()`
- `src/Support/AssetResolver.php` — resolves Filament theme CSS and extra CSS from config
- `src/Support/BrowsershotFactory.php` — creates configured Browsershot instances; supports `fitContent` mode via `select('body')`
- `resources/views/layouts/base.blade.php` — HTML document wrapper (theme CSS, colors, fonts)
- `resources/views/components/` — Blade templates for each renderer type

### Traits (in `src/Concerns/`)

- `HasOutput` — screenshot output methods (save, base64, response); auto-fit content height
- `HasViewport` — width, height, device scale
- `HasTheme` — dark mode, primary color, OKLCH color variables
- `HasFont` — configurable global font family
