<div class="fi-grid" style="display: grid; grid-template-columns: repeat({{ $layout['columns'] ?? 2 }}, minmax(0, 1fr)); gap: 1.5rem;">
    @foreach($layout['children'] ?? [] as $component)
        @include('filament-shot::components.form-component', ['component' => $component])
    @endforeach
</div>
