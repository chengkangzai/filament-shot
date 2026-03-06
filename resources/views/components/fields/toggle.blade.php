@php
    $isOn = (bool) ($field['value'] ?? false);
    $stateClass = $isOn ? 'fi-toggle-on' : 'fi-toggle-off';
    $colorClasses = $isOn ? ($field['onColorClasses'] ?? '') : ($field['offColorClasses'] ?? '');
@endphp
<div class="fi-fo-field-label-col">
    <div class="fi-fo-field-label-ctn">
        <label class="fi-fo-field-label">
            <span class="fi-fo-field-label-content">{{ $field['label'] ?? '' }}</span>
        </label>
    </div>
</div>
<button
    type="button"
    role="switch"
    aria-checked="{{ $isOn ? 'true' : 'false' }}"
    @if($field['disabled'] ?? false) disabled @endif
    class="fi-toggle fi-fo-toggle {{ $stateClass }} {{ $colorClasses }}"
>
    <div>
        <div aria-hidden="true"></div>
        <div aria-hidden="true"></div>
    </div>
</button>
