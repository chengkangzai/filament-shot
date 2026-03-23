<?php

namespace CCK\FilamentShot\Support;

use ArrayAccess;
use BackedEnum;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Support\Enums\IconSize;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\View\Components\BadgeComponent;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\View\Components\Columns\IconColumnComponent\IconComponent;
use Illuminate\View\ComponentAttributeBag;

/**
 * @implements ArrayAccess<string, mixed>
 */
class ColumnAdapter implements ArrayAccess
{
    private const PREFIXED_CLASS_MAP = [
        'fontFamily' => 'fi-font',
        'weight' => 'fi-font',
        'size' => 'fi-size',
        'alignment' => 'fi-align',
    ];

    private const BOOLEAN_CLASS_MAP = [
        'copyable' => 'fi-copyable',
        'wrap' => 'fi-wrapped',
    ];

    public function __construct(
        protected mixed $source,
    ) {}

    public function resolve(string $property, mixed $default = null, mixed ...$args): mixed
    {
        if (is_array($this->source)) {
            $value = $this->source[$property] ?? $default;

            if (is_callable($value)) {
                return $value(...$args) ?? $default;
            }

            return $value;
        }

        foreach (['get', 'is', 'can'] as $prefix) {
            $method = $prefix . ucfirst($property);
            $result = $this->safeCall(fn () => $this->source->$method(...$args), null);

            if ($result !== null) {
                if ($result instanceof BackedEnum) {
                    return $result->value;
                }

                return $result;
            }
        }

        return $default;
    }

    /**
     * Render a cell value for the given record.
     */
    public function renderCell(array $record): string
    {
        // TextInputColumn and SelectColumn require a mounted Livewire table to call
        // toEmbeddedHtml() (they call getLivewire() internally). Render them manually.
        if ($this->source instanceof TextInputColumn) {
            return $this->renderTextInputCell($record);
        }

        if ($this->source instanceof SelectColumn) {
            return $this->renderSelectCell($record);
        }

        if ($this->source instanceof Column && $this->source instanceof HasEmbeddedView) {
            try {
                $column = clone $this->source;
                $column->record($record);

                return $column->toEmbeddedHtml();
            } catch (\Throwable) {
                // Fall through to manual rendering
            }
        }

        if ($this->source instanceof IconColumn) {
            return $this->renderIconCell($record);
        }

        if ($this->source instanceof ColorColumn) {
            return $this->renderColorCell($record);
        }

        $name = is_array($this->source)
            ? ($this->source['name'] ?? '')
            : $this->safeCall(fn () => $this->source->getName(), '');

        $value = $record[$name] ?? '';

        $isBadge = $this->resolve('badge', false);
        $extraClasses = $this->resolveClasses($value);

        $escapedValue = e($value);

        if ($isBadge) {
            $colorName = $this->resolve('color', null, $value);
            $badgeClasses = self::resolveBadgeClasses($colorName);
            $divClasses = trim('fi-ta-text fi-ta-text-has-badges fi-ta-text-item ' . $extraClasses);

            return '<div class="' . $divClasses . '">'
                . '<span class="fi-badge fi-size-sm ' . $badgeClasses . '">' . $escapedValue . '</span>'
                . '</div>';
        }

        $spanClasses = trim('fi-ta-text-item fi-size-sm ' . $extraClasses);

        return '<div class="fi-ta-text">'
            . '<span class="' . $spanClasses . '">' . $escapedValue . '</span>'
            . '</div>';
    }

    /**
     * Resolve Filament CSS classes for a badge color.
     */
    public static function resolveBadgeClasses(?string $color): string
    {
        if ($color === null) {
            $color = 'primary';
        }

        $classes = FilamentColor::getComponentClasses(BadgeComponent::class, $color);

        return implode(' ', $classes);
    }

    public function offsetExists(mixed $offset): bool
    {
        if (is_array($this->source)) {
            if ($offset === 'label' && ! isset($this->source['label'])) {
                return isset($this->source['name']);
            }

            return isset($this->source[$offset]);
        }

        return $this->resolve($offset) !== null;
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (is_array($this->source)) {
            if ($offset === 'label') {
                return $this->source['label'] ?? str($this->source['name'] ?? '')->headline()->toString();
            }

            return $this->source[$offset] ?? null;
        }

        return $this->resolve($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void {}

    public function offsetUnset(mixed $offset): void {}

    /**
     * Render a TextInputColumn cell as a visible text input field.
     *
     * toEmbeddedHtml() on these columns requires a Livewire-mounted table, so
     * we render them manually using Filament's fi-ta-text-input CSS classes.
     */
    private function renderTextInputCell(array $record): string
    {
        /** @var TextInputColumn $column */
        $column = $this->source;
        $name = $this->safeCall(fn () => $column->getName(), '');
        $value = $record[$name] ?? '';
        $type = $this->safeCall(fn () => $column->getType(), 'text');
        $placeholder = $this->safeCall(fn () => $column->getPlaceholder(), null);
        $isDisabled = $this->safeCall(fn () => $column->isDisabled(), false);

        $escapedValue = e((string) $value);
        $escapedPlaceholder = $placeholder !== null ? ' placeholder="' . e($placeholder) . '"' : '';
        $disabledAttr = $isDisabled ? ' disabled' : '';

        return '<div class="fi-ta-text-input">'
            . '<div class="fi-input-wrp">'
            . '<div class="fi-input-wrp-content-ctn">'
            . '<input'
            . ' type="' . e($type) . '"'
            . ' class="fi-input"'
            . ' value="' . $escapedValue . '"'
            . $escapedPlaceholder
            . $disabledAttr
            . ' />'
            . '</div>'
            . '</div>'
            . '</div>';
    }

    /**
     * Render a SelectColumn cell as a visible native select dropdown.
     *
     * toEmbeddedHtml() on these columns requires a Livewire-mounted table, so
     * we render them manually using Filament's fi-ta-select CSS classes.
     */
    private function renderSelectCell(array $record): string
    {
        /** @var SelectColumn $column */
        $column = $this->source;
        $name = $this->safeCall(fn () => $column->getName(), '');
        $value = $record[$name] ?? '';
        $options = $this->safeCall(fn () => $column->getOptions(), []);
        $placeholder = $this->safeCall(fn () => $column->getPlaceholder(), null);
        $canSelectPlaceholder = $this->safeCall(fn () => $column->canSelectPlaceholder(), true);
        $isDisabled = $this->safeCall(fn () => $column->isDisabled(), false);

        $disabledAttr = $isDisabled ? ' disabled' : '';

        $html = '<div class="fi-ta-select">'
            . '<div class="fi-input-wrp">'
            . '<select class="fi-select-input"' . $disabledAttr . '>';

        if ($canSelectPlaceholder && $placeholder !== null) {
            $html .= '<option value="">' . e($placeholder) . '</option>';
        }

        foreach ($options as $optionValue => $optionLabel) {
            $selected = ((string) $optionValue === (string) $value) ? ' selected' : '';
            $html .= '<option value="' . e((string) $optionValue) . '"' . $selected . '>'
                . e((string) $optionLabel)
                . '</option>';
        }

        $html .= '</select>'
            . '</div>'
            . '</div>';

        return $html;
    }

    /**
     * Render a ColorColumn cell as a color swatch circle.
     */
    private function renderColorCell(array $record): string
    {
        $name = $this->safeCall(fn () => $this->source->getName(), '');
        $value = $record[$name] ?? null;

        if (blank($value)) {
            return '<div class="fi-ta-color"></div>';
        }

        $escapedColor = e($value);

        return '<div class="fi-ta-color">'
            . '<div style="background-color: ' . $escapedColor . '; width: 1.5rem; height: 1.5rem; border-radius: 9999px; border: 1px solid rgba(0,0,0,0.1);"></div>'
            . '</div>';
    }

    /**
     * Render an IconColumn cell manually when toEmbeddedHtml() is unavailable.
     */
    private function renderIconCell(array $record): string
    {
        /** @var IconColumn $column */
        $column = clone $this->source;
        $column->record($record);

        $name = $this->safeCall(fn () => $column->getName(), '');
        $value = $record[$name] ?? null;

        $isBoolean = $this->safeCall(fn () => $column->isBoolean(), false);

        if ($isBoolean) {
            $icon = $value ? $this->safeCall(fn () => $column->getTrueIcon(), null) : $this->safeCall(fn () => $column->getFalseIcon(), null);
            $color = $value ? $this->safeCall(fn () => $column->getTrueColor(), 'success') : $this->safeCall(fn () => $column->getFalseColor(), 'danger');
        } else {
            $icon = $this->safeCall(fn () => $column->getIcon($value), null);
            $color = $this->safeCall(fn () => $column->getColor($value), null);
        }

        if (blank($icon)) {
            return '<div class="fi-ta-icon"></div>';
        }

        $iconHtml = $this->safeCall(
            fn () => \Filament\Support\generate_icon_html($icon, attributes: (new ComponentAttributeBag)->color(IconComponent::class, $color), size: IconSize::Large)?->toHtml(),
            '',
        );

        return '<div class="fi-ta-icon">' . $iconHtml . '</div>';
    }

    /**
     * Resolve CSS classes from column properties for array-based rendering.
     */
    private function resolveClasses(mixed ...$args): string
    {
        $classes = [];

        foreach (self::PREFIXED_CLASS_MAP as $property => $prefix) {
            $value = $this->resolve($property, null, ...$args);

            if ($value !== null) {
                $classes[] = "{$prefix}-{$value}";
            }
        }

        foreach (self::BOOLEAN_CLASS_MAP as $property => $class) {
            if ($this->resolve($property, false, ...$args)) {
                $classes[] = $class;
            }
        }

        return implode(' ', $classes);
    }

    private function safeCall(callable $callback, mixed $default): mixed
    {
        try {
            return $callback() ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }
}
