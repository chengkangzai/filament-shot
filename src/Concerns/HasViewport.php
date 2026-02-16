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
        return $this->viewportWidth ?? config('shot.viewport.width', 1024);
    }

    public function getHeight(): int
    {
        return $this->viewportHeight ?? config('shot.viewport.height', 768);
    }

    public function getDeviceScale(): int
    {
        return $this->deviceScaleFactor ?? config('shot.viewport.device_scale_factor', 2);
    }
}
