<div class="fi-ta-ctn" style="flex-direction: column; overflow: hidden;">
    @if($heading)
        <div class="fi-ta-header">
            <p class="fi-ta-header-heading">{{ $heading }}</p>
        </div>
    @endif

    @if(!empty($bulkActions) && !empty($selectedRows))
        <div class="fi-ta-header-toolbar">
            <div class="fi-ta-actions fi-align-start fi-wrapped">
                @foreach($bulkActions as $bulkAction)
                    {!! $bulkAction->renderButton() !!}
                @endforeach
            </div>
        </div>

        <div class="fi-ta-selection-indicator">
            <span style="font-size: 0.875rem;">{{ count($selectedRows) }} {{ count($selectedRows) === 1 ? 'record' : 'records' }} selected.</span>
            <div class="fi-ta-selection-indicator-actions-ctn">
                <button type="button" class="fi-link fi-size-sm fi-color-primary">
                    <span class="fi-link-label">Select all {{ count($records) }}</span>
                </button>
                <button type="button" class="fi-link fi-size-sm fi-color-danger">
                    <span class="fi-link-label">Deselect all</span>
                </button>
            </div>
        </div>
    @endif

    <div class="fi-ta-content-ctn">
        <table class="fi-ta-table">
            <thead>
                <tr>
                    @if(!empty($bulkActions))
                        <th class="fi-ta-header-cell fi-ta-selection-cell">
                            <input type="checkbox" class="fi-ta-page-checkbox fi-checkbox-input" {{ count($selectedRows) === count($records) && count($records) > 0 ? 'checked' : '' }} />
                        </th>
                    @endif
                    @foreach($columns as $column)
                        <th class="fi-ta-header-cell">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                    @if(!empty($actions))
                        <th class="fi-ta-header-cell"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $record)
                    <tr class="fi-ta-row {{ $striped && $index % 2 === 1 ? 'fi-striped' : '' }}">
                        @if(!empty($bulkActions))
                            <td class="fi-ta-cell fi-ta-selection-cell">
                                <input type="checkbox" class="fi-ta-record-checkbox fi-checkbox-input" {{ in_array($index, $selectedRows) ? 'checked' : '' }} />
                            </td>
                        @endif
                        @foreach($columns as $column)
                            <td class="fi-ta-cell">
                                {!! $column->renderCell($record) !!}
                            </td>
                        @endforeach
                        @if(!empty($actions))
                            <td class="fi-ta-cell">
                                <div class="fi-ta-actions" style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;">
                                    @foreach($actions as $action)
                                        {!! $action->render() !!}
                                    @endforeach
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ (!empty($bulkActions) ? 1 : 0) + count($columns) + (!empty($actions) ? 1 : 0) }}">
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
