<?php

namespace CCK\FilamentShot\Concerns;

trait HasTailwind
{
    protected bool $tailwindCdn = false;

    /**
     * Inject the Tailwind Play CDN into the rendered HTML so that utility classes
     * used in custom Blade templates are available. Useful when blade()/view() templates
     * use Tailwind classes that are absent from Filament's purged CSS bundle.
     *
     * Requires network access during screenshot rendering (the CDN is loaded via HTTP).
     */
    public function withTailwind(): static
    {
        $this->tailwindCdn = true;

        return $this;
    }
}
