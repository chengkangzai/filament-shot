<?php

namespace CCK\FilamentShot;

use CCK\FilamentShot\Commands\FilamentShotCommand;
use CCK\FilamentShot\Support\AssetResolver;
use CCK\FilamentShot\Support\BrowsershotFactory;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentShotServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-shot';

    public static string $viewNamespace = 'filament-shot';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands());

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AssetResolver::class);
        $this->app->singleton(BrowsershotFactory::class);
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentShotCommand::class,
        ];
    }
}
