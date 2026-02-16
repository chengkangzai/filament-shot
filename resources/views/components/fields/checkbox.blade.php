<label style="display: inline-flex; align-items: center; gap: 0.75rem; cursor: pointer;">
    <input
        type="checkbox"
        name="{{ $field['name'] }}"
        @if($field['value'] ?? false) checked @endif
        @if($field['disabled'] ?? false) disabled @endif
        class="fi-checkbox-input"
        style="height: 1.25rem; width: 1.25rem; border-radius: 0.25rem; border: 1px solid #d1d5db; color: #6366f1;"
    />
    @if(!empty($field['label']))
        <span style="font-size: 0.875rem; color: #374151;">{{ $field['label'] }}</span>
    @endif
</label>
