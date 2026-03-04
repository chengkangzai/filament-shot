@php
    $badgeColors = [
        'danger'  => ['bg' => '#fef2f2', 'text' => '#dc2626', 'darkBg' => '#450a0a', 'darkText' => '#fca5a5'],
        'success' => ['bg' => '#f0fdf4', 'text' => '#16a34a', 'darkBg' => '#052e16', 'darkText' => '#86efac'],
        'warning' => ['bg' => '#fefce8', 'text' => '#ca8a04', 'darkBg' => '#422006', 'darkText' => '#fde047'],
        'info'    => ['bg' => '#eff6ff', 'text' => '#2563eb', 'darkBg' => '#172554', 'darkText' => '#93c5fd'],
        'primary' => ['bg' => '#eef2ff', 'text' => '#6366f1', 'darkBg' => '#1e1b4b', 'darkText' => '#a5b4fc'],
        'gray'    => ['bg' => '#f9fafb', 'text' => '#4b5563', 'darkBg' => '#1f2937', 'darkText' => '#d1d5db'],
    ];
@endphp

<div class="fi-ta" style="background-color: white; border-radius: 0.75rem; border: 1px solid #e5e7eb; overflow: hidden;">
    @if($heading)
        <div class="fi-ta-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;">
            <h3 style="font-size: 1rem; font-weight: 600; color: #111827; margin: 0;">{{ $heading }}</h3>
        </div>
    @endif

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    @foreach($columns as $column)
                        <th class="fi-ta-header-cell" style="padding: 0.75rem 1rem; font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $record)
                    <tr class="fi-ta-row" style="border-bottom: 1px solid #e5e7eb; {{ $striped && $index % 2 === 1 ? 'background-color: #f9fafb;' : '' }}">
                        @foreach($columns as $column)
                            <td class="fi-ta-cell" style="padding: 0.75rem 1rem; font-size: 0.875rem; color: #374151; white-space: nowrap;">
                                @php
                                    $value = $record[$column['name']] ?? '';
                                    $isBadge = $column['badge'] ?? false;
                                    $colorName = $column['color'] ?? null;

                                    if ($isBadge && $colorName === null && isset($column['getColor']) && $column['getColor'] !== null) {
                                        $colorName = ($column['getColor'])($value);
                                    }

                                    $colors = $isBadge && $colorName && isset($badgeColors[$colorName]) ? $badgeColors[$colorName] : null;
                                @endphp

                                @if($isBadge)
                                    @php
                                        $bg = $colors ? ($darkMode ? $colors['darkBg'] : $colors['bg']) : ($darkMode ? '#1f2937' : '#f3f4f6');
                                        $text = $colors ? ($darkMode ? $colors['darkText'] : $colors['text']) : ($darkMode ? '#d1d5db' : '#374151');
                                    @endphp
                                    <span style="display: inline-flex; align-items: center; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; background-color: {{ $bg }}; color: {{ $text }};">
                                        {{ $value }}
                                    </span>
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" style="padding: 2rem; text-align: center; font-size: 0.875rem; color: #9ca3af;">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
