<?php

namespace CCK\FilamentShot\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Support\MessageBag;
use Livewire\Component;

/**
 * @property Schema $form
 */
class ShotFormComponent extends Component implements HasForms
{
    use InteractsWithForms;

    public array $data = [];

    /** @var array<object> */
    protected static array $pendingComponents = [];

    protected static array $pendingState = [];

    public static function prepareFor(array $components, array $state): void
    {
        static::$pendingComponents = $components;
        static::$pendingState = $state;
    }

    public function boot(): void
    {
        if (! $this->getId()) {
            $this->setId('shot-form-' . str()->random(8));
        }

        $this->setErrorBag(new MessageBag);
    }

    public function mount(): void
    {
        $this->form->fill(static::$pendingState);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(static::$pendingComponents)
            ->statePath('data');
    }

    public function render()
    {
        return view('filament-shot::livewire.form');
    }
}
