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
        if ($configPath = config('filament-shot.css.theme_path')) {
            return $configPath;
        }

        // Resolve the path from the actual Filament package location.
        // base_path('vendor/...') fails in Testbench CI environments where
        // the vendor symlink may not exist or points to a wrong directory.
        $filamentDir = dirname((new \ReflectionClass(\Filament\FilamentServiceProvider::class))->getFileName(), 2);

        return $filamentDir . '/dist/theme.css';
    }
}
