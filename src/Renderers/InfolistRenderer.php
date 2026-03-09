<?php

namespace CCK\FilamentShot\Renderers;

use CCK\FilamentShot\Livewire\ShotInfolistComponent;
use Illuminate\Support\ViewErrorBag;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

class InfolistRenderer extends BaseRenderer
{
    protected array $state = [];

    public function __construct(
        protected array $entries = [],
    ) {}

    public function state(array $state): static
    {
        $this->state = $state;

        return $this;
    }

    protected function renderContent(): string
    {
        $this->ensureViewErrorBag();

        ShotInfolistComponent::prepareFor($this->entries, $this->state);

        $component = new ShotInfolistComponent;
        $component->boot();
        $component->mount();

        $extendBlade = app(ExtendBlade::class);
        $extendBlade->startLivewireRendering($component);
        app('view')->share('__livewire', $component);

        try {
            return $component->getSchema('infolist')->toHtml();
        } finally {
            $extendBlade->endLivewireRendering();
            app('view')->share('__livewire', null);
        }
    }

    protected function ensureViewErrorBag(): void
    {
        if (! isset(app('view')->getShared()['errors'])) {
            app('view')->share('errors', new ViewErrorBag);
        }
    }
}
