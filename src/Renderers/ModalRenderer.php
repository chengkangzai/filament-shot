<?php

namespace CCK\FilamentShot\Renderers;

use Illuminate\Support\ViewErrorBag;

class ModalRenderer extends BaseRenderer
{
    protected ?string $heading = null;

    protected ?string $description = null;

    protected ?string $icon = null;

    protected string $iconColor = 'primary';

    protected string $color = 'primary';

    protected string $submitLabel = 'Confirm';

    protected string $cancelLabel = 'Cancel';

    public function heading(string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function iconColor(string $color): static
    {
        $this->iconColor = $color;

        return $this;
    }

    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function submitLabel(string $label): static
    {
        $this->submitLabel = $label;

        return $this;
    }

    public function cancelLabel(string $label): static
    {
        $this->cancelLabel = $label;

        return $this;
    }

    protected function renderContent(): string
    {
        if (! isset(app('view')->getShared()['errors'])) {
            app('view')->share('errors', new ViewErrorBag);
        }

        return view('filament-shot::components.modal', [
            'heading' => $this->heading,
            'description' => $this->description,
            'icon' => $this->icon,
            'iconColor' => $this->iconColor,
            'color' => $this->color,
            'submitLabel' => $this->submitLabel,
            'cancelLabel' => $this->cancelLabel,
            'content' => '',
            'darkMode' => $this->isDarkMode(),
        ])->render();
    }
}
