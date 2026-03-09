<div class="fi-input-wrp fi-fo-tags-input">
    <div class="fi-input-wrp-content-ctn" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.375rem; padding: 0.5rem 0.75rem; min-height: 2.625rem;">
        @foreach($field['tags'] ?? [] as $tag)
            <span class="fi-badge fi-size-sm fi-color-primary" style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.125rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem;">{{ $tag }}</span>
        @endforeach
        <input
            type="text"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            @if($field['disabled'] ?? false) disabled @endif
            class="fi-input"
            style="border: none; outline: none; flex: 1; min-width: 4rem; padding: 0; box-shadow: none;"
        />
    </div>
</div>
