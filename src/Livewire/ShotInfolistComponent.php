<?php

namespace CCK\FilamentShot\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Schemas\Schema;
use Illuminate\Support\MessageBag;
use Livewire\Component;

class ShotInfolistComponent extends Component implements HasForms, HasInfolists
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    public array $data = [];

    /** @var array<object> */
    protected static array $pendingEntries = [];

    protected static array $pendingState = [];

    public static function prepareFor(array $entries, array $state): void
    {
        static::$pendingEntries = $entries;
        static::$pendingState = $state;
    }

    public function boot(): void
    {
        if (! $this->getId()) {
            $this->setId('shot-infolist-' . str()->random(8));
        }

        $this->setErrorBag(new MessageBag);
    }

    public function mount(): void
    {
        $this->data = static::$pendingState;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components(static::$pendingEntries)
            ->constantState(static::$pendingState);
    }

    public function render()
    {
        return view('filament-shot::livewire.infolist');
    }
}
