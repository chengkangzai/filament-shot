<?php

namespace CCK\FilamentShot\Renderers;

use Filament\Schemas\Components\Component;
use Illuminate\Support\ViewErrorBag;

class ModalRenderer extends BaseRenderer
{
    /** @var array<string> Field names whose select dropdowns should render open */
    protected array $openFields = [];

    protected array $state = [];

    protected ?string $heading = null;

    protected ?string $description = null;

    protected ?string $icon = null;

    protected string $iconColor = 'primary';

    protected string $color = 'primary';

    protected string $submitLabel = 'Confirm';

    protected string $cancelLabel = 'Cancel';

    /**
     * @param  array<Component>  $components  Body components rendered inside the modal
     */
    public function __construct(
        protected array $components = [],
    ) {}

    public function state(array $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Specify which select fields should render with their dropdown open.
     *
     * @param  array<string>  $fields  Field names to render open
     */
    public function openFields(array $fields): static
    {
        $this->openFields = $fields;

        return $this;
    }

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
            'content' => $this->renderBodyComponents(),
            'darkMode' => $this->isDarkMode(),
        ])->render();
    }

    /**
     * Render the optional body components by reusing FormRenderer's schema pipeline.
     *
     * The font, width, and colour theme are applied by this renderer's base layout,
     * so only the schema-affecting settings (state, open fields, dark mode) need to
     * be forwarded to the inner renderer.
     */
    protected function renderBodyComponents(): string
    {
        if ($this->components === []) {
            return '';
        }

        $form = (new FormRenderer($this->components))
            ->state($this->state)
            ->openFields($this->openFields);

        if ($this->darkMode !== null) {
            $this->darkMode ? $form->darkMode() : $form->lightMode();
        }

        return $form->renderSchemaHtml();
    }
}
