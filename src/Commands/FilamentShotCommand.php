<?php

namespace CCK\FilamentShot\Commands;

use Illuminate\Console\Command;

class FilamentShotCommand extends Command
{
    public $signature = 'filament-shot';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
