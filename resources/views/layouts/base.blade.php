<!DOCTYPE html>
<html lang="en" class="{{ $darkMode ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>{!! $themeCss !!}</style>
    <style>
        @import url('https://fonts.googleapis.com/css2?family={{ urlencode($font) }}:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: {{ $primaryColor }};
            --font-family: '{{ $font }}';
            --mono-font-family: ui-monospace;
            --serif-font-family: ui-serif;
            {!! $colorVariables !!}
        }

        /* Hide Livewire loading indicators — spinners visible in static HTML */
        .fi-loading-indicator { display: none !important; }

        /* Force Filament modal visible and static for screenshots.
           The modal component uses Alpine.js (x-cloak, x-show) which hides
           everything by default. Override to force visibility and use relative
           positioning so Browsershot can measure content height. */
        .fi-modal[x-cloak] {
            display: flex !important;
            position: relative !important;
        }
        .fi-modal .fi-modal-close-overlay {
            display: none !important;
        }
        .fi-modal .fi-modal-window-ctn {
            position: relative !important;
            width: 100%;
        }
        .fi-modal .fi-modal-window {
            position: relative !important;
        }

        /* Force Filament notification visible for screenshots.
           The notification component uses Alpine.js transitions which start
           invisible. Override to force visibility in static HTML. */
        .fi-no-notification {
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* FileUpload: style as FilePond dropzone since JS doesn't initialize */
        .fi-fo-file-upload {
            position: relative;
        }
        .fi-fo-file-upload-input-ctn {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 7rem;
            border: 2px dashed rgb(209 213 219);
            border-radius: 0.75rem;
            background-color: rgb(249 250 251);
            cursor: pointer;
            transition: border-color 0.15s;
        }
        .fi-fo-file-upload-input-ctn input[type="file"] {
            display: none;
        }
        .fi-fo-file-upload-input-ctn::after {
            content: 'Drag & drop your file or browse';
            display: block;
            font-size: 0.875rem;
            color: rgb(107 114 128);
        }
        .dark .fi-fo-file-upload-input-ctn {
            border-color: rgb(75 85 99);
            background-color: rgb(31 41 55);
        }
        .dark .fi-fo-file-upload-input-ctn::after {
            color: rgb(156 163 175);
        }

        /* Table rows: ensure white background so they don't inherit gray from grid context */
        @layer base {
            .fi-ta-table > tbody > tr:not(.fi-striped) {
                background-color: #ffffff;
            }
        }

        body {
            margin: 0;
            padding: 24px;
            min-height: auto !important;
            font-family: '{{ $font }}', ui-sans-serif, system-ui, sans-serif;
            background-color: {{ $darkMode ? '#111827' : '#ffffff' }};
        }
    </style>
    @if($extraCss)
    <style>{!! $extraCss !!}</style>
    @endif
    {{-- Stub $wire Alpine magic so Livewire-dependent components don't crash.
         State values are seeded from PHP rendering context so x-data components
         that use $wire.$entangle() receive the correct initial values. --}}
    <script>
        document.addEventListener('alpine:init', () => {
            if (typeof Alpine !== 'undefined' && !Alpine._magics?.wire) {
                const state = window.__filamentShotWireState || {};
                Alpine.magic('wire', () => ({
                    $entangle: (path) => state[path] ?? null,
                    $commit: () => {},
                    callSchemaComponentMethod: () => Promise.resolve({}),
                    get __instance() { return { canonical: state, ephemeral: state }; },
                }));
            }
        });
    </script>
</head>
<body class="fi-body antialiased {{ $darkMode ? 'dark' : '' }}">
    <div style="max-width: {{ $contentWidth ?? '100%' }}; margin: 0 auto;">
        {!! $content !!}
    </div>
    {{-- Placeholder replaced by BaseRenderer with plugin Alpine.data() registrations
         extracted from rendered HTML. Must appear BEFORE coreJsUrls so the registrations
         are queued on 'alpine:init' before Alpine.start() fires. --}}
    <!-- __FILAMENT_SHOT_PLUGIN_JS__ -->
    {{-- Core Filament JS bundles: includes Alpine.js. Loads last so plugin
         Alpine.data() registrations above are already queued when Alpine starts. --}}
    @foreach($coreJsUrls as $url)
    <script src="{{ $url }}"></script>
    @endforeach
</body>
</html>
