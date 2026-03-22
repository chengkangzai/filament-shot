<?php

namespace CCK\FilamentShot\Renderers;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

class HeaderActionsRenderer extends BaseRenderer
{
    protected ?string $pageTitle = null;

    /** @var array<string> */
    protected array $breadcrumbs = [];

    /**
     * @param  array<Action|ActionGroup>  $actions
     */
    public function __construct(
        protected array $actions = [],
    ) {}

    public function pageTitle(string $title): static
    {
        $this->pageTitle = $title;

        return $this;
    }

    /**
     * @param  array<string>  $breadcrumbs
     */
    public function breadcrumbs(array $breadcrumbs): static
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    protected function renderContent(): string
    {
        $actionsHtml = '';
        foreach ($this->actions as $action) {
            $actionsHtml .= $this->safeCall(
                fn () => $action->toHtml(),
                ''
            );
        }

        return view('filament-shot::components.header-actions', [
            'pageTitle' => $this->pageTitle,
            'breadcrumbs' => $this->breadcrumbs,
            'actionsHtml' => $actionsHtml,
        ])->render();
    }
}
