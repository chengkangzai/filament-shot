<select
    name="{{ $field['name'] }}"
    @if($field['disabled'] ?? false) disabled @endif
    class="fi-select-input"
    style="display: block; width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; background-color: white; padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #111827; outline: none; box-sizing: border-box; appearance: none;"
>
    @if(!empty($field['placeholder']))
        <option value="">{{ $field['placeholder'] }}</option>
    @endif
    @foreach($field['options'] ?? [] as $value => $label)
        <option value="{{ $value }}" @if(($field['value'] ?? '') == $value) selected @endif>{{ $label }}</option>
    @endforeach
</select>
