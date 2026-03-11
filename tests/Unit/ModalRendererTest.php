<?php

use CCK\FilamentShot\FilamentShot;

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
