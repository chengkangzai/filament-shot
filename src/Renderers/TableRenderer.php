<?php

namespace CCK\FilamentShot\Renderers;

use CCK\FilamentShot\Support\ActionAdapter;
use CCK\FilamentShot\Support\ColumnAdapter;

class TableRenderer extends BaseRenderer
{
    protected array $columns = [];

    protected array $records = [];

    protected ?string $heading = null;

    protected bool $striped = false;

    protected array $actions = [];

    protected bool $labeledActions = false;

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

        return view('filament-shot::components.table', [
            'columns' => $columns,
            'records' => $this->records,
            'heading' => $this->heading,
            'striped' => $this->striped,
            'actions' => $actions,
        ])->render();
    }
}
