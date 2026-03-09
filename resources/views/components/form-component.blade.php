@if(($component['component_type'] ?? 'field') === 'layout')
    @include('filament-shot::components.layouts.' . $component['layout'], ['layout' => $component])
@else
    @php $field = $component; @endphp
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
@endif
