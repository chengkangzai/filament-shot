<div class="fi-input-wrp fi-fo-textarea">
    <div class="fi-input-wrp-content-ctn">
        <textarea
            name="{{ $field['name'] }}"
            rows="{{ $field['rows'] ?? 3 }}"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            @if($field['disabled'] ?? false) disabled @endif
            class="fi-input"
        >{{ $field['value'] ?? '' }}</textarea>
    </div>
</div>
