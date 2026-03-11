<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Actions\Action;

it('renders a success notification with title and body', function () {
    $html = FilamentShot::notification()
        ->title('Status Updated')
        ->body('The customer status has been changed to Blocked.')
        ->success()
        ->toHtml();

    expect($html)
        ->toContain('fi-no-notification')
        ->toContain('Status Updated')
        ->toContain('The customer status has been changed to Blocked.')
        ->toContain('fi-status-success');
});

it('renders a danger notification', function () {
    $html = FilamentShot::notification()
        ->title('Error Occurred')
        ->body('Something went wrong.')
        ->danger()
        ->toHtml();

    expect($html)
        ->toContain('fi-no-notification')
        ->toContain('Error Occurred')
        ->toContain('Something went wrong.')
        ->toContain('fi-status-danger');
});

it('renders a warning notification', function () {
    $html = FilamentShot::notification()
        ->title('Warning')
        ->body('Please review your settings.')
        ->warning()
        ->toHtml();

    expect($html)
        ->toContain('fi-no-notification')
        ->toContain('Warning')
        ->toContain('Please review your settings.')
        ->toContain('fi-status-warning');
});

it('renders an info notification (default)', function () {
    $html = FilamentShot::notification()
        ->title('Information')
        ->body('Here is some info.')
        ->info()
        ->toHtml();

    expect($html)
        ->toContain('fi-no-notification')
        ->toContain('Information')
        ->toContain('Here is some info.')
        ->toContain('fi-status-info');
});

it('renders a notification with custom icon', function () {
    $html = FilamentShot::notification()
        ->title('Custom Icon')
        ->icon('heroicon-o-bell')
        ->success()
        ->toHtml();

    expect($html)
        ->toContain('fi-no-notification')
        ->toContain('Custom Icon')
        ->toContain('fi-no-notification-icon');
});

it('renders a notification with action buttons', function () {
    $html = FilamentShot::notification()
        ->title('New Comment')
        ->body('Someone commented on your post.')
        ->success()
        ->actions([
            Action::make('view')->label('View'),
            Action::make('dismiss')->label('Dismiss'),
        ])
        ->toHtml();

    expect($html)
        ->toContain('fi-no-notification')
        ->toContain('New Comment')
        ->toContain('fi-no-notification-actions')
        ->toContain('View')
        ->toContain('Dismiss');
});

it('applies dark mode', function () {
    $html = FilamentShot::notification()
        ->title('Dark Notification')
        ->success()
        ->darkMode()
        ->toHtml();

    expect($html)
        ->toContain('class="dark"')
        ->toContain('fi-no-notification');
});
