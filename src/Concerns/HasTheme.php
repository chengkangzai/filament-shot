<?php

namespace CCK\FilamentShot\Concerns;

trait HasTheme
{
    protected ?bool $darkMode = null;

    protected ?string $primaryColor = null;

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

    public function primaryColor(string $color): static
    {
        $this->primaryColor = $color;

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
}
