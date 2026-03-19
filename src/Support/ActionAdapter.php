<?php

namespace CCK\FilamentShot\Support;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\IconSize;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\View\Components\ButtonComponent;
use Filament\Support\View\Components\IconButtonComponent;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\ComponentAttributeBag;

class ActionAdapter
{
    protected bool $labeled = false;

    public function __construct(
        protected mixed $source,
    ) {}

    public function labeled(bool $labeled = true): static
    {
        $this->labeled = $labeled;

        return $this;
    }

    /**
     * Render the action as an icon button HTML string.
     */
    public function render(): string
    {
        if ($this->source instanceof ActionGroup) {
            return $this->renderActionGroup();
        }

        $icon = $this->getIcon();
        $color = $this->getColor();
        $label = $this->getLabel();

        if (blank($icon) && blank($label)) {
            return '';
        }

        if (filled($icon) && $this->labeled && filled($label)) {
            return $this->renderLabeledButton($icon, $color, $label);
        }

        if (filled($icon)) {
            return $this->renderIconButton($icon, $color, $label);
        }

        return $this->renderLinkButton($color, $label);
    }

    /**
     * Render an ActionGroup as a dropdown trigger + panel.
     */
    private function renderActionGroup(): string
    {
        /** @var ActionGroup $group */
        $group = $this->source;

        $actions = $this->safeCall(fn () => $group->getActions(), []);
        if (empty($actions)) {
            return '';
        }

        // Build trigger button (three-dot ellipsis icon)
        $triggerColor = $this->safeCall(fn () => $group->getColor(), null) ?? 'gray';
        $triggerClasses = $this->resolveIconButtonClasses($triggerColor);
        $trigger = '<button type="button" class="fi-icon-btn fi-size-md ' . $triggerClasses . '">'
            . '<svg class="fi-icon fi-size-lg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">'
            . '<path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM11.5 15.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z"/>'
            . '</svg>'
            . '</button>';

        // Build dropdown list items
        $itemsHtml = '';
        foreach ($actions as $action) {
            if (! ($action instanceof Action)) {
                continue;
            }

            $visible = $this->safeCall(fn () => $action->isVisible(), true);
            if (! $visible) {
                continue;
            }

            $label = $this->safeCall(fn () => $action->getLabel(), null);
            if (blank($label)) {
                continue;
            }

            $icon = $this->safeCall(fn () => $action->getIcon(), null)
                ?? $this->safeCall(fn () => $action->getGroupedIcon(), null);
            $color = $this->safeCall(fn () => $action->getColor(), null) ?? 'gray';
            $colorClasses = $this->resolveDropdownItemClasses($color);

            $iconHtml = '';
            if (filled($icon)) {
                $iconHtml = $this->safeCall(
                    fn () => \Filament\Support\generate_icon_html(
                        $icon,
                        attributes: new ComponentAttributeBag,
                        size: IconSize::Medium,
                    )?->toHtml(),
                    '',
                );
            }

            $itemsHtml .= '<button type="button" class="fi-dropdown-list-item ' . $colorClasses . '">'
                . $iconHtml
                . '<span class="fi-dropdown-list-item-label">' . e($label) . '</span>'
                . '</button>';
        }

        if ($itemsHtml === '') {
            return '';
        }

        return '<div class="fi-dropdown" style="position: relative;">'
            . '<div class="fi-dropdown-trigger">' . $trigger . '</div>'
            . '<div class="fi-dropdown-panel" style="position: absolute; top: 100%; right: 0; z-index: 10; margin-top: 0.25rem;">'
            . '<div class="fi-dropdown-list">' . $itemsHtml . '</div>'
            . '</div>'
            . '</div>';
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
     * Render as a button with both icon and label text.
     */
    private function renderLabeledButton(string | BackedEnum | Htmlable $icon, string $color, string $label): string
    {
        $colorClasses = $this->resolveIconButtonClasses($color);

        $iconHtml = $this->safeCall(
            fn () => \Filament\Support\generate_icon_html(
                $icon,
                attributes: new ComponentAttributeBag,
                size: IconSize::Small,
            )?->toHtml(),
            '',
        );

        return '<button type="button" class="fi-link fi-size-sm ' . $colorClasses . '" style="display: inline-flex; align-items: center; gap: 0.25rem;">'
            . $iconHtml
            . '<span class="fi-link-label">' . e($label) . '</span>'
            . '</button>';
    }

    /**
     * Render as a standard button (used for toolbar/bulk actions).
     */
    public function renderButton(): string
    {
        $icon = $this->getIcon();
        $color = $this->getColor();
        $label = $this->getLabel();

        if (blank($label)) {
            return '';
        }

        $colorClasses = $this->resolveButtonClasses($color);

        $iconHtml = '';
        if (filled($icon)) {
            $iconHtml = $this->safeCall(
                fn () => \Filament\Support\generate_icon_html(
                    $icon,
                    attributes: new ComponentAttributeBag,
                    size: IconSize::Small,
                )?->toHtml(),
                '',
            );
        }

        return '<button type="button" class="fi-btn fi-size-sm ' . $colorClasses . '">'
            . $iconHtml
            . '<span class="fi-btn-label">' . e($label) . '</span>'
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

    private function resolveButtonClasses(string $color): string
    {
        $classes = FilamentColor::getComponentClasses(ButtonComponent::class, $color);

        return implode(' ', $classes);
    }

    private function resolveDropdownItemClasses(string $color): string
    {
        if ($color === 'gray' || $color === 'primary') {
            return '';
        }

        return 'fi-color fi-color-' . $color;
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
