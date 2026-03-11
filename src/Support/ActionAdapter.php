<?php

namespace CCK\FilamentShot\Support;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Support\Enums\IconSize;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\View\Components\IconButtonComponent;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\ComponentAttributeBag;

class ActionAdapter
{
    public function __construct(
        protected mixed $source,
    ) {}

    /**
     * Render the action as an icon button HTML string.
     */
    public function render(): string
    {
        $icon = $this->getIcon();
        $color = $this->getColor();
        $label = $this->getLabel();

        if (blank($icon) && blank($label)) {
            return '';
        }

        if (filled($icon)) {
            return $this->renderIconButton($icon, $color, $label);
        }

        return $this->renderLinkButton($color, $label);
    }

    /**
     * Render as an icon button (default for table row actions).
     */
    private function renderIconButton(string | BackedEnum | Htmlable $icon, string $color, ?string $label): string
    {
        $colorClasses = $this->resolveIconButtonClasses($color);

        $iconHtml = $this->safeCall(
            fn () => \Filament\Support\generate_icon_html(
                $icon,
                attributes: new ComponentAttributeBag,
                size: IconSize::Large,
            )?->toHtml(),
            '',
        );

        if (blank($iconHtml)) {
            return '';
        }

        $titleAttr = filled($label) ? ' title="' . e($label) . '"' : '';

        return '<button type="button" class="fi-icon-btn fi-size-md ' . $colorClasses . '"' . $titleAttr . '>'
            . $iconHtml
            . '</button>';
    }

    /**
     * Render as a text link button (fallback when no icon).
     */
    private function renderLinkButton(string $color, ?string $label): string
    {
        $colorClasses = $this->resolveIconButtonClasses($color);

        return '<button type="button" class="fi-link fi-size-sm ' . $colorClasses . '">'
            . '<span class="fi-link-label">' . e($label) . '</span>'
            . '</button>';
    }

    private function getIcon(): string | BackedEnum | Htmlable | null
    {
        if ($this->source instanceof Action) {
            // Try icon(), then tableIcon(), then groupedIcon() as fallbacks.
            // EditAction/DeleteAction set their icon via tableIcon(), not icon().
            return $this->safeCall(fn () => $this->source->getIcon(), null)
                ?? $this->safeCall(fn () => $this->source->getTableIcon(), null)
                ?? $this->safeCall(fn () => $this->source->getGroupedIcon(), null);
        }

        if (is_array($this->source)) {
            return $this->source['icon'] ?? null;
        }

        return null;
    }

    private function getColor(): string
    {
        if ($this->source instanceof Action) {
            return $this->safeCall(fn () => $this->source->getColor(), null) ?? 'primary';
        }

        if (is_array($this->source)) {
            return $this->source['color'] ?? 'primary';
        }

        return 'primary';
    }

    private function getLabel(): ?string
    {
        if ($this->source instanceof Action) {
            return $this->safeCall(fn () => $this->source->getLabel(), null);
        }

        if (is_array($this->source)) {
            return $this->source['label'] ?? null;
        }

        return null;
    }

    /**
     * Resolve CSS color classes for an icon button.
     */
    private function resolveIconButtonClasses(string $color): string
    {
        $classes = FilamentColor::getComponentClasses(IconButtonComponent::class, $color);

        return implode(' ', $classes);
    }

    private function safeCall(callable $callback, mixed $default): mixed
    {
        try {
            return $callback() ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }
}
