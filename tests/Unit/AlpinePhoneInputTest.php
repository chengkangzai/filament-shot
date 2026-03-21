<?php

use CCK\FilamentShot\FilamentShot;
use CCK\FilamentShot\Renderers\BaseRenderer;
use CCK\FilamentShot\Support\AssetResolver;
use CCK\FilamentShot\Support\BrowsershotFactory;
use Filament\Forms\Components\TextInput;

// ---------------------------------------------------------------------------
// BaseRenderer: sanitizeHtml
// ---------------------------------------------------------------------------

it('strips all x-load-src attributes regardless of URL', function () {
    $renderer = new class extends BaseRenderer {
        protected function renderContent(): string { return ''; }

        public function callSanitize(string $html): string
        {
            return $this->sanitizeHtml($html, []);
        }
    };

    $html = '<div x-data="foo" x-load-src="http://localhost:8000/js/foo.js"></div>';
    expect($renderer->callSanitize($html))->not->toContain('x-load-src');

    $html = '<div x-data="foo" x-load-src="/js/foo.js"></div>';
    expect($renderer->callSanitize($html))->not->toContain('x-load-src');

    $html = '<div x-data="foo" x-load-src="https://cdn.example.com/foo.js"></div>';
    expect($renderer->callSanitize($html))->not->toContain('x-load-src');
});

it('strips x-load-css attributes', function () {
    $renderer = new class extends BaseRenderer {
        protected function renderContent(): string { return ''; }

        public function callSanitize(string $html): string
        {
            return $this->sanitizeHtml($html, []);
        }
    };

    $html = '<div x-load-css="[&quot;/css/foo.css&quot;]">content</div>';
    expect($renderer->callSanitize($html))->not->toContain('x-load-css');
});

it('removes Livewire loading indicator elements', function () {
    $renderer = new class extends BaseRenderer {
        protected function renderContent(): string { return ''; }

        public function callSanitize(string $html): string
        {
            return $this->sanitizeHtml($html, []);
        }
    };

    $html = '<button>Save <svg wire:loading.delay class="spinner"></svg></button>';
    $result = $renderer->callSanitize($html);
    expect($result)
        ->not->toContain('wire:loading.delay')
        ->toContain('<button>');
});

it('replaces __FILAMENT_SHOT_PLUGIN_JS__ placeholder with empty string when no alpine paths', function () {
    $renderer = new class extends BaseRenderer {
        protected function renderContent(): string { return ''; }

        public function callSanitize(string $html): string
        {
            return $this->sanitizeHtml($html, []);
        }
    };

    $html = '<body><!-- __FILAMENT_SHOT_PLUGIN_JS__ --><script></script></body>';
    expect($renderer->callSanitize($html))
        ->not->toContain('__FILAMENT_SHOT_PLUGIN_JS__')
        ->toContain('<script></script>');
});

// ---------------------------------------------------------------------------
// BaseRenderer: buildAlpineRegistration
// ---------------------------------------------------------------------------

it('transforms export{X as default} into Alpine.data() registration', function () {
    $renderer = new class extends BaseRenderer {
        protected function renderContent(): string { return ''; }

        public function callBuild(string $name, string $js): string
        {
            return $this->buildAlpineRegistration($name, $js);
        }
    };

    $js = 'function F0(){return{value:null}}export{F0 as default}';
    $result = $renderer->callBuild('myComponent', $js);

    expect($result)
        ->toContain("Alpine.data('myComponent',F0)")
        ->toContain("alpine:init")
        ->not->toContain('export{');
});

it('buildAlpineRegistration uses the provided component name not the exported variable', function () {
    $renderer = new class extends BaseRenderer {
        protected function renderContent(): string { return ''; }

        public function callBuild(string $name, string $js): string
        {
            return $this->buildAlpineRegistration($name, $js);
        }
    };

    $js = 'var X=function(){return{}}; export{X as default}';
    $result = $renderer->callBuild('phoneInputFormComponent', $js);

    expect($result)->toContain("Alpine.data('phoneInputFormComponent',X)");
});

it('buildAlpineRegistration returns raw content when export pattern not found', function () {
    $renderer = new class extends BaseRenderer {
        protected function renderContent(): string { return ''; }

        public function callBuild(string $name, string $js): string
        {
            return $this->buildAlpineRegistration($name, $js);
        }
    };

    $js = 'window.myLib = function() {}';
    $result = $renderer->callBuild('myLib', $js);

    expect($result)->toBe($js);
});

// ---------------------------------------------------------------------------
// BrowsershotFactory: inlineFileScripts
// ---------------------------------------------------------------------------

it('inlines file:// script src tags with file contents', function () {
    $factory = new BrowsershotFactory;

    $tmpFile = tempnam(sys_get_temp_dir(), 'fs-test-') . '.js';
    file_put_contents($tmpFile, 'console.log("hello");');
    register_shutdown_function(fn () => @unlink($tmpFile));

    $html = "<html><body><script src=\"file://{$tmpFile}\"></script></body></html>";

    $reflection = new ReflectionMethod($factory, 'inlineFileScripts');
    $result = $reflection->invoke($factory, $html);

    expect($result)
        ->toContain('console.log("hello");')
        ->not->toContain("file://{$tmpFile}");
});

it('leaves intact script src tags that are not file:// URLs', function () {
    $factory = new BrowsershotFactory;

    $html = '<script src="https://example.com/app.js"></script>';

    $reflection = new ReflectionMethod($factory, 'inlineFileScripts');
    $result = $reflection->invoke($factory, $html);

    expect($result)->toBe($html);
});

it('leaves intact file:// script tags when file does not exist', function () {
    $factory = new BrowsershotFactory;

    $html = '<script src="file:///nonexistent/path/script.js"></script>';

    $reflection = new ReflectionMethod($factory, 'inlineFileScripts');
    $result = $reflection->invoke($factory, $html);

    expect($result)->toBe($html);
});

// ---------------------------------------------------------------------------
// AssetResolver: getCoreJsFileUrls
// ---------------------------------------------------------------------------

it('getCoreJsFileUrls returns livewire.min.js as first entry', function () {
    $resolver = app(AssetResolver::class);
    $urls = $resolver->getCoreJsFileUrls();

    expect($urls)->not->toBeEmpty();
    expect($urls[0])->toContain('livewire.min.js');
});

it('getCoreJsFileUrls returns file:// URLs for existing files only', function () {
    $resolver = app(AssetResolver::class);
    $urls = $resolver->getCoreJsFileUrls();

    foreach ($urls as $url) {
        expect($url)->toStartWith('file://');
        $path = substr($url, 7);
        expect(file_exists($path))->toBeTrue("File not found: {$path}");
    }
});

// ---------------------------------------------------------------------------
// AssetResolver: rewriteCssUrls
// ---------------------------------------------------------------------------

it('rewriteCssUrls leaves data: and https:// URLs unchanged', function () {
    $resolver = app(AssetResolver::class);
    $reflection = new ReflectionMethod($resolver, 'rewriteCssUrls');

    $css = '.icon { background: url("data:image/png;base64,abc"); }
.logo { background: url("https://example.com/logo.png"); }';

    $result = $reflection->invoke($resolver, $css, '/tmp/fake.css');

    expect($result)->toBe($css);
});

it('rewriteCssUrls rewrites relative path url() to base64 data URI when file exists', function () {
    $resolver = app(AssetResolver::class);
    $reflection = new ReflectionMethod($resolver, 'rewriteCssUrls');

    // Create a temp image file to reference
    $tmpDir = sys_get_temp_dir() . '/fs-css-test-' . uniqid();
    mkdir($tmpDir);
    $imgFile = $tmpDir . '/sprite.png';
    file_put_contents($imgFile, "\x89PNG\r\n");
    $cssFile = $tmpDir . '/style.css';

    $css = '.flag { background: url("./sprite.png"); }';
    $result = $reflection->invoke($resolver, $css, $cssFile);

    expect($result)->toContain('data:')
        ->toContain('base64,')
        ->not->toContain('./sprite.png');

    @unlink($imgFile);
    @rmdir($tmpDir);
});

it('rewriteCssUrls leaves url() unchanged when file does not exist', function () {
    $resolver = app(AssetResolver::class);
    $reflection = new ReflectionMethod($resolver, 'rewriteCssUrls');

    $css = '.flag { background: url("../img/nonexistent.webp"); }';
    $result = $reflection->invoke($resolver, $css, '/tmp/fake/dist/style.css');

    expect($result)->toBe($css);
});

// ---------------------------------------------------------------------------
// Form HTML: wire state script injection
// ---------------------------------------------------------------------------

it('form with phone-like state includes wire state window variable', function () {
    $html = FilamentShot::form([
        TextInput::make('phone')->label('Phone'),
    ])->state(['phone' => '+60123456789'])->toHtml();

    expect($html)->toContain('__filamentShotWireState');
    expect($html)->toContain('data.phone');
    expect($html)->toContain('+60123456789');
});

it('modal CSS forces .fi-modal visible without requiring x-cloak', function () {
    $html = FilamentShot::form([
        TextInput::make('name')->label('Name'),
    ])->modal('Edit User')->toHtml();

    // Should not rely on [x-cloak] selector — Alpine removes it at runtime
    expect($html)->toMatch('/\.fi-modal\s*\{/');
    expect($html)->not->toMatch('/\.fi-modal\[x-cloak\]/');
});
