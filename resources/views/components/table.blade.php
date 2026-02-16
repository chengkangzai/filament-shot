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
                                {{ $record[$column['name']] ?? '' }}
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
