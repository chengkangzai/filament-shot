<div class="fi-in" style="display: flex; flex-direction: column; gap: 1.5rem;">
    @foreach($entries as $entry)
        <div class="fi-in-entry" style="display: flex; flex-direction: column; gap: 0.25rem;">
            <dt class="fi-in-entry-label" style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">
                {{ $entry['label'] }}
            </dt>
            <dd class="fi-in-entry-content" style="font-size: 0.875rem; color: #111827; margin: 0;">
                {{ $entry['value'] ?? '' }}
            </dd>
        </div>
    @endforeach
</div>
