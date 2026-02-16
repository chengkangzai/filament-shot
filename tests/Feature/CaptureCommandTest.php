<?php

it('fails without config option', function () {
    $this->artisan('filament-shot:capture')
        ->expectsOutput('Please provide a valid config file path using --config')
        ->assertExitCode(1);
});

it('fails with non-existent config file', function () {
    $this->artisan('filament-shot:capture', ['--config' => '/tmp/nonexistent.php'])
        ->expectsOutput('Please provide a valid config file path using --config')
        ->assertExitCode(1);
});
