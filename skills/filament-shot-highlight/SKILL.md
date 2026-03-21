---
name: filament-shot-highlight
description: Use filament-shot's highlight, annotation, and CSS customization features to create polished documentation screenshots. Use when the developer wants to draw attention to specific fields, add custom styling, create step-by-step tutorial screenshots, highlight validation errors, annotate UI elements, or inject custom CSS into screenshots. Trigger phrases include "highlight field", "highlight the email field", "draw attention to", "annotate screenshot", "show the user which field", "custom CSS screenshot", "step by step screenshot", "mark the field", "circle this field", "inject CSS", "custom styling screenshot", "documentation screenshot".
---

# Filament Shot — Highlight & Visual Annotation

You help developers create polished, annotated screenshots using filament-shot's `->highlight()` and `->css()` features. These are most useful for documentation, onboarding guides, changelogs, and step-by-step tutorials.

---

## `->highlight()` — draw attention to specific fields

```php
FilamentShot::form([...])
    ->highlight('email')           // default: red outline
    ->save('screenshot.png');
```

### Signature

```php
->highlight(string $fieldKey, string $color = '#ef4444', string $style = 'outline'): static
```

| Parameter | Default | Description |
|---|---|---|
| `$fieldKey` | required | The `make('key')` name of the field to highlight |
| `$color` | `'#ef4444'` (red) | Any CSS color: hex, rgb, named color |
| `$style` | `'outline'` | `'outline'` \| `'box'` \| `'underline'` |

### Highlight styles

**`outline`** — clean border around the field wrapper (default):
```php
->highlight('email')
->highlight('email', '#ef4444', 'outline')
```

**`box`** — border + soft background fill (more prominent):
```php
->highlight('email', '#ef4444', 'box')
->highlight('role', '#f59e0b', 'box')    // amber box
```

**`underline`** — subtle underline on the input itself:
```php
->highlight('name', '#3b82f6', 'underline')
```

### Multiple highlights at once

Chain multiple `->highlight()` calls — each can have a different color and style:

```php
FilamentShot::form([
    TextInput::make('name')->label('Name'),
    TextInput::make('email')->label('Email'),
    Select::make('role')->label('Role'),
])
->state(['name' => 'Jane Doe', 'email' => 'jane@example.com', 'role' => 'admin'])
->highlight('email', '#ef4444', 'box')      // red box — the new/required field
->highlight('name', '#3b82f6', 'outline')   // blue outline — for context
->width(700)
->save('annotated-form.png');
```

### Use cases by style

| Use case | Recommended style |
|---|---|
| "Fill in this field" — onboarding | `'box'` with brand color |
| "This field is required" — validation docs | `'box'` with `'#ef4444'` (red) |
| "This field changed" — changelog screenshot | `'outline'` with `'#f59e0b'` (amber) |
| "We updated this" — release notes | `'outline'` with primary color |
| Subtle annotation | `'underline'` |

### Step-by-step tutorial screenshots

Generate one screenshot per step, highlighting the relevant field at each step:

```php
$form = [
    TextInput::make('name')->label('Full Name'),
    TextInput::make('email')->label('Email'),
    Select::make('plan')->label('Plan')->options(['free' => 'Free', 'pro' => 'Pro']),
];

$state = ['name' => 'Jane Doe', 'email' => 'jane@example.com', 'plan' => 'pro'];

// Step 1 — highlight name field
FilamentShot::form($form)->state($state)
    ->highlight('name', '#3b82f6', 'box')
    ->save(storage_path('screenshots/step-1-name.png'));

// Step 2 — highlight email field
FilamentShot::form($form)->state($state)
    ->highlight('email', '#3b82f6', 'box')
    ->save(storage_path('screenshots/step-2-email.png'));

// Step 3 — highlight plan selection
FilamentShot::form($form)->state($state)
    ->highlight('plan', '#3b82f6', 'box')
    ->openFields(['plan'])   // show dropdown open for context
    ->save(storage_path('screenshots/step-3-plan.png'));
```

---

## `->css()` — inject custom CSS

```php
->css(string $css): static
```

Injects raw CSS into the screenshot's `<style>` tag. Use for fine-tuning appearance, overriding Filament defaults, or adding brand styling.

**Important CSS rule**: Filament's theme uses `@layer`, so unlayered CSS overrides everything. You can use regular selectors without `!important` in most cases.

### Common uses

```php
// Change the primary color for this screenshot
->css(':root { --primary-500: #8b5cf6; }')

// Remove labels for a compact view
->css('.fi-fo-field-wrp-label { display: none; }')

// Increase field padding
->css('.fi-fo-text-input { padding: 1rem; }')

// Custom font size for a specific field
->css('#form\\.name input { font-size: 1.25rem; font-weight: 700; }')

// Add a visual separator between sections
->css('.fi-section:not(:last-child) { border-bottom: 2px solid #e5e7eb; margin-bottom: 1.5rem; }')

// Simulate an error state on a field
->css('#form\\.email { border-color: #ef4444 !important; }')
->css('#form\\.email ~ .fi-fo-field-wrp-error-message { display: block; }')
```

### Load CSS from a file

```php
->cssFile(resource_path('css/my-custom-theme.css'))
->cssFile(base_path('vendor/my-plugin/dist/plugin.css'))
```

### Chain with highlight

`->css()` and `->highlight()` can be combined freely:

```php
FilamentShot::form([...])
    ->highlight('email', '#ef4444', 'box')
    ->css('.fi-btn-color-primary { background: #8b5cf6 !important; }')   // purple submit button
    ->darkMode()
    ->save('branded-screenshot.png');
```

---

## Practical patterns

### "What's new" release screenshot

Highlight the new field with amber to signal change:

```php
->highlight('api_key', '#f59e0b', 'outline')
->css('.fi-section-header-heading::after { content: " — New in v2.3"; color: #f59e0b; font-size: 0.75rem; }')
```

### Error state documentation

Show a form with validation errors by simulating error styling via CSS:

```php
->highlight('email', '#ef4444', 'box')
->css('[data-field-wrapper]:has(#form\\.email) .fi-fo-field-wrp-label { color: #ef4444; }')
```

### Brand-matched documentation screenshots

```php
->primaryColor('#6d28d9')                            // brand primary
->highlight('cta_field', '#6d28d9', 'box')           // brand color highlight
->css('body { background-color: #f5f3ff; }')         // brand background
->font('Nunito')                                      // brand font
```

### Zoom in on a specific section

Use a narrow width + highlight to focus the reader's eye:

```php
->width(500)
->highlight('critical_field', '#ef4444', 'box')
->save('focused-screenshot.png');
```
