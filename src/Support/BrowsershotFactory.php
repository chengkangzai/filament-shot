<?php

namespace CCK\FilamentShot\Support;

use Spatie\Browsershot\Browsershot;

class BrowsershotFactory
{
    public function create(string $html, int $width, int $height, int $deviceScale): Browsershot
    {
        $browsershot = Browsershot::html($html)
            ->windowSize($width, $height)
            ->deviceScaleFactor($deviceScale)
            ->timeout(config('filament-shot.browsershot.timeout', 60))
            ->waitUntilNetworkIdle();

        if ($nodeBinary = config('filament-shot.browsershot.node_binary')) {
            $browsershot->setNodeBinary($nodeBinary);
        }

        if ($npmBinary = config('filament-shot.browsershot.npm_binary')) {
            $browsershot->setNpmBinary($npmBinary);
        }

        if ($chromePath = config('filament-shot.browsershot.chrome_path')) {
            $browsershot->setChromePath($chromePath);
        }

        if (config('filament-shot.browsershot.no_sandbox', false)) {
            $browsershot->noSandbox();
        }

        foreach (config('filament-shot.browsershot.additional_options', []) as $key => $value) {
            $browsershot->setOption($key, $value);
        }

        return $browsershot;
    }
}
