<div class="fi-in" style="display: flex; flex-direction: column; gap: 1.5rem;">
    @foreach($entries as $entry)
        <div class="fi-in-entry">
            <div class="fi-in-entry-label-col">
                <div class="fi-in-entry-label-ctn">
                    <dt class="fi-in-entry-label">
                        {{ $entry['label'] }}
                    </dt>
                </div>
            </div>
            <div class="fi-in-entry-content-col">
                <dd class="fi-in-entry-content-ctn">
                    <div class="fi-in-entry-content">
                        <div class="fi-in-text">
                            <div class="fi-in-text-item">
                                {{ $entry['value'] ?? '' }}
                            </div>
                        </div>
                    </div>
                </dd>
            </div>
        </div>
    @endforeach
</div>
