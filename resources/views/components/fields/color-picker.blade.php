<div class="fi-fo-color-picker" style="display: flex; align-items: center; gap: 0.5rem;">
    <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; background-color: {{ $field['value'] ?? '#000000' }}; border: 1px solid var(--gray-300); flex-shrink: 0;"></div>
    <div class="fi-input-wrp fi-fo-text-input" style="flex: 1;">
        <div class="fi-input-wrp-content-ctn">
            <input
                type="text"
                name="{{ $field['name'] }}"
                value="{{ $field['value'] ?? '' }}"
                @if($field['disabled'] ?? false) disabled @endif
                class="fi-input"
            />
        </div>
    </div>
</div>
