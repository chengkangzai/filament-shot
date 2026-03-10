<?php

namespace CCK\FilamentShot\Renderers;

use CCK\FilamentShot\Livewire\ShotFormComponent;
use Illuminate\Support\ViewErrorBag;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

class FormRenderer extends BaseRenderer
{
    protected array $state = [];

    protected ?string $modalHeading = null;

    protected ?string $modalDescription = null;

    protected string $modalSubmitLabel = 'Submit';

    protected string $modalCancelLabel = 'Cancel';

    public function __construct(
        protected array $components = [],
    ) {}

    public function state(array $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function modal(string $heading): static
    {
        $this->modalHeading = $heading;

        return $this;
    }

    public function modalDescription(string $description): static
    {
        $this->modalDescription = $description;

        return $this;
    }

    public function modalSubmitLabel(string $label): static
    {
        $this->modalSubmitLabel = $label;

        return $this;
    }

    public function modalCancelLabel(string $label): static
    {
        $this->modalCancelLabel = $label;

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

        $html = $this->injectFormValues($html, $component->data);

        if ($this->modalHeading !== null) {
            $html = view('filament-shot::components.modal', [
                'heading' => $this->modalHeading,
                'description' => $this->modalDescription,
                'submitLabel' => $this->modalSubmitLabel,
                'cancelLabel' => $this->modalCancelLabel,
                'content' => $html,
                'darkMode' => $this->isDarkMode(),
            ])->render();
        }

        return $html;
    }

    /**
     * Inject state values into static HTML for components that use wire:model.
     *
     * Filament uses wire:model and Alpine.js for data binding. Since we render
     * static HTML without JS, we post-process to add proper HTML attributes.
     */
    protected function injectFormValues(string $html, array $data): string
    {
        $html = $this->injectInputValues($html, $data);
        $html = $this->injectCheckboxState($html, $data);
        $html = $this->injectSelectState($html, $data);
        $html = $this->injectTextareaContent($html, $data);
        $html = $this->injectToggleState($html, $data);

        return $html;
    }

    /**
     * Add value attributes to text/number/date inputs.
     */
    protected function injectInputValues(string $html, array $data): string
    {
        return preg_replace_callback(
            '/<input(\s[^>]*?)wire:model(?:\.[\w.]+)?="data\.([^"]+)"([^>]*?)\s*\/?>/s',
            function ($matches) use ($data) {
                $before = $matches[1];
                $fieldPath = $matches[2];
                $after = $matches[3];
                $full = $matches[0];

                // Skip checkboxes — handled separately
                if (preg_match('/type=["\']checkbox["\']/', $before . $after)) {
                    return $full;
                }

                $value = data_get($data, $fieldPath);

                if ($value === null || is_array($value)) {
                    return $full;
                }

                // Replace existing value or add one
                if (preg_match('/\bvalue\s*=\s*"[^"]*"/', $full)) {
                    return preg_replace('/\bvalue\s*=\s*"[^"]*"/', 'value="' . e($value) . '"', $full);
                }

                return str_replace('/>', ' value="' . e($value) . '" />', $full);
            },
            $html,
        );
    }

    /**
     * Add checked attribute to checkboxes when state is truthy.
     */
    protected function injectCheckboxState(string $html, array $data): string
    {
        return preg_replace_callback(
            '/<input(\s[^>]*?)type=["\']checkbox["\']([^>]*?)wire:model(?:\.[\w.]+)?="data\.([^"]+)"([^>]*?)\s*\/?>/s',
            function ($matches) use ($data) {
                $fieldPath = $matches[3];
                $value = data_get($data, $fieldPath);

                if ($value) {
                    return str_replace('/>', ' checked />', $matches[0]);
                }

                return $matches[0];
            },
            $html,
        );
    }

    /**
     * Add selected attribute to the matching option in select elements.
     */
    protected function injectSelectState(string $html, array $data): string
    {
        return preg_replace_callback(
            '/<select(\s[^>]*?)wire:model(?:\.[\w.]+)?="data\.([^"]+)"([^>]*?)>(.*?)<\/select>/s',
            function ($matches) use ($data) {
                $fieldPath = $matches[2];
                $value = data_get($data, $fieldPath);
                $selectHtml = $matches[0];

                if ($value === null) {
                    return $selectHtml;
                }

                // Remove the invalid value attribute on <select> tag
                $selectHtml = preg_replace('/(<select[^>]*)\s+value="[^"]*"/', '$1', $selectHtml);

                // Add selected to matching option
                $escapedValue = preg_quote(e($value), '/');

                return preg_replace(
                    '/(<option\s[^>]*value="' . $escapedValue . '")((?:\s[^>]*)?>)/',
                    '$1 selected$2',
                    $selectHtml,
                );
            },
            $html,
        );
    }

    /**
     * Put textarea value between opening and closing tags.
     */
    protected function injectTextareaContent(string $html, array $data): string
    {
        return preg_replace_callback(
            '/(<textarea\s[^>]*?wire:model(?:\.[\w.]+)?="data\.([^"]+)"[^>]*?)(?:\s+value="[^"]*")?\s*>(.*?)<\/textarea>/s',
            function ($matches) use ($data) {
                $openTag = $matches[1];
                $fieldPath = $matches[2];
                $existingContent = trim($matches[3]);
                $value = data_get($data, $fieldPath);

                if ($value === null || $existingContent !== '') {
                    return $matches[0];
                }

                // Remove any stray value attribute on the textarea tag
                $openTag = preg_replace('/\s+value="[^"]*"/', '', $openTag);

                return $openTag . '>' . e($value) . '</textarea>';
            },
            $html,
        );
    }

    /**
     * Set toggle button on/off state via CSS classes.
     *
     * Toggles use x-bind:class with Alpine.js to switch between
     * fi-toggle-on and fi-toggle-off. We resolve this statically and
     * remove x-cloak so the button is visible without Alpine.
     */
    protected function injectToggleState(string $html, array $data): string
    {
        return preg_replace_callback(
            '/<button\s[^>]*?x-data="\{\s*state:\s*\$wire\.\$entangle\(&#039;data\.([^&]+)&#039;[^)]*\)\s*\}"[^>]*?class="([^"]*fi-toggle[^"]*)"[^>]*>/s',
            function ($matches) use ($data) {
                $fieldPath = $matches[1];
                $value = data_get($data, $fieldPath);

                $onClasses = 'fi-toggle-on fi-color fi-color-primary fi-bg-color-600 fi-text-color-600 dark:fi-bg-color-600';
                $offClasses = 'fi-toggle-off';
                $stateClasses = $value ? $onClasses : $offClasses;

                $result = $matches[0];

                // Remove x-cloak so the button is visible (CSS hides [x-cloak])
                $result = preg_replace('/\s*x-cloak\b/', '', $result);

                // Add the state classes to existing class attribute
                $result = preg_replace(
                    '/class="([^"]*fi-toggle[^"]*)"/',
                    'class="$1 ' . $stateClasses . '"',
                    $result,
                );

                // Set aria-checked
                $ariaValue = $value ? 'true' : 'false';
                $result = preg_replace('/aria-checked="[^"]*"/', 'aria-checked="' . $ariaValue . '"', $result);

                return $result;
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
