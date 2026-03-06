<div class="fi-fo-radio" style="display: flex; flex-direction: column; gap: 0.75rem;">
    @foreach($field['options'] ?? [] as $value => $label)
        <label style="display: inline-flex; align-items: center; gap: 0.75rem; cursor: pointer;">
            <input
                type="radio"
                name="{{ $field['name'] }}"
                value="{{ $value }}"
                @if(($field['value'] ?? '') == $value) checked @endif
                @if($field['disabled'] ?? false) disabled @endif
                class="fi-radio-input"
            />
            <span class="fi-fo-field-label-content">{{ $label }}</span>
        </label>
    @endforeach
</div>
