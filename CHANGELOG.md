# Changelog

All notable changes to `filament-shot` will be documented in this file.

## Unreleased

### Bug Fixes

- **Fixed blank modal screenshots** (#150): `FilamentShot::modal()` and `FilamentShot::form()->modal()` produced empty PNGs because Filament's modal markup hides the inner `.fi-modal-window` via Alpine's `x-show="isWindowVisible"`, and the window container's viewport-tied grid layout (`min-height: 100%`, `max-height: calc(100dvh - 2rem)`) collapsed under the fitContent 1px viewport. The screenshot CSS now forces the window visible at its natural height.

### New Features

- **`FilamentShot::modal(array $components = [])`** (#150): the standalone modal renderer now accepts an optional components array, rendered inside the modal body via the form-rendering pipeline. Supports `->state()` and `->openFields()`. Matches the documented signature.

## v0.9.2 - 2026-03-26

### What's Changed

#### Bug Fixes

- **Fixed fitContent rendering**: `select('body')` was cropping screenshots to viewport height for content-dense pages. Now uses 1px viewport + `fullPage()` so `scrollHeight` accurately reflects actual content height (not inflated viewport size). This dramatically improves performance (~86% faster) and correctness.

#### New Features

- **`->withTailwind()`**: Injects the Tailwind Play CDN into rendered HTML, enabling utility classes in custom `blade()`/`view()` templates that are absent from Filament's purged CSS bundle. Requires network access during rendering.

**Full Changelog**: https://github.com/chengkangzai/filament-shot/compare/v0.9.1...v0.9.2

## v0.9.1 - 2026-03-26

### What's New

- **Filament v4 support** — package now supports both Filament `^4.0` and `^5.0`
- CI matrix extended to test against both Filament versions on every push

## v0.9.0 - 2026-03-24

### What's New

- **Builder blocks** — renders Filament Builder fields with block headers and UI (#104)
- **HeaderActions renderer** — `FilamentShot::headerActions()` for standalone action bars
- **CodeEditor** — syntax highlighting + light/dark theme support (#134/#141)
- **ToggleButtons** — static active state rendering
- **Editable columns** — table columns with inline input rendering
- **View/Blade renderer** — `FilamentShot::view()` and `FilamentShot::blade()` for arbitrary Blade rendering
- **Radio & CheckboxList** — static checked state injection with string-state coercion fix

## 1.0.0 - 202X-XX-XX

- initial release
