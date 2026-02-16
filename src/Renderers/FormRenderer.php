<?php

namespace CCK\FilamentShot\Renderers;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

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
        $fields = array_map(
            fn ($component) => $this->extractFieldData($component),
            $this->components,
        );

        return view('filament-shot::components.form', [
            'fields' => $fields,
        ])->render();
    }

    protected function extractFieldData(object $component): array
    {
        $name = $this->safeCall(fn () => $component->getName(), '');
        $type = $this->resolveFieldType($component);

        $data = [
            'type' => $type,
            'name' => $name,
            'label' => $this->safeCall(fn () => $component->getLabel(), $name),
            'value' => $this->state[$name] ?? null,
            'disabled' => $this->safeCall(fn () => $component->isDisabled(), false),
        ];

        if (method_exists($component, 'getPlaceholder')) {
            $data['placeholder'] = $this->safeCall(fn () => $component->getPlaceholder(), '');
        }

        if ($component instanceof Select) {
            $data['options'] = $this->safeCall(fn () => $component->getOptions(), []);
        }

        if ($component instanceof Radio) {
            $data['options'] = $this->safeCall(fn () => $component->getOptions(), []);
        }

        if ($component instanceof TextInput) {
            $data['input_type'] = $this->safeCall(fn () => $component->getType(), 'text');
            $data['readonly'] = $this->safeCall(fn () => $component->isReadOnly(), false);
        }

        if ($component instanceof Textarea) {
            $data['rows'] = $this->safeCall(fn () => $component->getRows(), 3);
        }

        if ($component instanceof Placeholder) {
            $data['content'] = $this->safeCall(fn () => $component->getContent(), '');
        }

        return $data;
    }

    protected function resolveFieldType(object $component): string
    {
        return match (true) {
            $component instanceof TextInput => 'text-input',
            $component instanceof Select => 'select',
            $component instanceof Textarea => 'textarea',
            $component instanceof Toggle => 'toggle',
            $component instanceof Checkbox => 'checkbox',
            $component instanceof Radio => 'radio',
            $component instanceof Placeholder => 'placeholder',
            default => 'text-input',
        };
    }

    protected function safeCall(callable $callback, mixed $default): mixed
    {
        try {
            return $callback() ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }
}
