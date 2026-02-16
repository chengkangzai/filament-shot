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
            ->timeout(config('shot.browsershot.timeout', 60))
            ->waitUntilNetworkIdle();

        if ($nodeBinary = config('shot.browsershot.node_binary')) {
            $browsershot->setNodeBinary($nodeBinary);
        }

        if ($npmBinary = config('shot.browsershot.npm_binary')) {
            $browsershot->setNpmBinary($npmBinary);
        }

        if ($chromePath = config('shot.browsershot.chrome_path')) {
            $browsershot->setChromePath($chromePath);
        }

        if (config('shot.browsershot.no_sandbox', false)) {
            $browsershot->noSandbox();
        }

        foreach (config('shot.browsershot.additional_options', []) as $key => $value) {
            $browsershot->setOption($key, $value);
        }

        return $browsershot;
    }
}
