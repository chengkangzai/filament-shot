<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

it('renders a text input field', function () {
    $html = FilamentShot::form([
        TextInput::make('name')->label('Full Name'),
    ])->toHtml();

    expect($html)
        ->toContain('Full Name')
        ->toContain('fi-input')
        ->toContain('name="name"');
});

it('renders a text input with placeholder', function () {
    $html = FilamentShot::form([
        TextInput::make('email')
            ->label('Email')
            ->placeholder('Enter your email'),
    ])->toHtml();

    expect($html)
        ->toContain('Email')
        ->toContain('Enter your email');
});

it('renders a select field with options', function () {
    $html = FilamentShot::form([
        Select::make('country')
            ->label('Country')
            ->options([
                'us' => 'United States',
                'uk' => 'United Kingdom',
            ]),
    ])->toHtml();

    expect($html)
        ->toContain('Country')
        ->toContain('United States')
        ->toContain('United Kingdom')
        ->toContain('fi-select-input');
});

it('renders a textarea field', function () {
    $html = FilamentShot::form([
        Textarea::make('bio')->label('Biography'),
    ])->toHtml();

    expect($html)
        ->toContain('Biography')
        ->toContain('fi-textarea-input')
        ->toContain('name="bio"');
});

it('renders a toggle field', function () {
    $html = FilamentShot::form([
        Toggle::make('active')->label('Active'),
    ])->toHtml();

    expect($html)
        ->toContain('Active')
        ->toContain('fi-toggle-input');
});

it('renders a checkbox field', function () {
    $html = FilamentShot::form([
        Checkbox::make('agree')->label('I agree'),
    ])->toHtml();

    expect($html)
        ->toContain('I agree')
        ->toContain('fi-checkbox-input');
});

it('renders form with state values', function () {
    $html = FilamentShot::form([
        TextInput::make('name')->label('Name'),
    ])->state(['name' => 'John Doe'])->toHtml();

    expect($html)
        ->toContain('John Doe');
});

it('renders complete HTML document', function () {
    $html = FilamentShot::form([
        TextInput::make('test'),
    ])->toHtml();

    expect($html)
        ->toContain('<!DOCTYPE html')
        ->toContain('<html')
        ->toContain('</html>');
});

it('applies dark mode class', function () {
    $html = FilamentShot::form([
        TextInput::make('test'),
    ])->darkMode()->toHtml();

    expect($html)
        ->toContain('class="dark"');
});
