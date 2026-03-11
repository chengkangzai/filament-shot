<?php

namespace CCK\FilamentShot\Livewire;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class ShotStatsComponent extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<\Filament\Widgets\StatsOverviewWidget\Stat> */
    protected static array $pendingStats = [];

    public static function prepareFor(array $stats): void
    {
        static::$pendingStats = $stats;
    }

    public function boot(): void
    {
        if (! $this->getId()) {
            $this->setId('shot-stats-' . str()->random(8));
        }

        $this->setErrorBag(new MessageBag);
    }

    public function mount(): void {}

    public function content(Schema $schema): Schema
    {
        $columns = $this->getStatColumns();

        return $schema
            ->components([
                Section::make()
                    ->schema(static::$pendingStats)
                    ->columns(['default' => $columns])
                    ->contained(false),
            ]);
    }

    protected function getStatColumns(): int
    {
        $count = count(static::$pendingStats);

        if ($count < 3) {
            return $count ?: 1;
        }

        if (($count % 3) !== 1) {
            return 3;
        }

        return 4;
    }

    public function render()
    {
        return view('filament-shot::livewire.stats');
    }
}
