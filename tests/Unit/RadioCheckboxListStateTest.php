<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;

it('renders radio with the selected option checked', function () {
    $html = FilamentShot::form([
        Radio::make('status')
            ->label('Status')
            ->options(['active' => 'Active', 'blocked' => 'Blocked', 'pending' => 'Pending']),
    ])->state(['status' => 'blocked'])->toHtml();

    // The blocked radio should have checked attribute, others should not
    expect($html)->toContain('value="blocked"');
    // Count checked attributes near radio inputs to verify only one is checked
    preg_match_all('/<input[^>]*type=["\']radio["\'][^>]*>/s', $html, $radios);
    $checkedCount = count(array_filter($radios[0], fn ($r) => str_contains($r, 'checked')));
    expect($checkedCount)->toBe(1);
});

it('renders checkboxlist with the selected options checked', function () {
    $html = FilamentShot::form([
        CheckboxList::make('roles')
            ->label('Roles')
            ->options(['admin' => 'Admin', 'editor' => 'Editor', 'viewer' => 'Viewer']),
    ])->state(['roles' => ['admin', 'viewer']])->toHtml();

    preg_match_all('/<input[^>]*type=["\']checkbox["\'][^>]*>/s', $html, $checkboxes);
    $checkedCount = count(array_filter($checkboxes[0], fn ($c) => str_contains($c, 'checked')));
    expect($checkedCount)->toBe(2);
});

it('renders radio with no selection when state is empty', function () {
    $html = FilamentShot::form([
        Radio::make('status')->options(['a' => 'A', 'b' => 'B']),
    ])->state([])->toHtml();

    preg_match_all('/<input[^>]*type=["\']radio["\'][^>]*>/s', $html, $radios);
    $checkedCount = count(array_filter($radios[0], fn ($r) => str_contains($r, 'checked')));
    expect($checkedCount)->toBe(0);
});

it('renders checkboxlist with no selection when state is empty', function () {
    $html = FilamentShot::form([
        CheckboxList::make('roles')->options(['a' => 'A', 'b' => 'B']),
    ])->state(['roles' => []])->toHtml();

    preg_match_all('/<input[^>]*type=["\']checkbox["\'][^>]*>/s', $html, $checkboxes);
    $checkedCount = count(array_filter($checkboxes[0], fn ($c) => str_contains($c, 'checked')));
    expect($checkedCount)->toBe(0);
});

it('does not check any radio when state key is absent', function () {
    // If the state does not contain the field key at all, no option should be pre-selected
    $html = FilamentShot::form([
        Radio::make('status')->options(['' => 'None', 'active' => 'Active']),
    ])->state([])->toHtml();

    preg_match_all('/<input[^>]*type=["\']radio["\'][^>]*>/s', $html, $radios);
    $checkedCount = count(array_filter($radios[0], fn ($r) => str_contains($r, 'checked')));
    expect($checkedCount)->toBe(0);
});

it('matches radio integer value against string option key', function () {
    // Integer state value 1 must match string option key "1"
    $html = FilamentShot::form([
        Radio::make('priority')
            ->options(['1' => 'Low', '2' => 'Medium', '3' => 'High']),
    ])->state(['priority' => 1])->toHtml();

    preg_match_all('/<input[^>]*type=["\']radio["\'][^>]*>/s', $html, $radios);
    $checkedCount = count(array_filter($radios[0], fn ($r) => str_contains($r, 'checked')));
    expect($checkedCount)->toBe(1);

    // The "1" / Low option should be the checked one
    $checkedInput = array_values(array_filter($radios[0], fn ($r) => str_contains($r, 'checked')))[0];
    expect($checkedInput)->toContain('value="1"');
});

it('does not falsely check a radio when integer 0 is in state and options are strings', function () {
    // PHP loose comparison: 0 == "active" is true — strict casting prevents this
    $html = FilamentShot::form([
        Radio::make('status')->options(['active' => 'Active', 'blocked' => 'Blocked']),
    ])->state(['status' => 0])->toHtml();

    preg_match_all('/<input[^>]*type=["\']radio["\'][^>]*>/s', $html, $radios);
    $checkedCount = count(array_filter($radios[0], fn ($r) => str_contains($r, 'checked')));
    expect($checkedCount)->toBe(0);
});

it('matches checkboxlist integer values against string option keys', function () {
    // Integer state values should match string option keys
    $html = FilamentShot::form([
        CheckboxList::make('ids')
            ->options(['1' => 'Item One', '2' => 'Item Two', '3' => 'Item Three']),
    ])->state(['ids' => [1, 3]])->toHtml();

    preg_match_all('/<input[^>]*type=["\']checkbox["\'][^>]*>/s', $html, $checkboxes);
    $checkedCount = count(array_filter($checkboxes[0], fn ($c) => str_contains($c, 'checked')));
    expect($checkedCount)->toBe(2);
});

it('coerces checkboxlist string state to array', function () {
    // A single string value should be treated as if it were a one-element array
    $html = FilamentShot::form([
        CheckboxList::make('roles')
            ->options(['admin' => 'Admin', 'editor' => 'Editor', 'viewer' => 'Viewer']),
    ])->state(['roles' => 'editor'])->toHtml();

    preg_match_all('/<input[^>]*type=["\']checkbox["\'][^>]*>/s', $html, $checkboxes);
    $checkedCount = count(array_filter($checkboxes[0], fn ($c) => str_contains($c, 'checked')));
    expect($checkedCount)->toBe(1);
});

it('html-escapes radio option labels', function () {
    $html = FilamentShot::form([
        Radio::make('type')
            ->options(['a' => '<script>alert(1)</script>', 'b' => 'Safe']),
    ])->state([])->toHtml();

    expect($html)->not->toContain('<script>alert(1)</script>');
    expect($html)->toContain('&lt;script&gt;');
});

it('html-escapes checkboxlist option labels', function () {
    $html = FilamentShot::form([
        CheckboxList::make('tags')
            ->options(['x' => '<b>Bold</b>', 'y' => 'Plain']),
    ])->state([])->toHtml();

    expect($html)->not->toContain('<b>Bold</b>');
    expect($html)->toContain('&lt;b&gt;');
});
