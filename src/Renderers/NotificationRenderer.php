<?php

namespace CCK\FilamentShot\Renderers;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;

class NotificationRenderer extends BaseRenderer
{
    protected ?string $title = null;

    protected ?string $body = null;

    protected ?string $icon = null;

    protected ?string $status = null;

    /**
     * @var array<Action|ActionGroup>
     */
    protected array $actions = [];

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function body(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function success(): static
    {
        $this->status = 'success';

        return $this;
    }

    public function danger(): static
    {
        $this->status = 'danger';

        return $this;
    }

    public function warning(): static
    {
        $this->status = 'warning';

        return $this;
    }

    public function info(): static
    {
        $this->status = 'info';

        return $this;
    }

    /**
     * @param  array<Action|ActionGroup>  $actions
     */
    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    protected function renderContent(): string
    {
        $notification = Notification::make()
            ->title($this->title);

        if ($this->body !== null) {
            $notification->body($this->body);
        }

        if ($this->status !== null) {
            $notification->status($this->status);
        }

        if ($this->icon !== null) {
            $notification->icon($this->icon);
        }

        if (! empty($this->actions)) {
            $notification->actions($this->actions);
        }

        return view('filament-shot::components.notification', [
            'notificationHtml' => $notification->toEmbeddedHtml(),
        ])->render();
    }
}
