<div class="fi-fo-field-label-col" style="display: flex; align-items: center; gap: 0.75rem;">
    <input
        type="checkbox"
        name="{{ $field['name'] }}"
        @if($field['value'] ?? false) checked @endif
        @if($field['disabled'] ?? false) disabled @endif
        class="fi-checkbox-input"
    />
    @if(!empty($field['label']))
        <label class="fi-fo-field-label">
            <span class="fi-fo-field-label-content">{{ $field['label'] }}</span>
        </label>
    @endif
</div>
