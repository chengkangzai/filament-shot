<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\TextInput;

it('renders body components inside the modal', function () {
    $html = FilamentShot::modal([
        TextInput::make('reason')->label('Reason for deletion'),
    ])
        ->heading('Confirm Deletion')
        ->color('danger')
        ->toHtml();

    expect($html)
        ->toContain('fi-modal')
        ->toContain('class="fi-modal-content"')
        ->toContain('Reason for deletion')
        ->toContain('wire:model')
        ->toContain('Confirm Deletion');
});

it('injects state into modal body components', function () {
    $html = FilamentShot::modal([
        TextInput::make('reason'),
    ])
        ->heading('Confirm')
        ->state(['reason' => 'Duplicate account'])
        ->toHtml();

    expect($html)->toContain('value="Duplicate account"');
});

it('renders an empty body when no components are given', function () {
    $html = FilamentShot::modal()
        ->heading('Confirm Action')
        ->toHtml();

    expect($html)
        ->toContain('fi-modal')
        ->not->toContain('class="fi-modal-content"');
});

it('renders a standalone confirmation modal', function () {
    $html = FilamentShot::modal()
        ->heading('Block Customer')
        ->description('Are you sure you want to block this customer?')
        ->submitLabel('Yes, block')
        ->cancelLabel('Cancel')
        ->toHtml();

    expect($html)
        ->toContain('fi-modal')
        ->toContain('fi-modal-window')
        ->toContain('fi-modal-header')
        ->toContain('fi-modal-heading')
        ->toContain('Block Customer')
        ->toContain('Are you sure you want to block this customer?')
        ->toContain('fi-modal-footer')
        ->toContain('Yes, block')
        ->toContain('Cancel');
});

it('renders a modal without description', function () {
    $html = FilamentShot::modal()
        ->heading('Confirm Action')
        ->toHtml();

    expect($html)
        ->toContain('fi-modal')
        ->toContain('Confirm Action')
        ->not->toContain('<p class="fi-modal-description">');
});

it('renders a modal with icon', function () {
    $html = FilamentShot::modal()
        ->heading('Delete Item')
        ->icon('heroicon-o-trash')
        ->toHtml();

    expect($html)
        ->toContain('fi-modal')
        ->toContain('fi-modal-icon-ctn')
        ->toContain('Delete Item');
});

it('renders a modal with icon color', function () {
    $html = FilamentShot::modal()
        ->heading('Warning')
        ->icon('heroicon-o-exclamation-triangle')
        ->iconColor('danger')
        ->toHtml();

    expect($html)
        ->toContain('fi-modal')
        ->toContain('fi-modal-icon-ctn')
        ->toContain('fi-color-danger');
});

it('renders a modal with custom button color', function () {
    $html = FilamentShot::modal()
        ->heading('Delete')
        ->color('danger')
        ->submitLabel('Delete')
        ->toHtml();

    expect($html)
        ->toContain('fi-modal')
        ->toContain('fi-color-danger')
        ->toContain('Delete');
});

it('renders a modal with default labels', function () {
    $html = FilamentShot::modal()
        ->heading('Confirm')
        ->toHtml();

    expect($html)
        ->toContain('Confirm') // submitLabel default
        ->toContain('Cancel'); // cancelLabel default
});

it('renders a modal in dark mode', function () {
    $html = FilamentShot::modal()
        ->heading('Dark Modal')
        ->darkMode()
        ->toHtml();

    expect($html)
        ->toContain('class="dark"')
        ->toContain('fi-modal');
});
