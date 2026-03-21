<?php

namespace CCK\FilamentShot\Renderers;

use CCK\FilamentShot\Concerns\HasCss;
use CCK\FilamentShot\Concerns\HasFont;
use CCK\FilamentShot\Concerns\HasHighlight;
use CCK\FilamentShot\Concerns\HasOutput;
use CCK\FilamentShot\Concerns\HasTheme;
use CCK\FilamentShot\Concerns\HasViewport;
use CCK\FilamentShot\Support\AssetResolver;

abstract class BaseRenderer
{
    use HasCss;
    use HasFont;
    use HasHighlight;
    use HasOutput;
    use HasTheme;
    use HasViewport;

    abstract protected function renderContent(): string;

    protected function safeCall(callable $callback, mixed $default): mixed
    {
        try {
            return $callback() ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    public function renderHtml(): string
    {
        $resolver = app(AssetResolver::class);

        $alpineRelativePaths = $resolver->getAlpineComponentRelativePaths();

        $html = view('filament-shot::layouts.base', [
            'darkMode' => $this->isDarkMode(),
            'primaryColor' => $this->getPrimaryColor(),
            'colorVariables' => $this->getColorCssVariables(),
            'themeCss' => $resolver->getThemeCssContent(),
            'extraCss' => implode("\n", array_filter([
                $resolver->getExtraCss(),
                $resolver->getPluginCssContent(),
                $this->getCustomCss(),
                $this->getHighlightCss(),
            ])),
            'coreJsUrls' => $resolver->getCoreJsFileUrls(),
            'content' => $this->renderContent(),
            'contentWidth' => $this->getWidth() . 'px',
            'font' => $this->getFont(),
        ])->render();

        return $this->sanitizeHtml($html, $alpineRelativePaths);
    }

    /**
     * Sanitize rendered HTML for static screenshot rendering.
     *
     * Extracts (x-data componentName, x-load-src relativePath) pairs from the HTML,
     * matches them to loaded plugin JS, and generates correct Alpine.data() registrations
     * using the actual component name from x-data (e.g. 'phoneInputFormComponent'),
     * not the asset ID (e.g. 'filament-phone-input'). Removes x-load attributes since
     * the component functions are pre-registered. Strips Livewire loading indicators.
     *
     * @param  string[]  $alpineRelativePaths  relative public paths of pre-registered components
     */
    protected function sanitizeHtml(string $html, array $alpineRelativePaths = []): string
    {
        if (! empty($alpineRelativePaths)) {
            // Extract x-data component names paired with their x-load-src relative paths.
            // Elements with x-load have both attributes on the same opening tag.
            // We inject <script> registration snippets for any matched components.
            $registrations = $this->extractAlpineComponentRegistrations($html, $alpineRelativePaths);

            $snippet = ! empty($registrations)
                ? '<script>' . implode('', $registrations) . '</script>'
                : '';

            $html = str_replace('<!-- __FILAMENT_SHOT_PLUGIN_JS__ -->', $snippet, $html);

            $html = preg_replace('/\s*x-load-src="[^"]*"/', '', $html);
            $html = preg_replace('/\s*x-load\b(?!-)(?:\s*=\s*"[^"]*")?/', '', $html);
        } else {
            $html = preg_replace('/\s*x-load-src="[^"]*localhost[^"]*"/', '', $html);
            $html = str_replace('<!-- __FILAMENT_SHOT_PLUGIN_JS__ -->', '', $html);
        }

        // Remove x-load-css (plugin CSS is already inlined via getPluginCssContent).
        $html = preg_replace('/\s*x-load-css="[^"]*"/', '', $html);

        // Remove Livewire loading indicator elements.
        $html = preg_replace('/<svg\b[^>]*wire:loading\.delay[^>]*>.*?<\/svg>/s', '', $html);
        $html = preg_replace('/\s*wire:loading\.remove[^"]*="[^"]*"/', '', $html);
        $html = preg_replace('/\s*wire:target="[^"]*"/', '', $html);

        return $html;
    }

    /**
     * Scan HTML for elements that have both x-data and x-load-src, extract the
     * component name from x-data and map it to a pre-loaded asset path.
     *
     * Returns an array of JS snippets: document.addEventListener('alpine:init', ...)
     *
     * @param  string[]  $alpineRelativePaths
     * @return string[]
     */
    protected function extractAlpineComponentRegistrations(string $html, array $alpineRelativePaths): array
    {
        $resolver = app(AssetResolver::class);
        $pathToJs = $resolver->getPluginAlpineComponentJsByPath();
        $registrations = [];

        // Match opening tags that have x-load-src and x-data on the same element.
        // x-data may come before or after x-load-src within the tag.
        preg_match_all(
            '/x-data="(\w+)\([^"]*"\s[^>]*x-load-src="([^"]+)"|x-load-src="([^"]+)"[^>]*x-data="(\w+)\(/',
            $html,
            $matches,
            PREG_SET_ORDER,
        );

        foreach ($matches as $match) {
            // Either order: x-data first (groups 1,2) or x-load-src first (groups 3,4)
            $componentName = $match[1] ?: $match[4];
            $srcUrl = $match[2] ?: $match[3];

            $parsed = parse_url($srcUrl);
            $relativePath = ltrim($parsed['path'] ?? '', '/');

            if (in_array($relativePath, $alpineRelativePaths, true) && isset($pathToJs[$relativePath])) {
                // Transform the module JS with the correct Alpine component name
                $registrations[$componentName] = $this->buildAlpineRegistration(
                    $componentName,
                    $pathToJs[$relativePath],
                );
            }
        }

        return array_values($registrations);
    }

    protected function buildAlpineRegistration(string $componentName, string $jsContent): string
    {
        // Extract the exported default variable name from: export{F0 as default}
        if (! preg_match('/export\s*\{(\w+)\s+as\s+default\}/', $jsContent, $m)) {
            return $jsContent;
        }

        $exportedVar = $m[1];

        // Strip the export statement and append Alpine.data() registration.
        $js = preg_replace('/export\s*\{[^}]+\}[;\s]*$/', '', $jsContent);

        return $js . "document.addEventListener('alpine:init',()=>{Alpine.data('{$componentName}',{$exportedVar});},{once:true});";
    }
}
