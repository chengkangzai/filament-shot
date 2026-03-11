<?php

namespace CCK\FilamentShot\Renderers;

use CCK\FilamentShot\Livewire\ShotStatsComponent;
use Illuminate\Support\ViewErrorBag;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

class StatsRenderer extends BaseRenderer
{
    public function __construct(
        protected array $stats = [],
    ) {}

    protected function renderContent(): string
    {
        $this->ensureViewErrorBag();

        ShotStatsComponent::prepareFor($this->stats);

        $component = new ShotStatsComponent;
        $component->boot();
        $component->mount();

        $extendBlade = app(ExtendBlade::class);
        $extendBlade->startLivewireRendering($component);
        app('view')->share('__livewire', $component);

        try {
            $html = $component->getSchema('content')->toHtml();
        } finally {
            $extendBlade->endLivewireRendering();
            app('view')->share('__livewire', null);
        }

        return $this->injectSvgCharts($html);
    }

    /**
     * Replace empty Alpine.js chart containers with static SVG sparklines.
     *
     * Filament's native chart rendering uses x-data/x-load with Chart.js,
     * which doesn't execute in static HTML. We replace these containers
     * with SVG polyline charts that render without JavaScript.
     */
    protected function injectSvgCharts(string $html): string
    {
        foreach ($this->stats as $stat) {
            $chart = $this->safeCall(fn () => $stat->getChart(), null);
            if (empty($chart)) {
                continue;
            }

            $chartColor = $this->safeCall(fn () => $stat->getChartColor(), null) ?? 'gray';
            $html = $this->replaceSingleChart($html, $chart, $chartColor);
        }

        return $html;
    }

    protected function replaceSingleChart(string $html, array $chart, string $chartColor): string
    {
        // Find the x-data="{ statsOverviewStatChart() {} }" container and replace it
        $pattern = '/<div\s+x-data="\{\s*statsOverviewStatChart\(\)\s*\{\}\s*\}">\s*<div[^>]*>.*?<\/div>\s*<\/div>/s';

        return preg_replace($pattern, $this->buildSvgChart($chart, $chartColor), $html, 1);
    }

    protected function buildSvgChart(array $chart, string $chartColor): string
    {
        $max = max($chart);
        $min = min($chart);
        $range = $max - $min ?: 1;
        $width = (count($chart) - 1) * 10;

        $points = collect($chart)->map(function ($v, $i) use ($range, $min) {
            $x = $i * 10;
            $y = 40 - (($v - $min) / $range * 36);

            return "$x,$y";
        })->implode(' ');

        $fillPoints = "0,40 $points {$width},40";

        return '<div class="fi-wi-stats-overview-stat-chart" style="position: absolute; inset-inline: 0; bottom: 0; overflow: hidden; border-radius: 0 0 0.75rem 0.75rem;">'
            . '<svg viewBox="0 0 ' . $width . ' 40" style="width: 100%; height: 1.5rem; display: block;" preserveAspectRatio="none">'
            . '<polygon fill="var(--' . e($chartColor) . '-400)" fill-opacity="0.1" points="' . $fillPoints . '" />'
            . '<polyline fill="none" stroke="var(--' . e($chartColor) . '-400)" stroke-width="2" points="' . $points . '" />'
            . '</svg>'
            . '</div>';
    }

    protected function ensureViewErrorBag(): void
    {
        if (! isset(app('view')->getShared()['errors'])) {
            app('view')->share('errors', new ViewErrorBag);
        }
    }
}
