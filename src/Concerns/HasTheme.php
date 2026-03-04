<?php

namespace CCK\FilamentShot\Concerns;

use Filament\Support\Colors\Color;
use Filament\Support\Colors\ColorManager;

trait HasTheme
{
    protected ?bool $darkMode = null;

    protected ?string $primaryColor = null;

    /**
     * @var array<string, array<int, string>|string>
     */
    protected array $colors = [];

    public function darkMode(): static
    {
        $this->darkMode = true;

        return $this;
    }

    public function lightMode(): static
    {
        $this->darkMode = false;

        return $this;
    }

    /**
     * @param  array<string, array<int, string>|string>  $colors
     */
    public function colors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    public function primaryColor(string $color): static
    {
        $this->primaryColor = $color;
        $this->colors['primary'] = $color;

        return $this;
    }

    public function isDarkMode(): bool
    {
        return $this->darkMode ?? config('filament-shot.theme.dark_mode', false);
    }

    public function getPrimaryColor(): string
    {
        return $this->primaryColor ?? config('filament-shot.theme.primary_color', '#6366f1');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getResolvedColors(): array
    {
        $defaults = ColorManager::DEFAULT_COLORS;
        $configColors = config('filament-shot.theme.colors', []);
        $merged = array_merge($defaults, $configColors, $this->colors);

        $resolved = [];

        foreach ($merged as $name => $color) {
            if (is_string($color)) {
                $resolved[$name] = Color::generatePalette($color);
            } elseif (is_array($color)) {
                $resolved[$name] = array_map(
                    fn (string $value): string => str_contains($value, 'oklch') ? $value : Color::convertToOklch($value),
                    $color,
                );
            }
        }

        return $resolved;
    }

    public function getColorCssVariables(): string
    {
        $css = '';

        foreach ($this->getResolvedColors() as $name => $shades) {
            foreach ($shades as $shade => $value) {
                $css .= "--{$name}-{$shade}: {$value};\n";
            }
        }

        return $css;
    }
}
