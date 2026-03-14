<?php

namespace CCK\FilamentShot\Renderers;

use CCK\FilamentShot\Concerns\HasFont;
use CCK\FilamentShot\Concerns\HasHighlight;
use CCK\FilamentShot\Concerns\HasOutput;
use CCK\FilamentShot\Concerns\HasTheme;
use CCK\FilamentShot\Concerns\HasViewport;
use CCK\FilamentShot\Support\AssetResolver;

abstract class BaseRenderer
{
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

        $html = view('filament-shot::layouts.base', [
            'darkMode' => $this->isDarkMode(),
            'primaryColor' => $this->getPrimaryColor(),
            'colorVariables' => $this->getColorCssVariables(),
            'themeCss' => $resolver->getThemeCssContent(),
            'extraCss' => $resolver->getExtraCss() . "\n" . $this->getHighlightCss(),
            'content' => $this->renderContent(),
            'contentWidth' => $this->getWidth() . 'px',
            'font' => $this->getFont(),
        ])->render();

        return $this->sanitizeHtml($html);
    }

    /**
     * Remove attributes that reference localhost URLs.
     *
     * Filament components use x-load-src with the app URL for Alpine.js
     * lazy-loading. In non-application contexts this resolves to
     * http://localhost which triggers Browsershot's security check.
     * These attributes serve no purpose in static screenshots.
     */
    protected function sanitizeHtml(string $html): string
    {
        // Remove x-load-src attributes (localhost URLs trigger Browsershot security)
        $html = preg_replace('/\s*x-load-src="[^"]*"/', '', $html);

        // Remove Livewire loading indicator elements.
        // Elements with wire:loading (without .remove) are spinners that only show
        // during Livewire requests. In static HTML they're visible and cause unwanted
        // loading icons. Elements with wire:loading.remove are the normal-state icons
        // that should remain visible.
        $html = preg_replace('/<svg\b[^>]*wire:loading\.delay[^>]*>.*?<\/svg>/s', '', $html);

        // Also strip wire:loading.remove attributes from normal icons so they render
        // without Livewire-specific attributes that serve no purpose in static HTML.
        $html = preg_replace('/\s*wire:loading\.remove[^"]*="[^"]*"/', '', $html);
        $html = preg_replace('/\s*wire:target="[^"]*"/', '', $html);

        return $html;
    }
}
