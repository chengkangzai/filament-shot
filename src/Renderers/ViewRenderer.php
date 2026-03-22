<?php

namespace CCK\FilamentShot\Renderers;

use Illuminate\Support\Facades\Blade;

class ViewRenderer extends BaseRenderer
{
    protected array $viewData = [];

    public function __construct(
        protected readonly ?string $viewName = null,
        protected readonly ?string $bladeTemplate = null,
    ) {}

    public function data(array $data): static
    {
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    protected function renderContent(): string
    {
        if ($this->viewName !== null) {
            return view($this->viewName, $this->viewData)->render();
        }

        return Blade::render($this->bladeTemplate ?? '', $this->viewData);
    }
}
