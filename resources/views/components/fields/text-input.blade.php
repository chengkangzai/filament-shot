<div class="fi-input-wrp fi-fo-text-input">
    <div class="fi-input-wrp-content-ctn">
        <input
            type="{{ $field['input_type'] ?? 'text' }}"
            name="{{ $field['name'] }}"
            value="{{ $field['value'] ?? '' }}"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            @if($field['disabled'] ?? false) disabled @endif
            @if($field['readonly'] ?? false) readonly @endif
            class="fi-input"
        />
    </div>
</div>
