<?php

namespace CCK\FilamentShot\Renderers;

class TableRenderer extends BaseRenderer
{
    protected array $columns = [];

    protected array $records = [];

    protected ?string $heading = null;

    protected bool $striped = false;

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

    protected function renderContent(): string
    {
        $columnData = array_map(
            fn ($column) => $this->extractColumnData($column),
            $this->columns,
        );

        return view('filament-shot::components.table', [
            'columns' => $columnData,
            'records' => $this->records,
            'heading' => $this->heading,
            'striped' => $this->striped,
            'darkMode' => $this->isDarkMode(),
        ])->render();
    }

    protected function extractColumnData(mixed $column): array
    {
        if (is_array($column)) {
            return [
                'name' => $column['name'] ?? '',
                'label' => $column['label'] ?? str($column['name'] ?? '')->headline()->toString(),
                'badge' => $column['badge'] ?? false,
                'color' => $column['color'] ?? null,
            ];
        }

        return [
            'name' => $this->safeCall(fn () => $column->getName(), ''),
            'label' => $this->safeCall(fn () => $column->getLabel(), ''),
            'badge' => $this->safeCall(fn () => $column->isBadge(), false),
            'color' => null, // Color is resolved per-record in the template
            'getColor' => method_exists($column, 'getColor') ? fn ($state) => $this->safeCall(fn () => $column->getColor($state), null) : null,
        ];
    }

    protected function safeCall(callable $callback, mixed $default): mixed
    {
        try {
            return $callback() ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }
}
