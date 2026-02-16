<div class="fi-fo-component-ctn" style="display: flex; flex-direction: column; gap: 1.5rem;">
    @foreach($fields as $field)
        <div class="fi-fo-field-wrp">
            @if(!empty($field['label']) && $field['type'] !== 'checkbox')
                <label class="fi-fo-field-wrp-label" style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 500; color: #374151;">
                    {{ $field['label'] }}
                </label>
            @endif

            @include('filament-shot::components.fields.' . $field['type'], ['field' => $field])
        </div>
    @endforeach
</div>
