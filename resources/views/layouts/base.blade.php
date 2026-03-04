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
            {!! $colorVariables !!}
        }

        body {
            margin: 0;
            padding: 24px;
            font-family: '{{ $font }}', ui-sans-serif, system-ui, sans-serif;
            background-color: {{ $darkMode ? '#111827' : '#f9fafb' }};
        }
    </style>
    @if($extraCss)
    <style>{!! $extraCss !!}</style>
    @endif
</head>
<body class="fi-body antialiased {{ $darkMode ? 'dark' : '' }}">
    <div style="max-width: {{ $contentWidth ?? '100%' }}; margin: 0 auto;">
        {!! $content !!}
    </div>
</body>
</html>
