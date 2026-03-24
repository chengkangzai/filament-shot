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
        $html = $this->injectWireStateScript($html, $component->data);
        $html = $this->fixTabs($html);
        $html = $this->fixWizard($html);
        $html = $this->fixBuilder($html);
        $html = $this->fixRichEditor($html);
        $html = $this->fixMarkdownEditor($html);
        $html = $this->fixCodeEditor($html, $component->data);

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
     * Inject form state as a global JS object for the $wire Alpine magic stub.
     *
     * JS-driven components like PhoneInput use `$wire.$entangle('data.phone')`
     * to get their initial value. The $wire stub in base.blade.php reads from
     * `window.__filamentShotWireState` to provide those values at init time.
     */
    protected function injectWireStateScript(string $html, array $data): string
    {
        // Build data.* paths (Filament uses 'data.fieldName' as wire:model paths)
        $wireState = [];
        foreach ($data as $key => $value) {
            if (! is_array($value)) {
                $wireState["data.{$key}"] = $value;
            }
        }

        $json = json_encode($wireState, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return "<script>window.__filamentShotWireState = {$json};</script>\n" . $html;
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
        $html = $this->injectToggleButtonsState($html, $data);
        $html = $this->injectRadioState($html, $data);
        $html = $this->injectColorPickerState($html, $data);

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

                // Skip radio inputs — ToggleButtons use radio with fixed option values
                if (preg_match('/type=["\']radio["\']/', $before . $after)) {
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
     * Add checked attribute to the selected ToggleButtons radio input.
     *
     * ToggleButtons renders hidden radio inputs alongside styled <label> buttons.
     * CSS selectors like `input:checked + label` apply the active visual style.
     * We add `checked` to the radio input whose value matches the current state.
     */
    protected function injectToggleButtonsState(string $html, array $data): string
    {
        if (! str_contains($html, 'fi-fo-toggle-buttons-input')) {
            return $html;
        }

        return preg_replace_callback(
            '/<input(\s[^>]*?)class="([^"]*fi-fo-toggle-buttons-input[^"]*)"([^>]*?)\s*\/?>/s',
            function ($matches) use ($data) {
                $full = $matches[0];
                $attrs = $matches[1] . $matches[3];

                // Extract wire:model field path
                if (! preg_match('/wire:model(?:\.[\w.]+)?="data\.([^"]+)"/', $attrs, $wireMatch)) {
                    return $full;
                }

                $fieldPath = $wireMatch[1];
                $stateValue = data_get($data, $fieldPath);

                if ($stateValue === null) {
                    return $full;
                }

                // Extract the option value from this radio input's value attribute
                if (! preg_match('/\bvalue="([^"]*)"/', $attrs, $valueMatch)) {
                    return $full;
                }

                $optionValue = $valueMatch[1];

                // Add checked if this option matches the state
                if ((string) $optionValue === (string) $stateValue) {
                    return str_replace('/>', ' checked />', $full);
                }

                return $full;
            },
            $html,
        );
    }

    /**
     * Add checked attribute to the selected Radio input.
     *
     * Radio renders one <input type="radio"> per option, each with a distinct
     * value attribute and the same wire:model. We add `checked` to the one
     * whose value matches the current state.
     */
    protected function injectRadioState(string $html, array $data): string
    {
        if (! str_contains($html, 'fi-fo-radio')) {
            return $html;
        }

        return preg_replace_callback(
            '/<input(\s[^>]*?)type=["\']radio["\']([^>]*?)wire:model(?:\.[\w.]+)?="data\.([^"]+)"([^>]*?)\s*\/?>/s',
            function ($matches) use ($data) {
                $full = $matches[0];

                // Skip ToggleButtons radio — handled by injectToggleButtonsState
                $allAttrs = $matches[1] . $matches[2] . $matches[4];
                if (str_contains($allAttrs, 'fi-fo-toggle-buttons-input')) {
                    return $full;
                }

                $fieldPath = $matches[3];
                $stateValue = data_get($data, $fieldPath);

                if ($stateValue === null) {
                    return $full;
                }

                // Extract the option value from this radio input's value attribute
                if (! preg_match('/\bvalue="([^"]*)"/', $allAttrs, $valueMatch)) {
                    return $full;
                }

                $optionValue = $valueMatch[1];

                if ((string) $optionValue === (string) $stateValue) {
                    return str_replace('/>', ' checked />', $full);
                }

                return $full;
            },
            $html,
        );
    }

    /**
     * Apply background-color to color picker preview swatches.
     *
     * ColorPicker uses x-bind:style to set background-color via Alpine.js.
     * We inject the color value as an inline style on the preview div.
     */
    protected function injectColorPickerState(string $html, array $data): string
    {
        if (! str_contains($html, 'fi-fo-color-picker')) {
            return $html;
        }

        // Match each colorPickerFormComponent x-data, extract the field path,
        // then inject background-color on the nearby preview swatch div.
        return preg_replace_callback(
            '/colorPickerFormComponent\(\{[^}]*\$entangle\([\'"]data\.([^\'"]+)[\'"]\)[^}]*\}\).*?(<div[^>]*class=")(fi-fo-color-picker-preview[^"]*)(")[^>]*/s',
            function ($matches) use ($data) {
                $fieldPath = $matches[1];
                $value = data_get($data, $fieldPath);
                $full = $matches[0];

                if (blank($value)) {
                    return $full;
                }

                // Inject background-color style on the preview div
                $styled = str_replace(
                    $matches[2] . $matches[3] . $matches[4],
                    $matches[2] . $matches[3] . $matches[4] . ' style="background-color: ' . e($value) . ';"',
                    $full,
                );

                return $styled;
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

    /**
     * Make Builder component blocks fully visible in static HTML.
     *
     * Filament's Builder uses Alpine.js in two ways that need fixing:
     *
     * 1. Block content (`fi-fo-builder-item-content`) uses `x-show="! isCollapsed"`.
     *    Alpine initialises `isCollapsed = false` so the content shows in a live browser,
     *    but in a static `toHtml()` context the element would remain hidden. We replace the
     *    `x-show` attribute with an explicit `style="display:block"` so it is always visible.
     *
     * 2. The "Insert between blocks" container (`fi-fo-builder-add-between-items-ctn`) is
     *    hidden by Filament CSS (`visibility:hidden; height:0; opacity:0`) and only revealed
     *    on hover via CSS selectors. We inject an inline style to make it statically visible
     *    so the "insert" affordance appears between every pair of blocks in the screenshot.
     */
    protected function fixBuilder(string $html): string
    {
        if (! str_contains($html, 'fi-fo-builder')) {
            return $html;
        }

        // Replace x-show="! isCollapsed" on builder item content divs with a visible style.
        // The attribute appears as a standalone attribute on the <div> tag that wraps the
        // block's schema content.
        $html = preg_replace(
            '/(<div\s[^>]*)x-show="!\s*isCollapsed"([^>]*class="[^"]*fi-fo-builder-item-content[^"]*")/s',
            '$1style="display:block"$2',
            $html,
        );
        // Also handle the reverse attribute order (class before x-show).
        // Uses [^"]* around the class name to tolerate extra classes (e.g. when ->blockPreviews() is enabled).
        $html = preg_replace(
            '/(<div\s[^>]*class="[^"]*fi-fo-builder-item-content[^"]*"[^>]*)\s+x-show="!\s*isCollapsed"/s',
            '$1 style="display:block"',
            $html,
        );

        // Make "Insert between blocks" containers visible by injecting an inline style.
        // The class sits directly on a <li> element rendered as:
        //   <li class="fi-fo-builder-add-between-items-ctn">
        $html = preg_replace(
            '/(<li\s[^>]*class=")(fi-fo-builder-add-between-items-ctn)(")/s',
            '$1$2$3 style="visibility:visible;opacity:1;height:auto;pointer-events:auto;"',
            $html,
        );

        return $html;
    }

    /**
     * Make Wizard steps visible and set the active/completed steps in static HTML.
     *
     * Filament's Wizard uses Alpine.js to toggle step visibility. We resolve
     * this statically by adding fi-active/fi-completed to the correct steps.
     */
    protected function fixWizard(string $html): string
    {
        if (! str_contains($html, 'fi-sc-wizard')) {
            return $html;
        }

        // Remove x-cloak from wizard header and step elements
        $html = preg_replace(
            '/(<ol[^>]*?)\s*x-cloak\b([^>]*?class="fi-sc-wizard-header")/s',
            '$1$2',
            $html,
        );
        $html = preg_replace(
            '/(<(?:div|form)[^>]*?)\s*x-cloak\b([^>]*?class="fi-sc-wizard-step)/s',
            '$1$2',
            $html,
        );

        // Find each wizardSchemaComponent and resolve its active step
        if (preg_match_all('/wizardSchemaComponent\(\{[^}]*\}\)/', $html, $xDataMatches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach (array_reverse($xDataMatches) as $xDataMatch) {
                $xData = $xDataMatch[0][0];
                $offset = $xDataMatch[0][1];

                // Extract startStep (1-indexed, default 1)
                $startStep = 1;
                if (preg_match('/startStep:\s*(\d+)/', $xData, $m)) {
                    $startStep = (int) $m[1];
                }

                // Find the hidden input with step keys near this component
                $searchRegion = substr($html, $offset, 3000);
                $stepKeys = [];
                if (preg_match('/value="([^"]*)"[^>]*x-ref="stepsData"/', $searchRegion, $m)) {
                    $decoded = json_decode(html_entity_decode($m[1]), true);
                    if (is_array($decoded)) {
                        $stepKeys = $decoded;
                    }
                }

                if (empty($stepKeys)) {
                    continue;
                }

                $activeIndex = max(0, $startStep - 1);

                // Add fi-active and fi-completed to header step <li> elements
                // Header steps use x-bind:class with getStepIndex() comparisons
                $html = preg_replace_callback(
                    '/<li[^>]*class="fi-sc-wizard-header-step"[^>]*>/s',
                    function ($match) use ($activeIndex) {
                        static $counter = -1;
                        $counter++;

                        $tag = $match[0];
                        if ($counter === $activeIndex) {
                            $tag = str_replace('fi-sc-wizard-header-step"', 'fi-sc-wizard-header-step fi-active"', $tag);
                        } elseif ($counter < $activeIndex) {
                            $tag = str_replace('fi-sc-wizard-header-step"', 'fi-sc-wizard-header-step fi-completed"', $tag);
                        }

                        return $tag;
                    },
                    $html,
                );

                // Add fi-active to the matching step content pane
                $activeKey = $stepKeys[$activeIndex] ?? $stepKeys[0];
                $escapedKey = preg_quote($activeKey, '/');

                // Step panes use x-ref="step-{key}" and class="fi-sc-wizard-step"
                $html = preg_replace(
                    '/(x-ref="step-' . $escapedKey . '"[^>]*class="fi-sc-wizard-step)(")/s',
                    '$1 fi-active$2',
                    $html,
                );
            }
        }

        return $html;
    }

    /**
     * Make Tabs components visible and set the active tab in static HTML.
     *
     * Filament's Tabs use Alpine.js (x-bind:class, x-cloak) to toggle
     * the active tab. We resolve this statically by:
     * 1. Removing x-cloak from the tab nav bar
     * 2. Adding fi-active to the correct tab button and content pane
     */
    protected function fixTabs(string $html): string
    {
        if (! str_contains($html, 'fi-sc-tabs')) {
            return $html;
        }

        // Remove x-cloak from fi-tabs nav elements to make tab bars visible
        $html = preg_replace(
            '/(class="fi-tabs[^"]*"[^>]*)\s*x-cloak="x-cloak"/',
            '$1',
            $html,
        );

        // Find each tabsSchemaComponent and resolve its active tab
        if (preg_match_all('/tabsSchemaComponent\(\{[^}]*\}\)/', $html, $xDataMatches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            // Process in reverse to preserve offsets
            foreach (array_reverse($xDataMatches) as $xDataMatch) {
                $xData = $xDataMatch[0][0];
                $offset = $xDataMatch[0][1];

                // Extract activeTab (1-indexed, default 1)
                $activeTabIndex = 1;
                if (preg_match('/activeTab:\s*(\d+)/', $xData, $m)) {
                    $activeTabIndex = (int) $m[1];
                }

                // Find the hidden input with tab keys near this component
                $searchRegion = substr($html, $offset, 2000);
                $tabKeys = [];
                if (preg_match('/value="([^"]*)"[^>]*x-ref="tabsData"/', $searchRegion, $m)) {
                    $decoded = json_decode(html_entity_decode($m[1]), true);
                    if (is_array($decoded)) {
                        $tabKeys = $decoded;
                    }
                }

                if (empty($tabKeys)) {
                    continue;
                }

                $activeKey = $tabKeys[max(0, $activeTabIndex - 1)] ?? $tabKeys[0];
                $escapedKey = preg_quote($activeKey, '/');

                // Add fi-active to the matching tab button
                // class= comes before data-tab-key= in the HTML
                $html = preg_replace(
                    '/(<button[^>]*class="fi-tabs-item)("[^>]*data-tab-key="' . $escapedKey . '")/s',
                    '$1 fi-active$2',
                    $html,
                );

                // Add fi-active to the matching tab content pane
                $html = preg_replace(
                    '/(<div[^>]*x-bind:class="\{[^}]*\'' . $escapedKey . '\'[^}]*\}"[^>]*class="fi-sc-tabs-tab)(")/s',
                    '$1 fi-active$2',
                    $html,
                );
            }
        }

        return $html;
    }

    /**
     * Make the RichEditor toolbar visible in static HTML.
     *
     * Filament's RichEditor toolbar is Blade-rendered but hidden behind
     * x-cloak on the wrapper. We strip x-cloak so the toolbar and content
     * area are visible, and remove panels/upload elements that are irrelevant
     * in static context.
     */
    protected function fixRichEditor(string $html): string
    {
        if (! str_contains($html, 'fi-fo-rich-editor')) {
            return $html;
        }

        // Remove x-cloak from the fi-fo-rich-editor wrapper to reveal toolbar
        $html = preg_replace(
            '/(<div\s[^>]*class="[^"]*fi-fo-rich-editor[^"]*"[^>]*)\s*x-cloak="x-cloak"/',
            '$1',
            $html,
        );

        // Remove x-cloak from inner elements (content area, floating toolbar)
        // so they become visible without Alpine.js
        $html = preg_replace(
            '/(<(?:div|textarea)\s[^>]*(?:fi-fo-rich-editor-content|x-ref="editor")[^>]*)\s+x-cloak/',
            '$1',
            $html,
        );

        // Force toolbar-on-top layout. Filament uses column-reverse by default
        // (toolbar below content) and switches to row (side-by-side) at wider
        // widths via container queries. For static screenshots, toolbar-on-top
        // is the most recognizable layout.
        $html = preg_replace(
            '/(<div\s[^>]*class="[^"]*fi-fo-rich-editor-main[^"]*")/',
            '$1 style="flex-direction: column;"',
            $html,
        );

        // Remove the upload message and file validation divs (x-show + x-cloak, irrelevant in static)
        $html = preg_replace(
            '/<div\s[^>]*x-show="isUploadingFile"[^>]*>.*?<\/div>/s',
            '',
            $html,
        );
        $html = preg_replace(
            '/<div\s[^>]*x-show="!\s*isUploadingFile\s*&&\s*fileValidationMessage"[^>]*>.*?<\/div>/s',
            '',
            $html,
        );

        // Remove the panels section (custom blocks / merge tags, hidden by x-cloak)
        $html = preg_replace(
            '/<div\s[^>]*class="[^"]*fi-fo-rich-editor-panels[^"]*"[^>]*>.*?<\/div>\s*<\/div>\s*<\/div>/s',
            '',
            $html,
        );

        return $html;
    }

    /**
     * Inject a static toolbar for the MarkdownEditor.
     *
     * Unlike RichEditor, MarkdownEditor's toolbar is rendered entirely by
     * EasyMDE JavaScript. We parse the toolbar button config from the x-data
     * attribute and inject a static toolbar that mimics the EasyMDE UI.
     */
    protected function fixMarkdownEditor(string $html): string
    {
        if (! str_contains($html, 'fi-fo-markdown-editor')) {
            return $html;
        }

        // Find the markdown editor x-data to extract toolbar buttons and min height
        return preg_replace_callback(
            '/<div\s[^>]*class="fi-input-wrp fi-fo-markdown-editor"[^>]*>(.*?)<\/div>\s*<\/div>\s*<\/div>/s',
            function ($matches) {
                $inner = $matches[1];

                // Extract toolbar buttons from JSON
                $buttons = [];
                if (preg_match('/toolbarButtons:\s*JSON\.parse\(\'(\[.*?\])\'\)/', $inner, $btnMatch)) {
                    $decoded = json_decode(str_replace(['\u0022'], ['"'], $btnMatch[1]), true);
                    if (is_array($decoded)) {
                        $buttons = $decoded;
                    }
                }

                // Extract min height
                $minHeight = '11.25rem';
                if (preg_match('/minHeight:\s*\'([^\']+)\'/', $inner, $hMatch)) {
                    $minHeight = $hMatch[1];
                }

                // Build static toolbar + editor area
                return $this->buildMarkdownEditorHtml($buttons, $minHeight);
            },
            $html,
        );
    }

    /**
     * Build static HTML that mimics EasyMDE's rendered UI.
     */
    protected function buildMarkdownEditorHtml(array $buttonGroups, string $minHeight): string
    {
        $toolbarIcons = [
            'bold' => ['label' => 'Bold', 'svg' => '<path fill-rule="evenodd" d="M4 3a1 1 0 0 1 1-1h6a4.5 4.5 0 0 1 3.274 7.587A4.75 4.75 0 0 1 11.25 18H5a1 1 0 0 1-1-1V3Zm2.5 5.5v-4H11a2 2 0 1 1 0 4H6.5Zm0 2.5v4.5h4.75a2.25 2.25 0 0 0 0-4.5H6.5Z" clip-rule="evenodd"/>'],
            'italic' => ['label' => 'Italic', 'svg' => '<path fill-rule="evenodd" d="M8 2.75A.75.75 0 0 1 8.75 2h7.5a.75.75 0 0 1 0 1.5h-3.215l-4.483 13h2.698a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1 0-1.5h3.215l4.483-13H8.75A.75.75 0 0 1 8 2.75Z" clip-rule="evenodd"/>'],
            'strike' => ['label' => 'Strikethrough', 'svg' => '<path fill-rule="evenodd" d="M2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm4.72-4.72a2.5 2.5 0 0 1 3.56 0 .75.75 0 0 0 1.06-1.06 4 4 0 0 0-5.68 0 .75.75 0 0 0 1.06 1.06ZM10 15.5a2.49 2.49 0 0 0 1.78-.74.75.75 0 1 1 1.06 1.06A4 4 0 0 1 6 13.5a.75.75 0 0 1 1.5 0 2.5 2.5 0 0 0 2.5 2.5Z" clip-rule="evenodd"/>'],
            'link' => ['label' => 'Link', 'svg' => '<path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z"/><path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z"/>'],
            'heading' => ['label' => 'Heading', 'svg' => '<path fill-rule="evenodd" d="M3.75 2a.75.75 0 0 1 .75.75V9h7V2.75a.75.75 0 0 1 1.5 0v14.5a.75.75 0 0 1-1.5 0V10.5h-7v6.75a.75.75 0 0 1-1.5 0V2.75A.75.75 0 0 1 3.75 2Zm12 3a.75.75 0 0 1 .75.75v2.5h1.75a.75.75 0 0 1 0 1.5H16.5v2.5a.75.75 0 0 1-1.5 0v-2.5h-1.75a.75.75 0 0 1 0-1.5H15v-2.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>'],
            'blockquote' => ['label' => 'Blockquote', 'svg' => '<path fill-rule="evenodd" d="M4.25 2A2.25 2.25 0 0 0 2 4.25v2.5A2.25 2.25 0 0 0 4.25 9h.208a.25.25 0 0 1 .248.22l.46 3.677A.75.75 0 0 0 5.91 13.5h1.18a.75.75 0 0 0 .744-.648l.46-3.676A.25.25 0 0 1 8.542 9h.208A2.25 2.25 0 0 0 11 6.75v-2.5A2.25 2.25 0 0 0 8.75 2h-4.5Zm7 0A2.25 2.25 0 0 0 9 4.25v2.5A2.25 2.25 0 0 0 11.25 9h.208a.25.25 0 0 1 .248.22l.46 3.677a.75.75 0 0 0 .744.603h1.18a.75.75 0 0 0 .744-.648l.46-3.676A.25.25 0 0 1 15.542 9h.208A2.25 2.25 0 0 0 18 6.75v-2.5A2.25 2.25 0 0 0 15.75 2h-4.5Z" clip-rule="evenodd"/>'],
            'codeBlock' => ['label' => 'Code block', 'svg' => '<path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 0 1 0 1.06L2.56 10l3.72 3.72a.75.75 0 0 1-1.06 1.06L.97 10.53a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Zm7.44 0a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L17.44 10l-3.72-3.72a.75.75 0 0 1 0-1.06ZM11.377 2.011a.75.75 0 0 1 .612.867l-2.5 14.5a.75.75 0 0 1-1.478-.255l2.5-14.5a.75.75 0 0 1 .866-.612Z" clip-rule="evenodd"/>'],
            'bulletList' => ['label' => 'Bullet list', 'svg' => '<path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75Zm0 10.5a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1-.75-.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Z" clip-rule="evenodd"/>'],
            'orderedList' => ['label' => 'Numbered list', 'svg' => '<path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/>'],
            'table' => ['label' => 'Table', 'svg' => '<path fill-rule="evenodd" d="M.99 5.24A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 0v2.25h15v-2.25a.75.75 0 0 0-.75-.75H3.25a.75.75 0 0 0-.75.75Zm15 3.75H9.5v3h7.5v-3Zm0 4.5H9.5v3h7.25a.75.75 0 0 0 .75-.75v-2.25Zm-9 3v-3h-6v2.25c0 .414.336.75.75.75H8.5Zm-6-4.5h6v-3h-6v3Z" clip-rule="evenodd"/>'],
            'attachFiles' => ['label' => 'Attach files', 'svg' => '<path fill-rule="evenodd" d="M15.621 4.379a3.06 3.06 0 0 0-4.328 0l-6.414 6.414a4.59 4.59 0 0 0 6.494 6.494l6.06-6.06a.75.75 0 1 1 1.06 1.06l-6.06 6.06a6.09 6.09 0 0 1-8.614-8.614l6.414-6.414a4.56 4.56 0 0 1 6.45 6.45l-6.415 6.414a3.03 3.03 0 0 1-4.285-4.285l5.657-5.657a.75.75 0 0 1 1.061 1.06L11.018 12.87a1.53 1.53 0 0 0 2.164 2.164l6.414-6.414a3.06 3.06 0 0 0 0-4.242Z" clip-rule="evenodd"/>'],
            'undo' => ['label' => 'Undo', 'svg' => '<path fill-rule="evenodd" d="M7.793 2.232a.75.75 0 0 1-.025 1.06L3.622 7.25h10.003a5.375 5.375 0 0 1 0 10.75H10.75a.75.75 0 0 1 0-1.5h2.875a3.875 3.875 0 0 0 0-7.75H3.622l4.146 3.957a.75.75 0 0 1-1.036 1.085l-5.5-5.25a.75.75 0 0 1 0-1.085l5.5-5.25a.75.75 0 0 1 1.06.025Z" clip-rule="evenodd"/>'],
            'redo' => ['label' => 'Redo', 'svg' => '<path fill-rule="evenodd" d="M12.207 2.232a.75.75 0 0 1 1.06-.025l5.5 5.25a.75.75 0 0 1 0 1.085l-5.5 5.25a.75.75 0 0 1-1.036-1.085l4.146-3.957H6.375a3.875 3.875 0 0 0 0 7.75H9.25a.75.75 0 0 1 0 1.5H6.375a5.375 5.375 0 0 1 0-10.75h10.003L12.232 3.293a.75.75 0 0 1-.025-1.06Z" clip-rule="evenodd"/>'],
        ];

        $toolbarHtml = '';
        foreach ($buttonGroups as $group) {
            if (! is_array($group)) {
                continue;
            }

            $groupHtml = '';
            foreach ($group as $button) {
                $tool = $toolbarIcons[$button] ?? null;
                if (! $tool) {
                    continue;
                }

                $groupHtml .= '<button class="fi-fo-rich-editor-tool" tabindex="-1" type="button" aria-label="' . e($tool['label']) . '">'
                    . '<svg class="fi-icon fi-size-md" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">'
                    . $tool['svg']
                    . '</svg>'
                    . '</button>';
            }

            if ($groupHtml !== '') {
                $toolbarHtml .= '<div class="fi-fo-rich-editor-toolbar-group">' . $groupHtml . '</div>';
            }
        }

        $toolbar = $toolbarHtml !== ''
            ? '<div class="fi-fo-rich-editor-toolbar">' . $toolbarHtml . '</div>'
            : '';

        return '<div class="fi-input-wrp fi-fo-markdown-editor fi-fo-rich-editor">'
            . '<div class="fi-input-wrp-content-ctn">'
            . '<div class="fi-fo-rich-editor-main" style="flex-direction: column;">'
            . $toolbar
            . '<div class="fi-fo-rich-editor-content fi-prose" style="min-height: ' . e($minHeight) . ';"></div>'
            . '</div>'
            . '</div>'
            . '</div>';
    }

    /**
     * Replace the Alpine-driven CodeEditor with a static, styled code block.
     *
     * Filament's CodeEditor uses CodeMirror (via Alpine.js) to render a full
     * code editor UI. Since we render static HTML without JavaScript, the editor
     * div (`x-ref="editor" x-cloak`) is empty and invisible.
     *
     * We extract the field's state path and language from the `x-data` attribute
     * and replace the hidden div with a styled block that mimics the CodeMirror UI.
     */
    protected function fixCodeEditor(string $html, array $data): string
    {
        if (! str_contains($html, 'fi-fo-code-editor')) {
            return $html;
        }

        return preg_replace_callback(
            '/<div\s[^>]*x-data="codeEditorFormComponent\(\{([^"]*)\}?\)"[^>]*>.*?<\/div>\s*<\/div>/s',
            function ($matches) use ($data) {
                $xData = $matches[1];

                // Extract state path from $entangle('data.fieldName', ...)
                $fieldPath = null;
                if (preg_match('/\$entangle\(&#039;data\.([^&]+)&#039;/', $xData, $m)) {
                    $fieldPath = $m[1];
                }

                // Extract language (e.g. language: 'php')
                $language = null;
                if (preg_match("/language:\s*'([^']+)'/", $xData, $m)) {
                    $language = $m[1];
                }

                // Get code content from state
                $code = $fieldPath !== null ? data_get($data, $fieldPath) : null;

                try {
                    $darkMode = $this->isDarkMode();
                } catch (\Throwable) {
                    $darkMode = false;
                }

                return $this->buildCodeEditorHtml((string) ($code ?? ''), $language, $darkMode);
            },
            $html,
        );
    }

    /**
     * Build static HTML that mimics a CodeMirror code editor UI.
     *
     * Mirrors Filament's CodeEditor behaviour: dark mode uses the One Dark theme;
     * light mode uses CodeMirror's default light theme.  Basic syntax highlighting
     * is applied via PHP's built-in highlight_string() for PHP code, a custom
     * formatter for JSON, and regex-based keyword colouring for other languages.
     */
    protected function buildCodeEditorHtml(string $code, ?string $language, bool $darkMode = true): string
    {
        if ($darkMode) {
            $bg        = '#282c34';
            $fg        = '#abb2bf';
            $gutterBg  = '#21252b';
            $gutterFg  = '#495162';
            $langBg    = '#21252b';
            $langFg    = '#636d83';
            $border    = '#181a1f';
        } else {
            $bg        = '#ffffff';
            $fg        = '#24292e';
            $gutterBg  = '#f6f8fa';
            $gutterFg  = '#959da5';
            $langBg    = '#f0f0f0';
            $langFg    = '#6a737d';
            $border    = '#e1e4e8';
        }

        $languageClass = $language !== null ? ' language-' . e($language) : '';
        $languageLabel = $language !== null
            ? '<div class="fi-fo-code-editor-lang" style="'
                . 'font-family: var(--font-mono, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace);'
                . 'font-size: 0.6875rem;'
                . "color: {$langFg};"
                . "background-color: {$langBg};"
                . 'padding: 0.25rem 0.75rem;'
                . 'text-align: right;'
                . 'letter-spacing: 0.05em;'
                . "border-bottom: 1px solid {$border};"
                . '">' . e(strtoupper($language)) . '</div>'
            : '';

        $lines          = explode("\n", $code);
        $gutterHtml     = '';
        $highlightedLines = $this->syntaxHighlightLines($code, $language, $darkMode);

        foreach ($lines as $i => $line) {
            $gutterHtml .= '<div class="cm-gutterElement" style="padding: 0 0.5rem;">' . ($i + 1) . '</div>';
        }

        $codeLines = '';
        foreach ($highlightedLines as $line) {
            $codeLines .= '<div class="cm-line">' . ($line !== '' ? $line : ' ') . '</div>';
        }

        return '<div class="fi-fo-code-editor-static' . $languageClass . '" style="'
            . 'font-family: var(--font-mono, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace);'
            . 'font-size: 0.875rem;'
            . 'line-height: 1.6;'
            . "background-color: {$bg};"
            . "color: {$fg};"
            . 'border-radius: var(--radius-lg, 0.5rem);'
            . 'overflow: hidden;'
            . 'min-height: 12rem;'
            . 'display: flex;'
            . 'flex-direction: column;'
            . '">'
            . $languageLabel
            . '<div style="display: flex; flex: 1; overflow: auto;">'
            . '<div class="cm-gutters" style="'
            . "background-color: {$gutterBg};"
            . "color: {$gutterFg};"
            . "border-inline-end: 1px solid {$border};"
            . 'padding: 0.75rem 0;'
            . 'min-width: 2.5rem;'
            . 'text-align: right;'
            . 'user-select: none;'
            . 'flex-shrink: 0;'
            . '">'
            . $gutterHtml
            . '</div>'
            . '<pre class="cm-scroller" style="'
            . 'margin: 0;'
            . 'padding: 0.75rem 1rem;'
            . 'flex: 1;'
            . 'overflow: auto;'
            . 'white-space: pre;'
            . 'color: inherit;'
            . '">'
            . '<code class="cm-content' . $languageClass . '">'
            . $codeLines
            . '</code>'
            . '</pre>'
            . '</div>'
            . '</div>';
    }

    /**
     * Return an array of syntax-highlighted HTML strings, one per line.
     *
     * PHP uses the built-in highlight_string() with colour remapping.
     * JSON uses a custom recursive formatter.
     * All other languages receive basic regex-based keyword / string / comment
     * colouring that covers the most common patterns.
     *
     * @return string[]
     */
    protected function syntaxHighlightLines(string $code, ?string $language, bool $darkMode): array
    {
        if ($language === 'php') {
            return $this->highlightPhpLines($code, $darkMode);
        }

        if ($language === 'json') {
            return $this->highlightJsonLines($code, $darkMode);
        }

        return $this->highlightGenericLines($code, $language, $darkMode);
    }

    /** @return string[] */
    protected function highlightPhpLines(string $code, bool $darkMode): array
    {
        // Wrap bare code (no opening tag) so highlight_string() accepts it
        $wrapped  = str_starts_with(ltrim($code), '<?') ? $code : "<?php\n{$code}";
        $stripped = str_starts_with(ltrim($code), '<?') ? false : true;

        $highlighted = highlight_string($wrapped, true);

        // Strip the outer <code> wrapper that highlight_string() adds
        $highlighted = preg_replace('/^<code[^>]*>(.*)<\/code>$/s', '$1', trim($highlighted)) ?? $highlighted;

        // Remove the injected `<?php\n` line when we added it ourselves
        if ($stripped) {
            $highlighted = preg_replace('/^.*?<br\s*\/?>/s', '', $highlighted, 1) ?? $highlighted;
        }

        // Remap PHP's default highlight colours to theme colours
        if ($darkMode) {
            $map = [
                '#0000BB' => '#e06c75', // variables / tags   → red
                '#007700' => '#c678dd', // keywords            → purple
                '#DD0000' => '#98c379', // strings             → green
                '#FF8000' => '#5c6370', // comments            → gray
                '#000000' => '#abb2bf', // default             → text
            ];
        } else {
            $map = [
                '#0000BB' => '#e36209', // variables / tags   → orange
                '#007700' => '#d73a49', // keywords            → red
                '#DD0000' => '#032f62', // strings             → dark-blue
                '#FF8000' => '#6a737d', // comments            → gray
                '#000000' => '#24292e', // default             → text
            ];
        }

        $highlighted = str_ireplace(array_keys($map), array_values($map), $highlighted);

        // Convert <br /> line breaks to newlines and split
        $highlighted = preg_replace('/<br\s*\/?>/i', "\n", $highlighted);

        return explode("\n", $highlighted ?? '');
    }

    /** @return string[] */
    protected function highlightJsonLines(string $code, bool $darkMode): array
    {
        $data = json_decode($code);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->highlightGenericLines($code, 'json', $darkMode);
        }

        if ($darkMode) {
            $keyColor    = '#e06c75';
            $strColor    = '#98c379';
            $numColor    = '#d19a66';
            $boolColor   = '#56b6c2';
            $nullColor   = '#56b6c2';
        } else {
            $keyColor    = '#d73a49';
            $strColor    = '#032f62';
            $numColor    = '#005cc5';
            $boolColor   = '#005cc5';
            $nullColor   = '#005cc5';
        }

        $pretty = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($pretty === false) {
            return $this->highlightGenericLines($code, 'json', $darkMode);
        }

        $result = preg_replace_callback(
            '/("(?:[^"\\\\]|\\\\.)*")\s*:        # object key
            |("(?:[^"\\\\]|\\\\.)*")             # string value
            |(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?) # number
            |(true|false)                         # boolean
            |(null)                               # null
            /x',
            function ($m) use ($keyColor, $strColor, $numColor, $boolColor, $nullColor) {
                if ($m[1] !== '') {
                    return '<span style="color:' . $keyColor . '">' . e($m[1]) . '</span>:';
                }
                if ($m[2] !== '') {
                    return '<span style="color:' . $strColor . '">' . e($m[2]) . '</span>';
                }
                if ($m[3] !== '') {
                    return '<span style="color:' . $numColor . '">' . e($m[3]) . '</span>';
                }
                if ($m[4] !== '') {
                    return '<span style="color:' . $boolColor . '">' . e($m[4]) . '</span>';
                }

                return '<span style="color:' . $nullColor . '">' . e($m[5]) . '</span>';
            },
            $pretty,
        );

        return explode("\n", $result ?? e($pretty));
    }

    /**
     * Basic regex-based highlighting for CSS, JS, Python, SQL, YAML, and similar.
     *
     * @return string[]
     */
    protected function highlightGenericLines(string $code, ?string $language, bool $darkMode): array
    {
        if ($darkMode) {
            $keywordColor = '#c678dd';
            $stringColor  = '#98c379';
            $commentColor = '#5c6370';
            $numberColor  = '#d19a66';
            $funcColor    = '#61afef';
        } else {
            $keywordColor = '#d73a49';
            $stringColor  = '#032f62';
            $commentColor = '#6a737d';
            $numberColor  = '#005cc5';
            $funcColor    = '#6f42c1';
        }

        $keywords = match ($language) {
            'javascript', 'typescript' =>
                'break|case|catch|class|const|continue|debugger|default|delete|do|else|export|extends|'
                . 'finally|for|function|if|import|in|instanceof|let|new|of|return|static|super|switch|'
                . 'this|throw|try|typeof|var|void|while|with|yield|async|await|null|true|false|undefined',
            'python' =>
                'and|as|assert|async|await|break|class|continue|def|del|elif|else|except|False|finally|'
                . 'for|from|global|if|import|in|is|lambda|None|nonlocal|not|or|pass|raise|return|True|'
                . 'try|while|with|yield',
            'css' =>
                'important|px|em|rem|vh|vw|auto|none|block|flex|grid|inline|absolute|relative|fixed|sticky',
            'sql' =>
                'SELECT|FROM|WHERE|AND|OR|NOT|IN|EXISTS|BETWEEN|LIKE|IS|NULL|ORDER|BY|GROUP|HAVING|'
                . 'JOIN|LEFT|RIGHT|INNER|OUTER|CROSS|ON|AS|DISTINCT|LIMIT|OFFSET|INSERT|INTO|VALUES|'
                . 'UPDATE|SET|DELETE|CREATE|TABLE|INDEX|DROP|ALTER|ADD|COLUMN|PRIMARY|KEY|FOREIGN|'
                . 'REFERENCES|CONSTRAINT|DEFAULT|UNIQUE|CHECK|VIEW|PROCEDURE|FUNCTION|TRIGGER|IF',
            'go' =>
                'break|case|chan|const|continue|default|defer|else|fallthrough|for|func|go|goto|if|'
                . 'import|interface|map|package|range|return|select|struct|switch|type|var|'
                . 'true|false|nil|make|new|len|cap|append|copy|close|delete|panic|recover|print|println',
            'yaml' => 'true|false|null|yes|no|on|off',
            default => null,
        };

        $commentPrefix = match ($language) {
            'python', 'yaml', 'bash', 'shell' => '#',
            'sql'                              => '--',
            'css'                              => null,
            default                            => '//',
        };

        $lines  = explode("\n", $code);
        $result = [];

        foreach ($lines as $line) {
            // Split at the comment boundary so token highlighting is only applied
            // to the code portion; the comment is wrapped separately.
            $codePart    = $line;
            $commentPart = null;

            if ($commentPrefix !== null) {
                $prefixPos = strpos($line, $commentPrefix);
                // Ensure the prefix isn't inside a quoted string (simple heuristic)
                if ($prefixPos !== false) {
                    $before = substr($line, 0, $prefixPos);
                    if (substr_count($before, '"') % 2 === 0 && substr_count($before, "'") % 2 === 0) {
                        $codePart    = $before;
                        $commentPart = substr($line, $prefixPos);
                    }
                }
            }

            $escaped = e($codePart);

            // Strings (double and single quoted) — apply before keywords/numbers
            $escaped = preg_replace(
                '/(&quot;(?:[^&]|&(?!quot;))*&quot;|&#039;(?:[^&]|&(?!#039;))*&#039;)/',
                '<span style="color:' . $stringColor . '">$1</span>',
                $escaped,
            ) ?? $escaped;

            // Numbers
            $escaped = preg_replace(
                '/(?<![a-zA-Z_\-])(\b\d+(?:\.\d+)?\b)(?![a-zA-Z_])/',
                '<span style="color:' . $numberColor . '">$1</span>',
                $escaped,
            ) ?? $escaped;

            // Keywords
            if ($keywords !== null) {
                $escaped = preg_replace(
                    '/\b(' . $keywords . ')\b/',
                    '<span style="color:' . $keywordColor . '">$1</span>',
                    $escaped,
                ) ?? $escaped;
            }

            // Function calls: identifier followed by (
            $escaped = preg_replace(
                '/\b([a-zA-Z_][a-zA-Z0-9_]*)(?=\()/',
                '<span style="color:' . $funcColor . '">$1</span>',
                $escaped,
            ) ?? $escaped;

            if ($commentPart !== null) {
                $escaped .= '<span style="color:' . $commentColor . '">' . e($commentPart) . '</span>';
            }

            $result[] = $escaped !== '' ? $escaped : ($line !== '' ? e($line) : '');
        }

        return $result;
    }

    protected function ensureViewErrorBag(): void
    {
        if (! isset(app('view')->getShared()['errors'])) {
            app('view')->share('errors', new ViewErrorBag);
        }
    }
}
