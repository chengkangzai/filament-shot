<div class="fi-input-wrp fi-fo-select fi-fo-select-native">
    <div class="fi-input-wrp-content-ctn">
        <select
            name="{{ $field['name'] }}"
            @if($field['disabled'] ?? false) disabled @endif
            class="fi-select-input"
        >
            @if(!empty($field['placeholder']))
                <option value="">{{ $field['placeholder'] }}</option>
            @endif
            @foreach($field['options'] ?? [] as $value => $label)
                <option value="{{ $value }}" @if(($field['value'] ?? '') == $value) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
