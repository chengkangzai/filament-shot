<?php

namespace CCK\FilamentShot\Support;

use Filament\FilamentServiceProvider;
use Filament\Support\Facades\FilamentAsset;

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

    /**
     * Auto-discover and return CSS from all Filament plugin assets.
     *
     * 3rd-party plugins register their CSS via FilamentAsset::register().
     * This reads those source files directly so their styles are included
     * in screenshots without requiring `php artisan filament:assets` to be run.
     */
    public function getPluginCssContent(): string
    {
        $css = '';

        foreach (FilamentAsset::getStyles() as $asset) {
            $path = $asset->getPath();

            if ($path && ! $asset->isRemote() && file_exists($path)) {
                $css .= file_get_contents($path) . "\n";
            }
        }

        return $css;
    }

    protected function getThemeCssPath(): string
    {
        if ($configPath = config('filament-shot.css.theme_path')) {
            return $configPath;
        }

        // Resolve the path from the actual Filament package location.
        // base_path('vendor/...') fails in Testbench CI environments where
        // the vendor symlink may not exist or points to a wrong directory.
        $filamentDir = dirname((new \ReflectionClass(FilamentServiceProvider::class))->getFileName(), 2);

        return $filamentDir . '/dist/theme.css';
    }
}
