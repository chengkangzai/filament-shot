<?php

namespace CCK\FilamentShot\Concerns;

trait HasCss
{
    protected string $customCss = '';

    /**
     * Inject raw CSS into the screenshot.
     * Useful for 3rd-party Filament plugins that register custom CSS outside
     * of Filament's asset pipeline (e.g. inline overrides, unpublished themes).
     */
    public function css(string $css): static
    {
        $this->customCss .= $css;

        return $this;
    }

    /**
     * Load CSS from a file path and inject it into the screenshot.
     * Pass the absolute path to a plugin's compiled CSS file.
     */
    public function cssFile(string $path): static
    {
        if (file_exists($path)) {
            $this->customCss .= file_get_contents($path) . "\n";
        }

        return $this;
    }

    public function getCustomCss(): string
    {
        return $this->customCss;
    }
}
