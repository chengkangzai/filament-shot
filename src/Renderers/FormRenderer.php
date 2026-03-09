<?php

namespace CCK\FilamentShot\Renderers;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\View\Components\ToggleComponent;

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
            fn ($component) => $this->processComponent($component),
            $this->components,
        );

        return view('filament-shot::components.form', [
            'fields' => $fields,
        ])->render();
    }

    protected function processComponent(object $component): array
    {
        if ($component instanceof Section) {
            return $this->extractSectionData($component);
        }

        if ($component instanceof Fieldset) {
            return $this->extractFieldsetData($component);
        }

        if ($component instanceof Grid) {
            return $this->extractGridData($component);
        }

        return $this->extractFieldData($component);
    }

    protected function extractSectionData(object $component): array
    {
        $children = $this->getSchemaChildren($component);

        return [
            'component_type' => 'layout',
            'layout' => 'section',
            'heading' => $this->safeCall(fn () => $component->getHeading(), ''),
            'description' => $this->safeCall(fn () => $component->getDescription(), null),
            'compact' => $this->safeCall(fn () => $component->isCompact(), false),
            'collapsed' => $this->safeCall(fn () => $component->isCollapsed(), false),
            'children' => array_map(fn ($child) => $this->processComponent($child), $children),
        ];
    }

    protected function extractFieldsetData(object $component): array
    {
        $children = $this->getSchemaChildren($component);

        return [
            'component_type' => 'layout',
            'layout' => 'fieldset',
            'label' => $this->safeCall(fn () => $component->getLabel(), ''),
            'children' => array_map(fn ($child) => $this->processComponent($child), $children),
        ];
    }

    protected function extractGridData(object $component): array
    {
        $children = $this->getSchemaChildren($component);
        $columns = $this->safeCall(fn () => $component->getColumns(), ['lg' => 2]);

        if (is_array($columns)) {
            $columns = $columns['lg'] ?? $columns['default'] ?? 2;
        }

        return [
            'component_type' => 'layout',
            'layout' => 'grid',
            'columns' => (int) $columns,
            'children' => array_map(fn ($child) => $this->processComponent($child), $children),
        ];
    }

    /**
     * Get child components from a layout component by accessing the stored schema directly.
     */
    protected function getSchemaChildren(object $component): array
    {
        try {
            $ref = new \ReflectionProperty($component, 'childComponents');
            $ref->setAccessible(true);
            $childComponents = $ref->getValue($component);
            $default = $childComponents['default'] ?? [];

            if ($default instanceof \Closure) {
                $default = $default();
            }

            return is_array($default) ? $default : [];
        } catch (\Throwable) {
            return [];
        }
    }

    protected function extractFieldData(object $component): array
    {
        $name = $this->safeCall(fn () => $component->getName(), '');
        $type = $this->resolveFieldType($component);

        $data = [
            'component_type' => 'field',
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

        if ($component instanceof Toggle) {
            $onColor = $this->safeCall(fn () => $component->getOnColor(), 'primary') ?? 'primary';
            $offColor = $this->safeCall(fn () => $component->getOffColor(), 'gray') ?? 'gray';
            $data['onColorClasses'] = implode(' ', FilamentColor::getComponentClasses(ToggleComponent::class, $onColor));
            $data['offColorClasses'] = implode(' ', FilamentColor::getComponentClasses(ToggleComponent::class, $offColor));
        }

        if ($component instanceof Placeholder) {
            $data['content'] = $this->safeCall(fn () => $component->getContent(), '');
        }

        if ($component instanceof TagsInput) {
            $value = $this->state[$name] ?? [];
            $data['tags'] = is_array($value) ? $value : [];
        }

        if ($component instanceof KeyValue) {
            $value = $this->state[$name] ?? [];
            $data['pairs'] = is_array($value) ? $value : [];
        }

        if ($component instanceof Repeater) {
            $value = $this->state[$name] ?? [];
            $data['items'] = is_array($value) ? $value : [];
            $children = $this->getSchemaChildren($component);
            $data['children'] = array_map(fn ($child) => $this->processComponent($child), $children);
        }

        if ($component instanceof ColorPicker) {
            $data['value'] = $this->state[$name] ?? '#000000';
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
            $component instanceof DatePicker => 'date-picker',
            $component instanceof DateTimePicker => 'date-time-picker',
            $component instanceof FileUpload => 'file-upload',
            $component instanceof ColorPicker => 'color-picker',
            $component instanceof TagsInput => 'tags-input',
            $component instanceof KeyValue => 'key-value',
            $component instanceof RichEditor => 'rich-editor',
            $component instanceof MarkdownEditor => 'markdown-editor',
            $component instanceof Repeater => 'repeater',
            default => 'text-input',
        };
    }
}
