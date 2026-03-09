<div class="fi-fo-repeater" style="display: flex; flex-direction: column; gap: 1rem;{{ ($field['disabled'] ?? false) ? ' opacity: 0.5; pointer-events: none;' : '' }}">
    @forelse($field['items'] ?? [] as $index => $item)
        <div class="fi-fo-repeater-item" style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1rem;">
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($field['children'] ?? [] as $child)
                    @php
                        $childComponent = $child;
                        if (($childComponent['component_type'] ?? 'field') === 'field') {
                            $childComponent = array_merge($childComponent, [
                                'value' => $item[$childComponent['name'] ?? ''] ?? null,
                            ]);
                        }
                    @endphp
                    @include('filament-shot::components.form-component', ['component' => $childComponent])
                @endforeach
            </div>
        </div>
    @empty
        <div class="fi-fo-repeater-item" style="border: 1px solid var(--gray-200); border-radius: 0.75rem; padding: 1rem;">
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($field['children'] ?? [] as $child)
                    @include('filament-shot::components.form-component', ['component' => $child])
                @endforeach
            </div>
        </div>
    @endforelse
</div>
