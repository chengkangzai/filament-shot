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
            $wrapperSelector = "[data-field-wrapper]:has({$cssId})";
            $color = $h['color'];

            return match ($h['style']) {
                'box' => "{$wrapperSelector} { outline: 3px solid {$color} !important; outline-offset: 4px; border-radius: 4px; background-color: {$color}22 !important; }",
                'underline' => "{$cssId} { border-bottom: 3px solid {$color} !important; }",
                default => "{$wrapperSelector} { outline: 3px solid {$color} !important; outline-offset: 4px; border-radius: 4px; }",
            };
        }, $this->highlights));
    }
}
