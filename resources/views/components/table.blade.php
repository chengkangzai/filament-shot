<div class="fi-ta-ctn" style="flex-direction: column; overflow: hidden;">
    @if($heading)
        <div class="fi-ta-header">
            <p class="fi-ta-header-heading">{{ $heading }}</p>
        </div>
    @endif

    <div class="fi-ta-content-ctn">
        <table class="fi-ta-table">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th class="fi-ta-header-cell">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $record)
                    <tr class="fi-ta-row {{ $striped && $index % 2 === 1 ? 'fi-striped' : '' }}">
                        @foreach($columns as $column)
                            <td class="fi-ta-cell">
                                {!! $column->renderCell($record) !!}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}">
                            <div class="fi-ta-empty-state">
                                <div class="fi-ta-empty-state-content">
                                    <p class="fi-ta-empty-state-description">No records found.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
