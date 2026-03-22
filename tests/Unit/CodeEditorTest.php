<?php

use CCK\FilamentShot\Renderers\FormRenderer;

// Helper: call protected buildCodeEditorHtml via reflection
function buildCodeEditorHtml(string $code, ?string $language): string
{
    $renderer = new FormRenderer([]);
    $method = new ReflectionMethod($renderer, 'buildCodeEditorHtml');

    return $method->invoke($renderer, $code, $language);
}

// Helper: call protected fixCodeEditor via reflection
function fixCodeEditorHtml(string $html, array $data): string
{
    $renderer = new FormRenderer([]);
    $method = new ReflectionMethod($renderer, 'fixCodeEditor');

    return $method->invoke($renderer, $html, $data);
}

// --- buildCodeEditorHtml tests ---

it('renders code editor with code-like appearance', function () {
    $html = buildCodeEditorHtml('<?php echo "Hello World"; ?>', null);

    expect($html)->toContain('fi-fo-code-editor-static');
    expect($html)->toContain('cm-content');
    expect($html)->toContain('cm-gutters');
});

it('renders code editor content is visible without alpine', function () {
    $html = buildCodeEditorHtml('function greet() { return "hi"; }', null);

    expect($html)->toContain('greet');
    // No x-cloak in the static replacement
    expect($html)->not->toContain('x-cloak');
});

it('renders code editor with language class', function () {
    $html = buildCodeEditorHtml('<?php echo "test"; ?>', 'php');

    expect($html)->toContain('language-php');
    expect($html)->toContain('PHP');
});

it('renders code editor with multiline content and correct line numbers', function () {
    $code = "function greet(\$name) {\n    return \"Hello, {\$name}!\";\n}";

    $html = buildCodeEditorHtml($code, null);

    expect($html)->toContain('greet');
    expect($html)->toContain('Hello');
    // Line numbers 1, 2, 3 should all be present
    expect($html)->toContain('>1<');
    expect($html)->toContain('>2<');
    expect($html)->toContain('>3<');
});

it('renders code editor with html-escaped content', function () {
    $html = buildCodeEditorHtml('<div class="foo">bar</div>', null);

    expect($html)->toContain('fi-fo-code-editor-static');
    // Content should be HTML-escaped
    expect($html)->toContain('&lt;div');
    expect($html)->toContain('&gt;');
    // Raw < should not appear in cm-content area (it's escaped)
    expect($html)->not->toContain('<div class="foo">');
});

it('renders code editor without content when state is empty', function () {
    $html = buildCodeEditorHtml('', null);

    expect($html)->toContain('fi-fo-code-editor-static');
    expect($html)->toContain('cm-content');
    // Still renders the structure (one line with a space placeholder)
    expect($html)->toContain('cm-line');
});

it('escapes xss content in code', function () {
    $html = buildCodeEditorHtml('<script>alert(1)</script>', null);

    expect($html)->toContain('fi-fo-code-editor-static');
    // The script tag must be escaped
    expect($html)->toContain('&lt;script&gt;');
    expect($html)->not->toContain('<script>alert(1)</script>');
});

it('escapes closing pre and code tags in code content', function () {
    $html = buildCodeEditorHtml('</pre></code>', null);

    expect($html)->not->toContain('</pre></code>');
    expect($html)->toContain('&lt;/pre&gt;');
    expect($html)->toContain('&lt;/code&gt;');
});

it('renders code editor without language badge when no language set', function () {
    $html = buildCodeEditorHtml('echo "hi";', null);

    expect($html)->toContain('fi-fo-code-editor-static');
    expect($html)->not->toContain('fi-fo-code-editor-lang');
    expect($html)->not->toContain('language-');
});

it('escapes language value in class and badge', function () {
    $html = buildCodeEditorHtml('code', '"><script>alert(1)</script>');

    expect($html)->not->toContain('<script>');
    // The language is escaped in the class and label
    expect($html)->toContain('&gt;');
});

it('renders correct line numbers for 100+ lines', function () {
    $lines = implode("\n", range(1, 120));
    $html = buildCodeEditorHtml($lines, null);

    expect($html)->toContain('>100<');
    expect($html)->toContain('>101<');
    expect($html)->toContain('>120<');
});

// --- fixCodeEditor tests ---

it('fixCodeEditor returns html unchanged when no code editor present', function () {
    $html = '<div class="fi-fo-text-input">hello</div>';
    $result = fixCodeEditorHtml($html, []);

    expect($result)->toBe($html);
});

it('fixCodeEditor replaces code editor with static block', function () {
    // Simulate the HTML that Filament renders for a CodeEditor
    $html = '<div x-data="codeEditorFormComponent({state: $wire.$entangle(&#039;data.snippet&#039;), language: \'php\'})" class="fi-fo-code-editor"><div x-ref="editor" x-cloak></div></div></div>';

    $result = fixCodeEditorHtml($html, ['snippet' => 'echo "hi";']);

    expect($result)->toContain('fi-fo-code-editor-static');
    expect($result)->toContain('echo &quot;hi&quot;');
    expect($result)->not->toContain('x-cloak');
});

it('fixCodeEditor renders empty editor when state key is missing', function () {
    $html = '<div x-data="codeEditorFormComponent({state: $wire.$entangle(&#039;data.code&#039;), language: \'php\'})" class="fi-fo-code-editor"><div x-ref="editor" x-cloak></div></div></div>';

    $result = fixCodeEditorHtml($html, []);

    expect($result)->toContain('fi-fo-code-editor-static');
    // Should not crash, just render empty editor
    expect($result)->toContain('cm-content');
});
