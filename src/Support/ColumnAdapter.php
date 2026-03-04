<?php

namespace CCK\FilamentShot\Support;

use ArrayAccess;
use BackedEnum;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\View\Components\BadgeComponent;
use Filament\Tables\Columns\Column;

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
        if ($this->source instanceof Column && $this->source instanceof HasEmbeddedView) {
            try {
                $column = clone $this->source;
                $column->record($record);

                return $column->toEmbeddedHtml();
            } catch (\Throwable) {
                // Fall through to manual rendering
            }
        }

        $name = is_array($this->source)
            ? ($this->source['name'] ?? '')
            : $this->safeCall(fn () => $this->source->getName(), '');

        $value = $record[$name] ?? '';

        $isBadge = $this->resolve('badge', false);
        $extraClasses = $this->resolveClasses($value);

        if ($isBadge) {
            $colorName = $this->resolve('color', null, $value);
            $badgeClasses = self::resolveBadgeClasses($colorName);
            $escapedValue = e($value);

            return '<div class="fi-ta-text fi-ta-text-has-badges fi-ta-text-item ' . $extraClasses . '">'
                . '<span class="fi-badge fi-size-sm ' . $badgeClasses . '">' . $escapedValue . '</span>'
                . '</div>';
        }

        $escapedValue = e($value);

        return '<div class="fi-ta-text ' . $extraClasses . '">'
            . '<span class="fi-ta-text-item fi-size-sm">' . $escapedValue . '</span>'
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
