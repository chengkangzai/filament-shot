<textarea
    name="{{ $field['name'] }}"
    rows="{{ $field['rows'] ?? 3 }}"
    placeholder="{{ $field['placeholder'] ?? '' }}"
    @if($field['disabled'] ?? false) disabled @endif
    class="fi-textarea-input"
    style="display: block; width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; background-color: white; padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #111827; outline: none; box-sizing: border-box; resize: vertical;"
>{{ $field['value'] ?? '' }}</textarea>
