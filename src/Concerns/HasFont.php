<?php

namespace CCK\FilamentShot\Concerns;

trait HasFont
{
    protected ?string $font = null;

    public function font(string $font): static
    {
        $this->font = $font;

        return $this;
    }

    public function getFont(): string
    {
        return $this->font ?? config('filament-shot.theme.font', 'Inter');
    }
}
