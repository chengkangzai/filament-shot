<?php

namespace CCK\FilamentShot\Support;

use Spatie\Browsershot\Browsershot;

class BrowsershotFactory
{
    public function create(string $html, int $width, int $height, int $deviceScale, bool $fitContent = false): Browsershot
    {
        // Inline file:// script sources before writing to temp file.
        // Chromium blocks cross-directory file:// access, so <script src="file:///..."> tags
        // from vendor paths fail silently when the temp HTML is in /tmp/. Inlining guarantees
        // Alpine.js and Filament core bundles actually execute during screenshot rendering.
        $html = $this->inlineFileScripts($html);

        // Write to a temp file and load via file:// URL instead of passing HTML inline.
        // Inline HTML is validated by Browsershot for unsafe patterns (file:/, view-source, etc.)
        // which causes false positives when JS bundles contain these strings as code literals.
        $tempFile = tempnam(sys_get_temp_dir(), 'filament-shot-') . '.html';
        file_put_contents($tempFile, $html);
        register_shutdown_function(static fn () => @unlink($tempFile));

        $browsershot = Browsershot::htmlFromFilePath($tempFile)
            ->windowSize($width, $height)
            ->deviceScaleFactor($deviceScale)
            ->timeout(config('filament-shot.browsershot.timeout', 60))
            ->waitUntilNetworkIdle()
            ->delay(config('filament-shot.browsershot.delay', 500));

        if ($fitContent) {
            $browsershot->select('body');
        }

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

    /**
     * Replace <script src="file:///abs/path.js"> tags with inline <script> content.
     *
     * Chromium blocks cross-directory file:// access when loading from a temp file://
     * page in /tmp/. Without inlining, Alpine.js and Filament core bundles silently
     * fail to load, leaving the page without JS initialization.
     */
    protected function inlineFileScripts(string $html): string
    {
        return preg_replace_callback(
            '/<script\s+src="file:\/\/([^"]+)"><\/script>/',
            function (array $match): string {
                $path = $match[1];
                if (! file_exists($path)) {
                    return $match[0];
                }

                return '<script>' . file_get_contents($path) . '</script>';
            },
            $html,
        );
    }
}
