<div class="fi-fo-component-ctn" style="display: flex; flex-direction: column; gap: 1.5rem;">
    @foreach($fields as $field)
        <div class="fi-fo-field">
            @if(!empty($field['label']) && !in_array($field['type'], ['checkbox', 'toggle']))
                <div class="fi-fo-field-label-col">
                    <div class="fi-fo-field-label-ctn">
                        <label class="fi-fo-field-label">
                            <span class="fi-fo-field-label-content">{{ $field['label'] }}</span>
                        </label>
                    </div>
                </div>
            @endif

            <div class="fi-fo-field-content-col">
                @include('filament-shot::components.fields.' . $field['type'], ['field' => $field])
            </div>
        </div>
    @endforeach
</div>
