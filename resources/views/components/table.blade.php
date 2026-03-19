<div class="fi-ta-ctn {{ $heading ? 'fi-ta-ctn-with-header' : '' }}" style="flex-direction: column; overflow: hidden;">
    @if($heading)
        <div class="fi-ta-header-ctn">
            <div class="fi-ta-header">
                <div>
                    <h3 class="fi-ta-header-heading">{{ $heading }}</h3>
                </div>
            </div>
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
                <button type="button" class="fi-link fi-size-sm {{ $linkPrimaryClasses }}">
                    <span class="fi-link-label">Select all {{ count($records) }}</span>
                </button>
                <button type="button" class="fi-link fi-size-sm {{ $linkDangerClasses }}">
                    <span class="fi-link-label">Deselect all</span>
                </button>
            </div>
        </div>
    @endif

    <div class="fi-ta-content-ctn">
        <div class="fi-ta-content">
            <table class="fi-ta-table">
                <thead>
                    <tr>
                        @if($reorderable)
                            <th class="fi-ta-header-cell"></th>
                        @endif
                        @if(!empty($bulkActions))
                            <th class="fi-ta-cell fi-ta-selection-cell">
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
                            @if($reorderable)
                                <td class="fi-ta-cell">
                                    <button class="fi-ta-reorder-handle fi-icon-btn" type="button">
                                        <svg class="fi-icon fi-size-md" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M2 6.75A.75.75 0 0 1 2.75 6h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 6.75Zm0 6.5a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </td>
                            @endif
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
                                    <div class="fi-ta-actions fi-align-end">
                                        @foreach($actions as $action)
                                            {!! $action->render() !!}
                                        @endforeach
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($reorderable ? 1 : 0) + (!empty($bulkActions) ? 1 : 0) + count($columns) + (!empty($actions) ? 1 : 0) }}">
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
</div>
