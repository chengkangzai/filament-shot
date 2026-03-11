<?php

namespace CCK\FilamentShot\Renderers;

use CCK\FilamentShot\Livewire\ShotFormComponent;
use Filament\Forms\Components\Select;
use Illuminate\Support\ViewErrorBag;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

class FormRenderer extends BaseRenderer
{
    protected array $state = [];

    protected ?string $modalHeading = null;

    protected ?string $modalDescription = null;

    protected ?string $modalIcon = null;

    protected string $modalIconColor = 'primary';

    protected string $modalColor = 'primary';

    protected string $modalSubmitLabel = 'Submit';

    protected string $modalCancelLabel = 'Cancel';

    /** @var array<string> Field names whose select dropdowns should render open */
    protected array $openFields = [];

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

    public function modalIcon(string $icon): static
    {
        $this->modalIcon = $icon;

        return $this;
    }

    public function modalIconColor(string $color): static
    {
        $this->modalIconColor = $color;

        return $this;
    }

    public function modalColor(string $color): static
    {
        $this->modalColor = $color;

        return $this;
    }

    public function modalCancelLabel(string $label): static
    {
        $this->modalCancelLabel = $label;

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
                'icon' => $this->modalIcon,
                'iconColor' => $this->modalIconColor,
                'color' => $this->modalColor,
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
        $html = $this->injectMultiSelectState($html, $data);
        $html = $this->injectTextareaContent($html, $data);
        $html = $this->injectToggleState($html, $data);

        if (! empty($this->openFields)) {
            $html = $this->injectSelectOpenState($html, $data);
        }

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

    /**
     * Inject selected tags into multi-select fields.
     *
     * Multi-selects use Alpine.js to render selected values as tags.
     * Since JS doesn't run, we inject tag elements for each selected value.
     */
    protected function injectMultiSelectState(string $html, array $data): string
    {
        $optionMaps = $this->collectMultiSelectOptions($this->components);

        // Find all multi-select components and inject tags.
        // Match x-data="selectFormComponent({...isMultiple: true...state: $wire.$entangle('data.FIELD')...})"
        if (preg_match_all('/x-data="selectFormComponent\(\{.*?isMultiple:\s*true.*?state:\s*\$wire\.\$entangle\(&#039;data\.([^&]+)&#039;/s', $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            // Process in reverse order so offsets remain valid
            foreach (array_reverse($matches) as $match) {
                $fieldPath = $match[1][0];
                $matchPos = $match[0][1];
                $values = data_get($data, $fieldPath);

                if (! is_array($values) || empty($values)) {
                    continue;
                }

                $options = $optionMaps[$fieldPath] ?? [];
                $tagsHtml = '';

                foreach ($values as $value) {
                    $label = $options[$value] ?? $value;
                    $tagsHtml .= '<span class="fi-badge fi-size-sm fi-color fi-color-primary fi-bg-color-50 fi-text-color-600 dark:fi-bg-color-600 dark:fi-text-color-50" style="display: inline-flex; margin: 0.125rem;">'
                        . '<span class="fi-badge-label">' . e($label) . '</span>'
                        . '</span>';
                }

                $wrapper = '<div style="display: flex; flex-wrap: wrap; gap: 0.25rem; padding: 0.5rem;">' . $tagsHtml . '</div>';

                // Search backwards from match position to find fi-input-wrp-content-ctn
                $searchStart = max(0, $matchPos - 3000);
                $searchBack = substr($html, $searchStart, $matchPos - $searchStart);
                $ctnPos = strrpos($searchBack, 'fi-input-wrp-content-ctn');

                if ($ctnPos !== false) {
                    $insertPos = strpos($searchBack, '>', $ctnPos);
                    if ($insertPos !== false) {
                        $absolutePos = $searchStart + $insertPos + 1;
                        $html = substr($html, 0, $absolutePos) . $wrapper . substr($html, $absolutePos);
                    }
                }
            }
        }

        return $html;
    }

    /**
     * Collect option maps from multi-select components recursively.
     *
     * @return array<string, array<string, string>>
     */
    protected function collectMultiSelectOptions(array $components, string $prefix = ''): array
    {
        $maps = [];

        foreach ($components as $component) {
            if ($component instanceof Select) {
                try {
                    if ($component->isMultiple()) {
                        $name = $prefix . $component->getName();
                        $maps[$name] = $component->getOptions();
                    }
                } catch (\Throwable) {
                    // Skip if we can't resolve
                }
            }

            // Recurse into child components (Section, Grid, Builder, etc.)
            try {
                $children = $component->getChildComponents();
                if (! empty($children)) {
                    $childPrefix = $prefix;

                    // Builder blocks nest under {builder}.{uuid}.data.
                    // Repeater items nest under {repeater}.{uuid}.
                    // For direct children, no extra prefix needed.
                    $maps = array_merge($maps, $this->collectMultiSelectOptions($children, $childPrefix));
                }
            } catch (\Throwable) {
                // Skip if no children
            }
        }

        return $maps;
    }

    /**
     * Inject a dropdown overlay for select fields listed in openFields.
     *
     * Finds each native <select> by its wire:model path, collects its options,
     * and appends a styled dropdown list after the select's input wrapper.
     */
    protected function injectSelectOpenState(string $html, array $data): string
    {
        $optionMaps = $this->collectSelectOptions($this->components);

        foreach ($this->openFields as $fieldName) {
            $options = $optionMaps[$fieldName] ?? [];
            $selectedValue = data_get($data, $fieldName);

            if (empty($options)) {
                continue;
            }

            // Find the native select element by its wire:model attribute
            $selectPattern = '/<select[^>]*wire:model(?:\.[\\w.]+)?="data\.' . preg_quote($fieldName, '/') . '"[^>]*>.*?<\/select>/s';

            if (! preg_match($selectPattern, $html, $selectMatch, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $selectEnd = $selectMatch[0][1] + strlen($selectMatch[0][0]);

            // Find the closing </div> of the fi-input-wrp-content-ctn after the select
            $afterSelect = substr($html, $selectEnd, 2000);
            $closingPos = strpos($afterSelect, '</div>');

            if ($closingPos === false) {
                continue;
            }

            // Build the dropdown HTML
            $dropdownHtml = $this->buildSelectDropdown($options, $selectedValue);

            // Find the fi-input-wrp div that contains this select, and append dropdown after it
            // Search forward from the select to find the fi-input-wrp closing divs
            $searchAfter = substr($html, $selectEnd);

            // We need to find the end of the fi-input-wrp div (2 closing divs after content-ctn)
            // content-ctn closes first, then fi-input-wrp closes
            $divDepth = 0;
            $insertOffset = 0;
            $found = false;

            for ($i = 0; $i < strlen($searchAfter) - 6; $i++) {
                if (substr($searchAfter, $i, 5) === '</div') {
                    $divDepth++;
                    if ($divDepth >= 2) {
                        // Find the > after </div
                        $closeBracket = strpos($searchAfter, '>', $i);
                        $insertOffset = $closeBracket + 1;
                        $found = true;

                        break;
                    }
                }
            }

            if (! $found) {
                continue;
            }

            $absoluteInsertPos = $selectEnd + $insertOffset;
            $html = substr($html, 0, $absoluteInsertPos) . $dropdownHtml . substr($html, $absoluteInsertPos);
        }

        return $html;
    }

    /**
     * Build the dropdown overlay HTML for an open select.
     */
    protected function buildSelectDropdown(array $options, mixed $selectedValue): string
    {
        $itemsHtml = '';

        foreach ($options as $value => $label) {
            $isSelected = (string) $value === (string) $selectedValue;
            $selectedClass = $isSelected ? ' fi-color fi-color-primary fi-bg-color-50 dark:fi-bg-color-600' : '';
            $checkIcon = $isSelected
                ? '<svg class="fi-icon fi-size-md" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 1.25rem; height: 1.25rem; flex-shrink: 0;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>'
                : '';

            $itemsHtml .= '<div class="fi-select-option' . $selectedClass . '" style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; font-size: 0.875rem; cursor: pointer;">'
                . '<span>' . e($label) . '</span>'
                . $checkIcon
                . '</div>';
        }

        return '<div style="margin-top: 0.25rem; background: white; border: 1px solid rgb(229 231 235); border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1); overflow: hidden;">'
            . $itemsHtml
            . '</div>';
    }

    /**
     * Collect option maps from all select components recursively.
     *
     * @return array<string, array<string, string>>
     */
    protected function collectSelectOptions(array $components, string $prefix = ''): array
    {
        $maps = [];

        foreach ($components as $component) {
            if ($component instanceof Select) {
                try {
                    $name = $prefix . $component->getName();
                    $maps[$name] = $component->getOptions();
                } catch (\Throwable) {
                    // Skip if we can't resolve
                }
            }

            try {
                $children = $component->getChildComponents();
                if (! empty($children)) {
                    $maps = array_merge($maps, $this->collectSelectOptions($children, $prefix));
                }
            } catch (\Throwable) {
                // Skip if no children
            }
        }

        return $maps;
    }

    protected function ensureViewErrorBag(): void
    {
        if (! isset(app('view')->getShared()['errors'])) {
            app('view')->share('errors', new ViewErrorBag);
        }
    }
}
