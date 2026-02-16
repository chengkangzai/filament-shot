<button
    type="button"
    role="switch"
    aria-checked="{{ ($field['value'] ?? false) ? 'true' : 'false' }}"
    @if($field['disabled'] ?? false) disabled @endif
    class="fi-toggle-input"
    style="position: relative; display: inline-flex; height: 1.5rem; width: 2.75rem; flex-shrink: 0; cursor: pointer; border-radius: 9999px; border: 2px solid transparent; transition: background-color 0.2s; background-color: {{ ($field['value'] ?? false) ? '#6366f1' : '#d1d5db' }};"
>
    <span
        style="display: inline-block; height: 1.25rem; width: 1.25rem; border-radius: 9999px; background-color: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s; transform: translateX({{ ($field['value'] ?? false) ? '1.25rem' : '0' }});"
    ></span>
</button>
