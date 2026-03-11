<?php

namespace CCK\FilamentShot\Renderers;

use CCK\FilamentShot\Support\ActionAdapter;
use CCK\FilamentShot\Support\ColumnAdapter;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\View\Components\LinkComponent;

class TableRenderer extends BaseRenderer
{
    protected array $columns = [];

    protected array $records = [];

    protected ?string $heading = null;

    protected bool $striped = false;

    protected array $actions = [];

    protected bool $labeledActions = false;

    protected array $bulkActions = [];

    protected array $selectedRows = [];

    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function records(array $records): static
    {
        $this->records = $records;

        return $this;
    }

    public function heading(string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function striped(bool $striped = true): static
    {
        $this->striped = $striped;

        return $this;
    }

    public function recordActions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function labeledActions(bool $labeled = true): static
    {
        $this->labeledActions = $labeled;

        return $this;
    }

    public function bulkActions(array $bulkActions): static
    {
        $this->bulkActions = $bulkActions;

        return $this;
    }

    public function selectedRows(array $selectedRows): static
    {
        $this->selectedRows = $selectedRows;

        return $this;
    }

    protected function renderContent(): string
    {
        $columns = array_map(
            fn ($column) => new ColumnAdapter($column),
            $this->columns,
        );

        $actions = array_map(
            fn ($action) => (new ActionAdapter($action))->labeled($this->labeledActions),
            $this->actions,
        );

        $bulkActions = array_map(
            fn ($action) => (new ActionAdapter($action))->labeled(true),
            $this->bulkActions,
        );

        $linkPrimaryClasses = implode(' ', FilamentColor::getComponentClasses(LinkComponent::class, 'primary'));
        $linkDangerClasses = implode(' ', FilamentColor::getComponentClasses(LinkComponent::class, 'danger'));

        return view('filament-shot::components.table', [
            'columns' => $columns,
            'records' => $this->records,
            'heading' => $this->heading,
            'striped' => $this->striped,
            'actions' => $actions,
            'bulkActions' => $bulkActions,
            'selectedRows' => $this->selectedRows,
            'linkPrimaryClasses' => $linkPrimaryClasses,
            'linkDangerClasses' => $linkDangerClasses,
        ])->render();
    }
}
