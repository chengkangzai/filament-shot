<?php

namespace CCK\FilamentShot\Renderers;

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
        $entryData = array_map(fn ($entry) => $this->extractEntryData($entry), $this->entries);

        return view('filament-shot::components.infolist', [
            'entries' => $entryData,
        ])->render();
    }

    protected function extractEntryData(object $entry): array
    {
        $name = $this->safeCall(fn () => $entry->getName(), '');

        return [
            'name' => $name,
            'label' => $this->safeCall(fn () => $entry->getLabel(), $name),
            'value' => $this->state[$name] ?? $this->safeCall(fn () => $entry->getConstantState(), ''),
        ];
    }
}
