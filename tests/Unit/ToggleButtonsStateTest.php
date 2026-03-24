<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\Radio;
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

it('handles multiple toggle buttons groups in one form', function () {
    $html = FilamentShot::form([
        ToggleButtons::make('status')
            ->options(['active' => 'Active', 'blocked' => 'Blocked']),
        ToggleButtons::make('priority')
            ->options(['low' => 'Low', 'high' => 'High']),
    ])->state(['status' => 'active', 'priority' => 'high'])->toHtml();

    preg_match_all('/<input[^>]*fi-fo-toggle-buttons-input[^>]*checked[^>]*\/?>/', $html, $checkedInputs);
    expect(count($checkedInputs[0]))->toBe(2);

    // The correct options should be checked in each group
    $checkedHtml = implode(' ', $checkedInputs[0]);
    expect($checkedHtml)->toContain('value="active"');
    expect($checkedHtml)->toContain('value="high"');
});

it('handles null state value gracefully for toggle buttons', function () {
    $html = FilamentShot::form([
        ToggleButtons::make('status')
            ->options(['active' => 'Active', 'blocked' => 'Blocked']),
    ])->state(['status' => null])->toHtml();

    // No radio input should be checked when state is null
    preg_match_all('/<input[^>]*fi-fo-toggle-buttons-input[^>]*checked[^>]*\/?>/', $html, $checkedInputs);
    expect(count($checkedInputs[0]))->toBe(0);
});

it('handles missing field in state gracefully for toggle buttons', function () {
    $html = FilamentShot::form([
        ToggleButtons::make('status')
            ->options(['active' => 'Active', 'blocked' => 'Blocked']),
    ])->state([])->toHtml();

    // No radio input should be checked when the field is absent from state
    preg_match_all('/<input[^>]*fi-fo-toggle-buttons-input[^>]*checked[^>]*\/?>/', $html, $checkedInputs);
    expect(count($checkedInputs[0]))->toBe(0);
});

it('renders regular Radio component with checked state injected', function () {
    $html = FilamentShot::form([
        Radio::make('role')
            ->label('Role')
            ->options([
                'admin' => 'Administrator',
                'editor' => 'Editor',
                'viewer' => 'Viewer',
            ]),
    ])->state(['role' => 'editor'])->toHtml();

    expect($html)->toContain('fi-fo-radio');

    // The radio input for 'editor' should be checked
    preg_match_all('/<input[^>]*type=["\']radio["\'][^>]*checked[^>]*\/?>/', $html, $checkedInputs);
    expect(count($checkedInputs[0]))->toBe(1);
    expect($checkedInputs[0][0])->toContain('value="editor"');
});

it('does not mark any Radio option checked when state is empty', function () {
    $html = FilamentShot::form([
        Radio::make('role')
            ->options(['admin' => 'Admin', 'editor' => 'Editor']),
    ])->state([])->toHtml();

    preg_match_all('/<input[^>]*type=["\']radio["\'][^>]*checked[^>]*\/?>/', $html, $checkedInputs);
    expect(count($checkedInputs[0]))->toBe(0);
});

it('form with both ToggleButtons and Radio does not cross-contaminate checked state', function () {
    $html = FilamentShot::form([
        ToggleButtons::make('status')
            ->options(['active' => 'Active', 'blocked' => 'Blocked', 'pending' => 'Pending']),
        Radio::make('role')
            ->options(['admin' => 'Admin', 'editor' => 'Editor', 'viewer' => 'Viewer']),
    ])->state(['status' => 'pending', 'role' => 'admin'])->toHtml();

    expect($html)->toContain('fi-fo-toggle-buttons');
    expect($html)->toContain('fi-fo-radio');

    // Only one ToggleButtons radio should be checked
    preg_match_all('/<input[^>]*fi-fo-toggle-buttons-input[^>]*checked[^>]*\/?>/', $html, $tbChecked);
    expect(count($tbChecked[0]))->toBe(1);
    expect($tbChecked[0][0])->toContain('value="pending"');

    // Only one Radio input should be checked, and it must be 'admin'
    // Filter out ToggleButtons inputs from the radio check count
    preg_match_all('/<input[^>]*type=["\']radio["\'][^>]*checked[^>]*\/?>/', $html, $allChecked);
    $radioOnly = array_filter($allChecked[0], fn ($i) => ! str_contains($i, 'fi-fo-toggle-buttons-input'));
    expect(count($radioOnly))->toBe(1);
    expect(array_values($radioOnly)[0])->toContain('value="admin"');
});

it('ToggleButtons with wire:model prefix that is a substring of another field name does not misassign state', function () {
    // 'status' is a prefix of 'sub_status' — the regex must not confuse them
    $html = FilamentShot::form([
        ToggleButtons::make('status')
            ->options(['active' => 'Active', 'blocked' => 'Blocked']),
        ToggleButtons::make('sub_status')
            ->options(['open' => 'Open', 'closed' => 'Closed']),
    ])->state(['status' => 'active', 'sub_status' => 'closed'])->toHtml();

    preg_match_all('/<input[^>]*fi-fo-toggle-buttons-input[^>]*checked[^>]*\/?>/', $html, $checkedInputs);
    expect(count($checkedInputs[0]))->toBe(2);

    $checkedHtml = implode(' ', $checkedInputs[0]);
    expect($checkedHtml)->toContain('value="active"');
    expect($checkedHtml)->toContain('value="closed"');
    // 'blocked' and 'open' must NOT be checked
    preg_match_all('/value="([^"]+)"/', $checkedHtml, $vals);
    expect($vals[1])->not->toContain('blocked');
    expect($vals[1])->not->toContain('open');
});
