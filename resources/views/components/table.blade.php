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
                                @php
                                    $value = $record[$column['name']] ?? '';
                                    $isBadge = $column['badge'] ?? false;
                                @endphp

                                @if($isBadge)
                                    @php
                                        $colorName = $column['color'] ?? null;

                                        if ($colorName === null && isset($column['getColor'])) {
                                            $colorName = ($column['getColor'])($value);
                                        }

                                        $badgeClasses = \CCK\FilamentShot\Renderers\TableRenderer::resolveBadgeClasses($colorName);
                                    @endphp
                                    <div class="fi-ta-text fi-ta-text-has-badges fi-ta-text-item">
                                        <span class="fi-badge fi-size-sm {{ $badgeClasses }}">
                                            {{ $value }}
                                        </span>
                                    </div>
                                @else
                                    <div class="fi-ta-text">
                                        <span class="fi-ta-text-item fi-size-sm">{{ $value }}</span>
                                    </div>
                                @endif
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
