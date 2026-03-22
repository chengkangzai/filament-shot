<?php

use CCK\FilamentShot\FilamentShot;
use CCK\FilamentShot\Renderers\ViewRenderer;

it('FilamentShot::blade() returns a ViewRenderer', function () {
    expect(FilamentShot::blade('<div>test</div>'))->toBeInstanceOf(ViewRenderer::class);
});

it('FilamentShot::view() returns a ViewRenderer', function () {
    expect(FilamentShot::view('test-fixtures::tier-card'))->toBeInstanceOf(ViewRenderer::class);
});

it('renders a raw blade string', function () {
    $html = FilamentShot::blade('<div class="custom-class">Hello World</div>')
        ->toHtml();

    expect($html)->toContain('Hello World');
});

it('renders a raw blade string with data', function () {
    $html = FilamentShot::blade('<div>{{ $name }}</div>')
        ->data(['name' => 'Filament'])
        ->toHtml();

    expect($html)->toContain('Filament');
});

it('evaluates blade directives', function () {
    $html = FilamentShot::blade('@if(true)<span>Conditional</span>@endif')
        ->toHtml();

    expect($html)->toContain('Conditional');
});

it('wraps content in filament base layout', function () {
    $html = FilamentShot::blade('<div>Layout Test</div>')->toHtml();

    expect($html)
        ->toContain('<html')
        ->toContain('Layout Test');
});

it('applies dark mode', function () {
    $html = FilamentShot::blade('<div>Dark Mode</div>')
        ->darkMode()
        ->toHtml();

    expect($html)->toContain('class="dark"');
});

it('renders a named blade view with data', function () {
    app('view')->addNamespace('test-fixtures', __DIR__ . '/../fixtures/views');

    $html = FilamentShot::view('test-fixtures::tier-card')
        ->data([
            'tier' => ['name' => 'Gold', 'color' => '#FFD700'],
            'tierPoints' => 1500,
            'redeemablePoints' => 1200,
        ])
        ->toHtml();

    expect($html)
        ->toContain('Gold')
        ->toContain('1,500')
        ->toContain('Current Tier');
});

it('data method is chainable', function () {
    $renderer = FilamentShot::blade('<div>{{ $x }}</div>');
    $chained = $renderer->data(['x' => 'value']);

    expect($chained)->toBe($renderer);
});

it('data method merges when called multiple times', function () {
    $html = FilamentShot::blade('<div>{{ $a }} {{ $b }}</div>')
        ->data(['a' => 'first'])
        ->data(['b' => 'second'])
        ->toHtml();

    expect($html)
        ->toContain('first')
        ->toContain('second');
});

it('data method later call overwrites earlier key', function () {
    $html = FilamentShot::blade('<div>{{ $name }}</div>')
        ->data(['name' => 'Original'])
        ->data(['name' => 'Overwritten'])
        ->toHtml();

    expect($html)
        ->toContain('Overwritten')
        ->not->toContain('Original');
});

it('renders a blade component (heroicon) inside a blade string', function () {
    $html = FilamentShot::blade('<div><x-heroicon-o-check class="w-5 h-5" /></div>')
        ->toHtml();

    expect($html)->toContain('<svg');
});

it('empty blade string renders without crash', function () {
    $html = FilamentShot::blade('')->toHtml();

    expect($html)
        ->toBeString()
        ->toContain('<html');
});

it('blade syntax error throws an exception containing the syntax error message', function () {
    // An unclosed @if compiles to PHP with a dangling if block,
    // which causes a PHP parse error wrapped in an ErrorException at render time.
    // Note: the exception message includes "syntax error" and references the compiled view file.
    expect(fn () => FilamentShot::blade('@if(true)unclosed')->toHtml())
        ->toThrow('syntax error');
});
