<?php

namespace CCK\FilamentShot\Renderers;

use Filament\Infolists\Components\Entry;
use Filament\Support\Components\Contracts\HasEmbeddedView;

class InfolistRenderer extends BaseRenderer
{
    protected array $state = [];

    public function __construct(
        protected array $entries = [],
    ) {}

    public function state(array $state): static
    {
        $this->state = $state;

        return $this;
    }

    protected function renderContent(): string
    {
        $renderedEntries = [];

        foreach ($this->entries as $entry) {
            $renderedEntries[] = $this->renderEntry($entry);
        }

        return '<div class="fi-in" style="display: flex; flex-direction: column; gap: 1.5rem;">'
            . implode("\n", $renderedEntries)
            . '</div>';
    }

    protected function renderEntry(object $entry): string
    {
        if ($entry instanceof Entry && $entry instanceof HasEmbeddedView) {
            try {
                $name = $this->safeCall(fn () => $entry->getName(), '');
                $value = $this->state[$name] ?? $this->safeCall(fn () => $entry->getConstantState(), '');

                $clone = clone $entry;
                $clone->constantState($value);

                return $clone->toEmbeddedHtml();
            } catch (\Throwable) {
                // Fall through to manual rendering
            }
        }

        return $this->renderEntryManually($entry);
    }

    protected function renderEntryManually(object $entry): string
    {
        $name = $this->safeCall(fn () => $entry->getName(), '');
        $label = $this->safeCall(fn () => $entry->getLabel(), $name);
        $value = $this->state[$name] ?? $this->safeCall(fn () => $entry->getConstantState(), '');

        return '<div class="fi-in-entry">'
            . '<div class="fi-in-entry-label-col">'
            . '<div class="fi-in-entry-label-ctn">'
            . '<dt class="fi-in-entry-label">' . e($label) . '</dt>'
            . '</div></div>'
            . '<div class="fi-in-entry-content-col">'
            . '<dd class="fi-in-entry-content-ctn">'
            . '<div class="fi-in-entry-content">'
            . '<div class="fi-in-text"><div class="fi-in-text-item">' . e($value) . '</div></div>'
            . '</div></dd></div></div>';
    }
}
