<?php

use CCK\FilamentShot\FilamentShot;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;

it('renders code editor with code-like appearance', function () {
    $html = FilamentShot::form([
        CodeEditor::make('snippet')
            ->label('Code Snippet'),
    ])
        ->state(['snippet' => '<?php echo "Hello World"; ?>'])
        ->toHtml();

    expect($html)->toContain('fi-fo-code-editor');
    // After fix: should show the code content in a styled area
    expect($html)->toContain('Hello World');
});

it('renders code editor content is visible without alpine', function () {
    $html = FilamentShot::form([
        CodeEditor::make('script')
            ->label('Script'),
    ])
        ->state(['script' => 'function greet() { return "hi"; }'])
        ->toHtml();

    // Content should be visible in the HTML, not hidden behind x-cloak
    expect($html)->toContain('greet');
    // The x-cloak editor div should be replaced with visible content
    expect($html)->not->toContain('x-ref="editor" x-cloak');
});

it('renders code editor with language class', function () {
    $html = FilamentShot::form([
        CodeEditor::make('php_code')
            ->label('PHP Code')
            ->language(Language::Php),
    ])
        ->state(['php_code' => '<?php echo "test"; ?>'])
        ->toHtml();

    expect($html)->toContain('fi-fo-code-editor');
    // Should include language indicator
    expect($html)->toContain('language-php');
});

it('renders code editor with multiline content', function () {
    $code = "function greet(\$name) {\n    return \"Hello, {\$name}!\";\n}";

    $html = FilamentShot::form([
        CodeEditor::make('code')
            ->label('Code'),
    ])
        ->state(['code' => $code])
        ->toHtml();

    expect($html)->toContain('fi-fo-code-editor');
    expect($html)->toContain('greet');
    expect($html)->toContain('Hello');
});

it('renders code editor with html-escaped content', function () {
    $html = FilamentShot::form([
        CodeEditor::make('template')
            ->label('Template'),
    ])
        ->state(['template' => '<div class="foo">bar</div>'])
        ->toHtml();

    expect($html)->toContain('fi-fo-code-editor');
    // Content should be HTML-escaped in the output
    expect($html)->toContain('&lt;div');
});

it('renders code editor without content when state is empty', function () {
    $html = FilamentShot::form([
        CodeEditor::make('code')
            ->label('Code'),
    ])
        ->state([])
        ->toHtml();

    expect($html)->toContain('fi-fo-code-editor');
    // Should still render the code editor structure
    expect($html)->toContain('fi-fo-code-editor-static');
});
