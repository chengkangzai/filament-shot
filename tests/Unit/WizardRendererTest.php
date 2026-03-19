<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;

it('renders wizard with step indicator and first step active', function () {
    $html = FilamentShot::form([
        Wizard::make([
            Step::make('Account')
                ->schema([
                    TextInput::make('email')->label('Email'),
                ]),
            Step::make('Profile')
                ->schema([
                    TextInput::make('name')->label('Name'),
                ]),
            Step::make('Confirm')
                ->schema([
                    Toggle::make('terms')->label('Accept Terms'),
                ]),
        ]),
    ])
        ->state(['email' => 'test@example.com', 'name' => 'Jane', 'terms' => true])
        ->renderHtml();

    // Step header should be visible
    expect($html)->toMatch('/<ol[^>]*fi-sc-wizard-header/');

    // First step header should be active
    expect($html)->toMatch('/<li[^>]*fi-sc-wizard-header-step[^>]*fi-active/');

    // Step labels should be visible
    expect($html)->toContain('Account');
    expect($html)->toContain('Profile');
    expect($html)->toContain('Confirm');

    // First step content pane should have fi-active
    expect($html)->toContain('fi-sc-wizard-step fi-active');
});

it('renders wizard with second step active and first completed', function () {
    $html = FilamentShot::form([
        Wizard::make([
            Step::make('Step 1')
                ->schema([TextInput::make('field1')]),
            Step::make('Step 2')
                ->schema([TextInput::make('field2')]),
            Step::make('Step 3')
                ->schema([TextInput::make('field3')]),
        ])->startOnStep(2),
    ])
        ->state([])
        ->renderHtml();

    // First step should be completed
    expect($html)->toMatch('/<li[^>]*fi-sc-wizard-header-step[^>]*fi-completed/');
    // Second step should be active
    expect($html)->toMatch('/<li[^>]*fi-sc-wizard-header-step[^>]*fi-active/');
});

it('renders file upload with dropzone placeholder', function () {
    $html = FilamentShot::form([
        \Filament\Forms\Components\FileUpload::make('avatar')
            ->label('Avatar'),
    ])
        ->state([])
        ->renderHtml();

    expect($html)->toContain('fi-fo-file-upload');
    expect($html)->toContain('fi-fo-file-upload-input-ctn');
});
