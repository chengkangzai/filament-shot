<?php

namespace CCK\FilamentShot\Support;

use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Livewire\LivewireServiceProvider;

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
     *
     * URL references (flag sprites, icons) are rewritten to base64 data URIs so
     * the images load correctly when the CSS is inlined in a static HTML file.
     */
    public function getPluginCssContent(): string
    {
        $css = '';

        foreach (FilamentAsset::getStyles() as $asset) {
            $path = $asset->getPath();

            if (! $path || $asset->isRemote() || ! file_exists($path)) {
                continue;
            }

            $css .= $this->rewriteCssUrls(file_get_contents($path), $path) . "\n";
        }

        return $css;
    }

    /**
     * Rewrite CSS url() references to base64 data URIs so they work when inlined.
     *
     * Handles two cases:
     * - Relative paths (e.g. `../img/flags.webp`): resolved relative to the CSS file
     * - Absolute /vendor/package-name/file paths: looked up in the package source tree
     */
    protected function rewriteCssUrls(string $css, string $cssFilePath): string
    {
        // Normalize the path to resolve any `../` segments before computing dirs
        $realCssDir = realpath(dirname($cssFilePath)) ?: dirname($cssFilePath);
        $packageRoot = dirname($realCssDir, 2);

        return preg_replace_callback(
            '/url\(([\'"]?)([^)\'"\n]+)\1\)/',
            function (array $match) use ($realCssDir, $packageRoot): string {
                $url = trim($match[2]);
                $quote = $match[1];

                if (str_starts_with($url, 'data:') || preg_match('/^https?:\/\//', $url)) {
                    return $match[0];
                }

                $resolved = null;

                if (! str_starts_with($url, '/')) {
                    // Relative path: resolve relative to CSS file directory
                    $candidate = realpath($realCssDir . '/' . $url);

                    if ($candidate && file_exists($candidate)) {
                        $resolved = $candidate;
                    }
                } elseif (preg_match('/^\/vendor\/[^\/]+\/(.+)$/', $url, $vendorMatch)) {
                    // Absolute /vendor/package-name/file path — find it in the package source.
                    // Packages publish assets from their source tree; search common image dirs.
                    // PHP glob() doesn't support **, so we list explicit candidate paths.
                    $filename = basename($vendorMatch[1]);
                    $candidates = [
                        $packageRoot . '/images/vendor/intl-tel-input/build/' . $filename,
                        $packageRoot . '/resources/images/' . $filename,
                        $packageRoot . '/dist/img/' . $filename,
                    ];

                    foreach ($candidates as $candidate) {
                        if (file_exists($candidate)) {
                            $resolved = $candidate;

                            break;
                        }
                    }
                }

                if (! $resolved) {
                    return $match[0];
                }

                $mime = mime_content_type($resolved) ?: 'application/octet-stream';
                $data = base64_encode((string) file_get_contents($resolved));

                return "url({$quote}data:{$mime};base64,{$data}{$quote})";
            },
            $css,
        );
    }

    /**
     * Return file:// URLs for Filament's core JavaScript bundles.
     *
     * These include Alpine.js and Filament component registrations.
     * Using file:// URLs avoids inlining (saves ~200KB per render) while still
     * allowing Chromium to load them when rendering from a temp file:// page.
     */
    public function getCoreJsFileUrls(): array
    {
        $urls = [];

        // Livewire bundles Alpine.js and calls Alpine.start() — must load first.
        // Filament's JS bundles assume window.Alpine is already set when they run.
        $livewireDir = dirname((new \ReflectionClass(LivewireServiceProvider::class))->getFileName(), 2);
        $livewirePath = $livewireDir . '/dist/livewire.min.js';

        if (file_exists($livewirePath)) {
            $urls[] = 'file://' . $livewirePath;
        }

        foreach ([
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            ActionsServiceProvider::class,
        ] as $provider) {
            $dir = dirname((new \ReflectionClass($provider))->getFileName(), 2);
            $path = $dir . '/dist/index.js';

            if (file_exists($path) && filesize($path) > 20) {
                $urls[] = 'file://' . $path;
            }
        }

        return $urls;
    }

    /**
     * Return transformed plugin AlpineComponent JS ready to include as a regular script.
     *
     * Filament plugin JS bundles are ES modules (using export syntax) designed to be
     * loaded via Chromium's dynamic import(). However, dynamic import() of file:// URLs
     * is blocked by Chromium's security policy. We transform each module's default export
     * into an Alpine.data() registration so it can run as a plain <script> before Alpine starts.
     *
     * Transform: export{FunctionName as default} → document.addEventListener('alpine:init', ...)
     */
    public function getPluginAlpineComponentJs(): string
    {
        $js = '';

        foreach (FilamentAsset::getAlpineComponents() as $asset) {
            $path = $asset->getPath();

            if (! $path || $asset->isRemote() || ! file_exists($path)) {
                continue;
            }

            $content = file_get_contents($path);
            $id = $asset->getId();

            // Transform ES module export to Alpine.data() registration.
            // Matches: export{FunctionName as default} or export{FunctionName}
            $transformed = preg_replace_callback(
                '/export\s*\{([^}]+)\}[;\s]*$/',
                function ($m) use ($id) {
                    $exports = array_map('trim', explode(',', $m[1]));
                    $defaultExport = null;

                    foreach ($exports as $export) {
                        if (str_contains($export, 'as default')) {
                            $defaultExport = trim(explode('as', $export)[0]);

                            break;
                        }
                    }

                    if (! $defaultExport) {
                        $defaultExport = trim($exports[0]);
                    }

                    return "document.addEventListener('alpine:init',()=>{Alpine.data('{$id}',{$defaultExport});},{once:true});";
                },
                $content,
            );

            $js .= $transformed . "\n";
        }

        return $js;
    }

    /**
     * Return a map of relativePath => raw JS content for each registered AlpineComponent.
     *
     * Used by BaseRenderer to match x-load-src URLs from the rendered HTML to
     * the correct JS file content, so we can transform and register it under
     * the right Alpine component name (from x-data, not the asset ID).
     *
     * @return array<string, string> relativePath => file content
     */
    public function getPluginAlpineComponentJsByPath(): array
    {
        $map = [];

        foreach (FilamentAsset::getAlpineComponents() as $asset) {
            $path = $asset->getPath();

            if ($path && ! $asset->isRemote() && file_exists($path)) {
                $map[$asset->getRelativePublicPath()] = file_get_contents($path);
            }
        }

        return $map;
    }

    /**
     * Return relative public paths for all registered local AlpineComponent assets.
     */
    public function getAlpineComponentRelativePaths(): array
    {
        $paths = [];

        foreach (FilamentAsset::getAlpineComponents() as $asset) {
            if ($asset->getPath() && ! $asset->isRemote()) {
                $paths[] = $asset->getRelativePublicPath();
            }
        }

        return $paths;
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
