<?php

namespace CCK\FilamentShot\Concerns;

trait HasHighlight
{
    /** @var array<int, array{key: string, color: string, style: string}> */
    protected array $highlights = [];

    public function highlight(string $fieldKey, string $color = '#ef4444', string $style = 'outline'): static
    {
        $this->highlights[] = ['key' => $fieldKey, 'color' => $color, 'style' => $style];

        return $this;
    }

    public function getHighlightCss(): string
    {
        if (empty($this->highlights)) {
            return '';
        }

        return implode("\n", array_map(function (array $h): string {
            $cssId = '#' . str_replace('.', '\\.', 'form.' . $h['key']);
            // Primary selector: [data-field-wrapper] inside the wire:partial container.
            // wire:partial="schema-component::form.FIELDKEY" is present on the ancestor
            // grid-column div for every field type, including RichEditor where the field
            // element does not carry an id attribute that :has() could match.
            // In CSS attribute selectors the attribute name colon needs escaping (\:)
            // but the attribute value string is a literal — no CSS escaping required.
            $partialValue = 'schema-component::form.' . $h['key'];
            $partialSelector = "[wire\\:partial=\"{$partialValue}\"] [data-field-wrapper]";
            // Fallback selector kept for fields (e.g. TextInput) that do carry an id.
            $wrapperSelector = "[data-field-wrapper]:has({$cssId})";
            $color = $h['color'];

            return match ($h['style']) {
                'box' => "{$partialSelector}, {$wrapperSelector} { outline: 3px solid {$color} !important; outline-offset: 4px; border-radius: 4px; background-color: {$color}22 !important; }",
                'underline' => "{$partialSelector} [x-ref=\"editor\"], {$cssId} { border-bottom: 3px solid {$color} !important; }",
                default => "{$partialSelector}, {$wrapperSelector} { outline: 3px solid {$color} !important; outline-offset: 4px; border-radius: 4px; }",
            };
        }, $this->highlights));
    }
}
