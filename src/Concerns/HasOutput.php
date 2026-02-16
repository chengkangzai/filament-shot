<?php

namespace CCK\FilamentShot\Concerns;

use CCK\FilamentShot\Support\BrowsershotFactory;
use Illuminate\Http\Response;
use Spatie\Browsershot\Browsershot;

trait HasOutput
{
    abstract public function renderHtml(): string;

    public function save(string $path): static
    {
        $this->getBrowsershot()->save($path);

        return $this;
    }

    public function toBase64(): string
    {
        return $this->getBrowsershot()->base64Screenshot();
    }

    public function toHtml(): string
    {
        return $this->renderHtml();
    }

    public function toResponse(): Response
    {
        $screenshot = $this->getBrowsershot()->screenshot();

        return new Response($screenshot, 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => strlen($screenshot),
        ]);
    }

    protected function getBrowsershot(): Browsershot
    {
        $html = $this->renderHtml();

        return app(BrowsershotFactory::class)->create(
            $html,
            $this->getWidth(),
            $this->getHeight(),
            $this->getDeviceScale(),
        );
    }
}
