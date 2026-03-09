<fieldset class="fi-fieldset" style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1.5rem;">
    @if(!empty($layout['label']))
        <legend class="fi-fieldset-legend" style="font-size: 0.875rem; font-weight: 500; padding: 0 0.25rem;">{{ $layout['label'] }}</legend>
    @endif
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        @foreach($layout['children'] ?? [] as $component)
            @include('filament-shot::components.form-component', ['component' => $component])
        @endforeach
    </div>
</fieldset>
