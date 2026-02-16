<div class="fi-radio" style="display: flex; flex-direction: column; gap: 0.5rem;">
    @foreach($field['options'] ?? [] as $value => $label)
        <label style="display: inline-flex; align-items: center; gap: 0.75rem; cursor: pointer;">
            <input
                type="radio"
                name="{{ $field['name'] }}"
                value="{{ $value }}"
                @if(($field['value'] ?? '') == $value) checked @endif
                @if($field['disabled'] ?? false) disabled @endif
                style="height: 1.25rem; width: 1.25rem; border: 1px solid #d1d5db; color: #6366f1;"
            />
            <span style="font-size: 0.875rem; color: #374151;">{{ $label }}</span>
        </label>
    @endforeach
</div>
