<?php

namespace CCK\FilamentShot\Renderers;

use CCK\FilamentShot\Concerns\HasFont;
use CCK\FilamentShot\Concerns\HasOutput;
use CCK\FilamentShot\Concerns\HasTheme;
use CCK\FilamentShot\Concerns\HasViewport;
use CCK\FilamentShot\Support\AssetResolver;

abstract class BaseRenderer
{
    use HasFont;
    use HasOutput;
    use HasTheme;
    use HasViewport;

    abstract protected function renderContent(): string;

    public function renderHtml(): string
    {
        $resolver = app(AssetResolver::class);

        return view('filament-shot::layouts.base', [
            'darkMode' => $this->isDarkMode(),
            'primaryColor' => $this->getPrimaryColor(),
            'colorVariables' => $this->getColorCssVariables(),
            'themeCss' => $resolver->getThemeCssContent(),
            'extraCss' => $resolver->getExtraCss(),
            'content' => $this->renderContent(),
            'contentWidth' => $this->getWidth() . 'px',
            'font' => $this->getFont(),
        ])->render();
    }
}
