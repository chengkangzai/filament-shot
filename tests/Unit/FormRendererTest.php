<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

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
        ->toContain('fi-fo-textarea')
        ->toContain('name="bio"');
});

it('renders a toggle field', function () {
    $html = FilamentShot::form([
        Toggle::make('active')->label('Active'),
    ])->toHtml();

    expect($html)
        ->toContain('Active')
        ->toContain('fi-toggle');
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

// --- Layout components ---

it('renders a section with heading', function () {
    $html = FilamentShot::form([
        Section::make('Personal Info')
            ->schema([
                TextInput::make('name')->label('Name'),
            ]),
    ])->toHtml();

    expect($html)
        ->toContain('fi-section')
        ->toContain('Personal Info')
        ->toContain('fi-input');
});

it('renders a section with description', function () {
    $html = FilamentShot::form([
        Section::make('Details')
            ->description('Fill in your details')
            ->schema([
                TextInput::make('name'),
            ]),
    ])->toHtml();

    expect($html)
        ->toContain('fi-section')
        ->toContain('Fill in your details');
});

it('renders a fieldset with label', function () {
    $html = FilamentShot::form([
        Fieldset::make('Contact')
            ->schema([
                TextInput::make('phone')->label('Phone'),
            ]),
    ])->toHtml();

    expect($html)
        ->toContain('fi-fieldset')
        ->toContain('Contact')
        ->toContain('fi-input');
});

it('renders a grid with columns', function () {
    $html = FilamentShot::form([
        Grid::make(3)
            ->schema([
                TextInput::make('first')->label('First'),
                TextInput::make('middle')->label('Middle'),
                TextInput::make('last')->label('Last'),
            ]),
    ])->toHtml();

    expect($html)
        ->toContain('fi-grid')
        ->toContain('repeat(3')
        ->toContain('fi-input');
});

it('renders nested layouts', function () {
    $html = FilamentShot::form([
        Section::make('Outer')
            ->schema([
                Fieldset::make('Inner')
                    ->schema([
                        TextInput::make('nested')->label('Nested Field'),
                    ]),
            ]),
    ])->toHtml();

    expect($html)
        ->toContain('fi-section')
        ->toContain('fi-fieldset')
        ->toContain('Nested Field');
});

// --- New field types ---

it('renders a date picker field', function () {
    $html = FilamentShot::form([
        DatePicker::make('birthday')->label('Birthday'),
    ])->state(['birthday' => '2024-01-15'])->toHtml();

    expect($html)
        ->toContain('Birthday')
        ->toContain('fi-fo-text-input')
        ->toContain('2024-01-15');
});

it('renders a date time picker field', function () {
    $html = FilamentShot::form([
        DateTimePicker::make('starts_at')->label('Starts At'),
    ])->state(['starts_at' => '2024-01-15 10:00'])->toHtml();

    expect($html)
        ->toContain('Starts At')
        ->toContain('fi-fo-text-input')
        ->toContain('2024-01-15 10:00');
});

it('renders a file upload field', function () {
    $html = FilamentShot::form([
        FileUpload::make('avatar')->label('Avatar'),
    ])->toHtml();

    expect($html)
        ->toContain('Avatar')
        ->toContain('fi-fo-file-upload');
});

it('renders a color picker field', function () {
    $html = FilamentShot::form([
        ColorPicker::make('theme_color')->label('Theme Color'),
    ])->state(['theme_color' => '#3b82f6'])->toHtml();

    expect($html)
        ->toContain('Theme Color')
        ->toContain('fi-fo-color-picker')
        ->toContain('#3b82f6');
});

it('renders a tags input field', function () {
    $html = FilamentShot::form([
        TagsInput::make('tags')->label('Tags'),
    ])->state(['tags' => ['Laravel', 'PHP']])->toHtml();

    expect($html)
        ->toContain('Tags')
        ->toContain('fi-fo-tags-input')
        ->toContain('Laravel')
        ->toContain('PHP');
});

it('renders a key value field', function () {
    $html = FilamentShot::form([
        KeyValue::make('metadata')->label('Metadata'),
    ])->state(['metadata' => ['key1' => 'value1']])->toHtml();

    expect($html)
        ->toContain('Metadata')
        ->toContain('fi-fo-key-value')
        ->toContain('key1')
        ->toContain('value1');
});

it('renders a rich editor field', function () {
    $html = FilamentShot::form([
        RichEditor::make('content')->label('Content'),
    ])->state(['content' => '<p>Hello world</p>'])->toHtml();

    expect($html)
        ->toContain('Content')
        ->toContain('fi-fo-rich-editor')
        ->toContain('<p>Hello world</p>');
});

it('renders a markdown editor field', function () {
    $html = FilamentShot::form([
        MarkdownEditor::make('notes')->label('Notes'),
    ])->state(['notes' => '# Heading'])->toHtml();

    expect($html)
        ->toContain('Notes')
        ->toContain('fi-fo-markdown-editor')
        ->toContain('# Heading');
});

it('renders a repeater field with items', function () {
    $html = FilamentShot::form([
        Repeater::make('items')
            ->label('Items')
            ->schema([
                TextInput::make('name')->label('Item Name'),
            ]),
    ])->state(['items' => [
        ['name' => 'Item One'],
        ['name' => 'Item Two'],
    ]])->toHtml();

    expect($html)
        ->toContain('Items')
        ->toContain('fi-fo-repeater')
        ->toContain('Item One')
        ->toContain('Item Two');
});
