<?php

namespace CCK\FilamentShot\Renderers;

use BackedEnum;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Support\Enums\IconSize;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\ComponentAttributeBag;

class NavigationRenderer extends BaseRenderer
{
    protected array $items = [];

    protected ?string $heading = null;

    public function items(array $items): static
    {
        $this->items = $items;

        return $this;
    }

    public function heading(string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    protected function renderContent(): string
    {
        $groups = $this->buildGroups();

        return view('filament-shot::components.navigation', [
            'groups' => $groups,
            'heading' => $this->heading,
        ])->render();
    }

    protected function buildGroups(): array
    {
        $groups = [];

        foreach ($this->items as $item) {
            if ($item instanceof NavigationGroup) {
                $groups[] = $this->extractGroupData($item);
            } elseif ($item instanceof NavigationItem) {
                // Ungrouped items go into a group with no label
                $groups[] = [
                    'label' => null,
                    'icon' => null,
                    'items' => [$this->extractItemData($item)],
                    'isCollapsible' => false,
                    'isCollapsed' => false,
                ];
            } elseif (is_array($item)) {
                $groups[] = $this->extractArrayGroupData($item);
            }
        }

        return $groups;
    }

    protected function extractGroupData(NavigationGroup $group): array
    {
        $items = [];
        foreach ($this->safeCall(fn () => $group->getItems(), []) as $navItem) {
            if ($navItem instanceof NavigationItem) {
                $items[] = $this->extractItemData($navItem);
            } elseif (is_array($navItem)) {
                $items[] = $this->extractArrayItemData($navItem);
            }
        }

        return [
            'label' => $this->safeCall(fn () => $group->getLabel(), null),
            'icon' => $this->resolveIcon($this->safeCall(fn () => $group->getIcon(), null)),
            'items' => $items,
            'isCollapsible' => $this->safeCall(fn () => $group->isCollapsible(), false),
            'isCollapsed' => $this->safeCall(fn () => $group->isCollapsed(), false),
        ];
    }

    protected function extractItemData(NavigationItem $item): array
    {
        $isActive = $this->safeCall(fn () => $item->isActive(), false);

        $icon = $isActive
            ? ($this->safeCall(fn () => $item->getActiveIcon(), null) ?? $this->safeCall(fn () => $item->getIcon(), null))
            : $this->safeCall(fn () => $item->getIcon(), null);

        $childItems = [];
        foreach ($this->safeCall(fn () => $item->getChildItems(), []) as $child) {
            if ($child instanceof NavigationItem) {
                $childItems[] = $this->extractItemData($child);
            }
        }

        return [
            'label' => $this->safeCall(fn () => $item->getLabel(), ''),
            'icon' => $this->resolveIcon($icon),
            'badge' => $this->safeCall(fn () => $item->getBadge(), null),
            'badgeColor' => $this->safeCall(fn () => $item->getBadgeColor(), null),
            'isActive' => $isActive,
            'childItems' => $childItems,
        ];
    }

    protected function extractArrayGroupData(array $group): array
    {
        $items = [];
        foreach ($group['items'] ?? [] as $item) {
            if ($item instanceof NavigationItem) {
                $items[] = $this->extractItemData($item);
            } elseif (is_array($item)) {
                $items[] = $this->extractArrayItemData($item);
            }
        }

        return [
            'label' => $group['label'] ?? null,
            'icon' => $group['icon'] ?? null,
            'items' => $items,
            'isCollapsible' => $group['isCollapsible'] ?? false,
            'isCollapsed' => $group['isCollapsed'] ?? false,
        ];
    }

    protected function extractArrayItemData(array $item): array
    {
        return [
            'label' => $item['label'] ?? '',
            'icon' => $item['icon'] ?? null,
            'badge' => $item['badge'] ?? null,
            'badgeColor' => $item['badgeColor'] ?? null,
            'isActive' => $item['isActive'] ?? false,
            'childItems' => [],
        ];
    }

    protected function resolveIcon(string | BackedEnum | Htmlable | null $icon): ?string
    {
        if ($icon === null) {
            return null;
        }

        $html = $this->safeCall(
            fn () => \Filament\Support\generate_icon_html(
                $icon,
                attributes: new ComponentAttributeBag,
                size: IconSize::Large,
            )?->toHtml(),
            null,
        );

        return $html;
    }
}
