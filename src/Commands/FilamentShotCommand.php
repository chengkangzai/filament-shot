<?php

namespace CCK\FilamentShot\Commands;

use CCK\FilamentShot\FilamentShot;
use Illuminate\Console\Command;

class FilamentShotCommand extends Command
{
    public $signature = 'filament-shot:capture {--config= : Path to config file defining screenshots}';

    public $description = 'Capture Filament component screenshots from a config file';

    public function handle(): int
    {
        $configPath = $this->option('config');

        if (! $configPath || ! file_exists($configPath)) {
            $this->error('Please provide a valid config file path using --config');

            return self::FAILURE;
        }

        $definitions = require $configPath;

        if (! is_array($definitions)) {
            $this->error('Config file must return an array of screenshot definitions');

            return self::FAILURE;
        }

        $count = 0;

        foreach ($definitions as $definition) {
            $type = $definition['type'] ?? null;
            $output = $definition['output'] ?? null;

            if (! $type || ! $output) {
                $this->warn('Skipping definition missing type or output');

                continue;
            }

            $renderer = match ($type) {
                'form' => FilamentShot::form($definition['components'] ?? []),
                'table' => FilamentShot::table()
                    ->columns($definition['columns'] ?? [])
                    ->records($definition['records'] ?? []),
                'infolist' => FilamentShot::infolist($definition['entries'] ?? []),
                'stats' => FilamentShot::stats($definition['stats'] ?? []),
                default => null,
            };

            if (! $renderer) {
                $this->warn("Unknown type: {$type}");

                continue;
            }

            if (isset($definition['width'])) {
                $renderer->width($definition['width']);
            }

            if (isset($definition['height'])) {
                $renderer->height($definition['height']);
            }

            if ($definition['dark_mode'] ?? false) {
                $renderer->darkMode();
            }

            $renderer->save($output);
            $count++;

            $this->info("Captured: {$output}");
        }

        $this->comment("Done! Captured {$count} screenshot(s).");

        return self::SUCCESS;
    }
}
