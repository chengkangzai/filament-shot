<div class="fi-section">
    @if(!empty($layout['heading']))
        <div class="fi-section-header" style="display: flex; flex-direction: column; gap: 0.25rem; padding: 1rem 1.5rem;">
            <h3 class="fi-section-heading" style="font-size: 1rem; font-weight: 600; line-height: 1.5rem;">{{ $layout['heading'] }}</h3>
            @if(!empty($layout['description']))
                <p class="fi-section-description" style="font-size: 0.875rem; color: var(--gray-500);">{{ $layout['description'] }}</p>
            @endif
        </div>
    @endif
    <div class="fi-section-content" style="display: flex; flex-direction: column; gap: 1.5rem; padding: 1rem 1.5rem;">
        @foreach($layout['children'] ?? [] as $component)
            @include('filament-shot::components.form-component', ['component' => $component])
        @endforeach
    </div>
</div>
