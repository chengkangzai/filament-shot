<?php

namespace CCK\FilamentShot\Concerns;

trait HasViewport
{
    protected ?int $viewportWidth = null;

    protected ?int $viewportHeight = null;

    protected ?int $deviceScaleFactor = null;

    public function width(int $width): static
    {
        $this->viewportWidth = $width;

        return $this;
    }

    public function height(int $height): static
    {
        $this->viewportHeight = $height;

        return $this;
    }

    public function deviceScale(int $scale): static
    {
        $this->deviceScaleFactor = $scale;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->viewportWidth ?? config('filament-shot.viewport.width', 1024);
    }

    public function getHeight(): int
    {
        return $this->viewportHeight ?? config('filament-shot.viewport.height', 768);
    }

    public function getDeviceScale(): int
    {
        return $this->deviceScaleFactor ?? config('filament-shot.viewport.device_scale_factor', 2);
    }
}
