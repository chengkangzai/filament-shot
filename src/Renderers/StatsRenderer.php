<?php

namespace CCK\FilamentShot\Renderers;

class StatsRenderer extends BaseRenderer
{
    public function __construct(
        protected array $stats = [],
    ) {}

    protected function renderContent(): string
    {
        $statData = array_map(fn ($stat) => $this->extractStatData($stat), $this->stats);

        return view('filament-shot::components.stats', [
            'stats' => $statData,
        ])->render();
    }

    protected function extractStatData(object $stat): array
    {
        return [
            'label' => $this->safeCall(fn () => $stat->getLabel(), ''),
            'value' => $this->safeCall(fn () => $stat->getValue(), ''),
            'description' => $this->safeCall(fn () => $stat->getDescription(), null),
            'descriptionIcon' => $this->safeCall(fn () => $stat->getDescriptionIcon(), null),
            'descriptionIconPosition' => $this->safeCall(fn () => $stat->getDescriptionIconPosition(), 'before'),
            'descriptionColor' => $this->safeCall(fn () => $stat->getDescriptionColor(), null),
            'icon' => $this->safeCall(fn () => $stat->getIcon(), null),
            'color' => $this->safeCall(fn () => $stat->getColor(), null),
            'chart' => $this->safeCall(fn () => $stat->getChart(), null),
        ];
    }
}
