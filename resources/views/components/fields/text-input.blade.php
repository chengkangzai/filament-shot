<input
    type="{{ $field['input_type'] ?? 'text' }}"
    name="{{ $field['name'] }}"
    value="{{ $field['value'] ?? '' }}"
    placeholder="{{ $field['placeholder'] ?? '' }}"
    @if($field['disabled'] ?? false) disabled @endif
    @if($field['readonly'] ?? false) readonly @endif
    class="fi-input"
    style="display: block; width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; background-color: white; padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #111827; outline: none; box-sizing: border-box;"
/>
