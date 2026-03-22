<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\ToggleButtons;

it('renders toggle buttons with the selected option as active', function () {
    $html = FilamentShot::form([
        ToggleButtons::make('status')
            ->label('Status')
            ->options(['active' => 'Active', 'blocked' => 'Blocked', 'pending' => 'Pending']),
    ])->state(['status' => 'blocked'])->toHtml();

    expect($html)->toContain('fi-fo-toggle-buttons');

    // ToggleButtons renders hidden <input type="radio"> inputs alongside styled <label> buttons.
    // CSS uses `input:checked + label` to style the active option.
    // We inject `checked` on the matching radio input.
    preg_match_all('/<input[^>]*fi-fo-toggle-buttons-input[^>]*checked[^>]*\/?>/', $html, $checkedInputs);
    expect(count($checkedInputs[0]))->toBe(1);

    // The checked input should be the one with value="blocked"
    expect($checkedInputs[0][0])->toContain('value="blocked"');
});

it('renders toggle buttons with no active state when state is empty', function () {
    $html = FilamentShot::form([
        ToggleButtons::make('status')
            ->options(['a' => 'A', 'b' => 'B']),
    ])->state([])->toHtml();

    // No radio input should be checked when state is empty
    preg_match_all('/<input[^>]*fi-fo-toggle-buttons-input[^>]*checked[^>]*\/?>/', $html, $checkedInputs);
    expect(count($checkedInputs[0]))->toBe(0);
});

it('preserves correct option values on all toggle button radio inputs', function () {
    $html = FilamentShot::form([
        ToggleButtons::make('status')
            ->options(['active' => 'Active', 'blocked' => 'Blocked', 'pending' => 'Pending']),
    ])->state(['status' => 'blocked'])->toHtml();

    // Extract all radio inputs for this component
    preg_match_all('/<input[^>]*fi-fo-toggle-buttons-input[^>]*\/?>/', $html, $inputs);
    expect(count($inputs[0]))->toBe(3);

    // Each input should have its own distinct value (not overwritten to the state value)
    preg_match_all('/value="([^"]+)"/', implode(' ', $inputs[0]), $values);
    expect($values[1])->toContain('active');
    expect($values[1])->toContain('blocked');
    expect($values[1])->toContain('pending');
});
