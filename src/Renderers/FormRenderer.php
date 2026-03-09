<?php

namespace CCK\FilamentShot\Renderers;

use CCK\FilamentShot\Livewire\ShotFormComponent;
use Illuminate\Support\ViewErrorBag;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

class FormRenderer extends BaseRenderer
{
    protected array $state = [];

    public function __construct(
        protected array $components = [],
    ) {}

    public function state(array $state): static
    {
        $this->state = $state;

        return $this;
    }

    protected function renderContent(): string
    {
        $this->ensureViewErrorBag();

        ShotFormComponent::prepareFor($this->components, $this->state);

        $component = new ShotFormComponent;
        $component->boot();
        $component->mount();

        // Register the component with Livewire's rendering stack so that
        // $this resolves to our component in Filament's Blade templates
        // (e.g., RichEditor uses $this->getId())
        $extendBlade = app(ExtendBlade::class);
        $extendBlade->startLivewireRendering($component);
        app('view')->share('__livewire', $component);

        try {
            $html = $component->getSchema('form')->toHtml();
        } finally {
            $extendBlade->endLivewireRendering();
            app('view')->share('__livewire', null);
        }

        return $this->injectFormValues($html, $component->data);
    }

    /**
     * Inject value attributes into inputs that use wire:model binding.
     *
     * Filament's Blade templates use wire:model for data binding, which requires
     * Livewire JS to populate values. Since we render static HTML for screenshots,
     * we need to add explicit value attributes.
     */
    protected function injectFormValues(string $html, array $data): string
    {
        // Match input/textarea elements with wire:model="data.fieldName"
        return preg_replace_callback(
            '/<(input|textarea|select)(\s[^>]*?)wire:model(?:\.[\w.]+)?="data\.([^"]+)"([^>]*?)(\s*\/?>)/s',
            function ($matches) use ($data) {
                $tag = $matches[1];
                $before = $matches[2];
                $fieldPath = $matches[3];
                $after = $matches[4];
                $close = $matches[5];

                $value = data_get($data, $fieldPath);

                if ($value === null) {
                    return $matches[0];
                }

                // Don't add value if already has one
                if (preg_match('/\bvalue\s*=/', $before . $after)) {
                    return $matches[0];
                }

                $escapedValue = e($value);

                return "<{$tag}{$before}wire:model=\"data.{$fieldPath}\"{$after} value=\"{$escapedValue}\"{$close}";
            },
            $html,
        );
    }

    protected function ensureViewErrorBag(): void
    {
        if (! isset(app('view')->getShared()['errors'])) {
            app('view')->share('errors', new ViewErrorBag);
        }
    }
}
