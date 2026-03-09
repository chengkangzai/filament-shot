<div class="fi-fo-markdown-editor" style="border: 1px solid var(--gray-200); border-radius: 0.75rem; overflow: hidden;">
    <div class="fi-fo-markdown-editor-toolbar" style="display: flex; gap: 0.25rem; padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--gray-200); background: var(--gray-50);">
        <button type="button" style="padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-weight: 700; font-size: 0.875rem; color: var(--gray-500);">B</button>
        <button type="button" style="padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-style: italic; font-size: 0.875rem; color: var(--gray-500);">I</button>
        <button type="button" style="padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.875rem; color: var(--gray-500);">#</button>
        <button type="button" style="padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.875rem; color: var(--gray-500);">&lt;/&gt;</button>
    </div>
    <div class="fi-fo-markdown-editor-content" style="min-height: 8rem; padding: 0.75rem;">
        {{ $field['value'] ?? '' }}
    </div>
</div>
