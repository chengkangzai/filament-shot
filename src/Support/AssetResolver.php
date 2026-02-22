<?php

namespace CCK\FilamentShot\Support;

class AssetResolver
{
    public function getThemeCssContent(): string
    {
        $path = $this->getThemeCssPath();

        if (! file_exists($path)) {
            return '';
        }

        return file_get_contents($path);
    }

    public function getExtraCss(): string
    {
        return config('filament-shot.css.extra', '');
    }

    protected function getThemeCssPath(): string
    {
        return config('filament-shot.css.theme_path')
            ?? base_path('vendor/filament/filament/dist/theme.css');
    }
}
