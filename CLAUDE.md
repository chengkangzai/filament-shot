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

### Alpine.js / JS-Driven Components

Filament v4/v5 ships JS-driven components (PhoneInput, etc.) that use Alpine.js. Since we render standalone HTML without a running server, Alpine must be bootstrapped manually:

- **Alpine.js source**: Bundled inside `livewire/livewire/dist/livewire.min.js` — NOT in Filament's own JS bundles. `AssetResolver::getCoreJsFileUrls()` includes this as the first script so Alpine is available when Filament's component bundles run.
- **`file://` blocking**: Chromium silently blocks `<script src="file:///...">` across directories when the page loads from `/tmp/`. `BrowsershotFactory::inlineFileScripts()` reads and inlines all `file://` script tags before writing the temp HTML file.
- **Plugin Alpine components** (e.g. `phoneInputFormComponent`): Registered as ES modules via `x-load`/`x-load-src`. Since dynamic `import()` of `file://` URLs is blocked, `BaseRenderer::extractAlpineComponentRegistrations()` extracts the `(x-data componentName, x-load-src relativePath)` pairs, transforms the ES module export into an `Alpine.data()` registration via `buildAlpineRegistration()`, and injects it before Alpine starts.
- **`$wire` stub**: `base.blade.php` registers an `Alpine.magic('wire', ...)` stub on `alpine:init` so Livewire-dependent components don't crash. `$entangle()` reads from `window.__filamentShotWireState` seeded by `FormRenderer::injectWireStateScript()`. `callSchemaComponentMethod` returns a never-resolving `Promise` to prevent intl-tel-input's geoip lookup from overriding the country set by `setNumber()`.
- **CSS URL rewriting**: Plugin CSS often references flag sprites via `url()`. `AssetResolver::rewriteCssUrls()` converts these to base64 data URIs so images load when CSS is inlined. Handles both relative paths (resolved against CSS file dir) and absolute `/vendor/package/file` paths (searched in the package source tree).
- **`x-load` / `x-load-src` / `x-load-css`**: Always stripped from sanitized HTML. Plugin CSS is already inlined; `x-load-src` Alpine modules are pre-registered via the mechanism above.
- **Modal visibility**: `.fi-modal { display: flex !important; }` is unconditional (no `[x-cloak]`) because Alpine now runs and removes `x-cloak`, then hides the modal via `x-show="isOpen"` (starts `false`). The `!important` overrides Alpine's inline `display: none`.

### Key Files

- `src/Support/ColumnAdapter.php` — unified column interface with `renderCell()` and `resolveBadgeClasses()`
- `src/Support/AssetResolver.php` — resolves theme CSS, plugin CSS (with URL rewriting), core JS file URLs (livewire first), and plugin Alpine component JS by path
- `src/Support/BrowsershotFactory.php` — creates configured Browsershot instances; inlines `file://` scripts; supports `fitContent` mode via `select('body')`
- `resources/views/layouts/base.blade.php` — HTML document wrapper (theme CSS, colors, fonts, `$wire` stub, `<!-- __FILAMENT_SHOT_PLUGIN_JS__ -->` placeholder)
- `resources/views/components/` — Blade templates for each renderer type

### Traits (in `src/Concerns/`)

- `HasOutput` — screenshot output methods (save, base64, response); auto-fit content height
- `HasViewport` — width, height, device scale
- `HasTheme` — dark mode, primary color, OKLCH color variables
- `HasFont` — configurable global font family
