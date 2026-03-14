<?php

use CCK\FilamentShot\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * Assert that a PNG file matches a stored reference snapshot.
 *
 * On first run (no snapshot exists), the PNG is saved as the reference.
 * On subsequent runs, pixels are compared with the reference.
 * Set UPDATE_SNAPSHOTS=true to regenerate all stored references.
 *
 * @param  string  $snapshotName  Unique name (no extension)
 * @param  string  $actualPngPath  Path to the generated PNG
 * @param  float   $threshold     Max fraction of differing pixels (default 0.001 = 0.1%)
 */
function assertImageMatchesSnapshot(string $snapshotName, string $actualPngPath, float $threshold = 0.001): void
{
    $snapshotDir = __DIR__ . '/__snapshots__/images';

    if (! is_dir($snapshotDir)) {
        mkdir($snapshotDir, 0755, true);
    }

    $snapshotPath = "{$snapshotDir}/{$snapshotName}.png";
    $update = (bool) getenv('UPDATE_SNAPSHOTS');

    if (! file_exists($snapshotPath) || $update) {
        copy($actualPngPath, $snapshotPath);
        expect(file_exists($snapshotPath))->toBeTrue("Failed to save snapshot '{$snapshotName}'");

        return; // First run or forced update — snapshot saved
    }

    $expected = imagecreatefromstring(file_get_contents($snapshotPath));
    $actual = imagecreatefromstring(file_get_contents($actualPngPath));

    expect($expected)->not->toBeFalse('Could not load snapshot PNG: ' . $snapshotPath);
    expect($actual)->not->toBeFalse('Could not load actual PNG: ' . $actualPngPath);

    $expW = imagesx($expected);
    $expH = imagesy($expected);
    $actW = imagesx($actual);
    $actH = imagesy($actual);

    expect($actW)->toBe($expW, "Image width changed: expected {$expW}px, got {$actW}px");
    expect($actH)->toBe($expH, "Image height changed: expected {$expH}px, got {$actH}px");

    $diffPixels = 0;
    $totalPixels = $expW * $expH;

    for ($x = 0; $x < $expW; $x++) {
        for ($y = 0; $y < $expH; $y++) {
            if (imagecolorat($expected, $x, $y) !== imagecolorat($actual, $x, $y)) {
                $diffPixels++;
            }
        }
    }

    imagedestroy($expected);
    imagedestroy($actual);

    $diffFraction = $totalPixels > 0 ? $diffPixels / $totalPixels : 0;
    $diffPct = round($diffFraction * 100, 3);

    expect($diffFraction)->toBeLessThanOrEqual(
        $threshold,
        "Image snapshot '{$snapshotName}' mismatch: {$diffPixels}/{$totalPixels} pixels differ ({$diffPct}%). "
        . "Run with UPDATE_SNAPSHOTS=true to regenerate."
    );
}
