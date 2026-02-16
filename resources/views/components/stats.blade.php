<div class="fi-wi-stats-overview" style="display: grid; grid-template-columns: repeat({{ count($stats) }}, minmax(0, 1fr)); gap: 1.5rem;">
    @foreach($stats as $stat)
        <div class="fi-wi-stats-overview-stat" style="background-color: white; border-radius: 0.75rem; border: 1px solid #e5e7eb; padding: 1.5rem; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                @if($stat['icon'])
                    <span style="color: {{ $stat['color'] ?? '#6b7280' }};">
                        <!-- icon: {{ $stat['icon'] }} -->
                    </span>
                @endif
                <span class="fi-wi-stats-overview-stat-label" style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">
                    {{ $stat['label'] }}
                </span>
            </div>

            <div class="fi-wi-stats-overview-stat-value" style="font-size: 1.875rem; font-weight: 700; color: #111827; margin-top: 0.25rem;">
                {{ $stat['value'] }}
            </div>

            @if($stat['description'])
                <div class="fi-wi-stats-overview-stat-description" style="display: flex; align-items: center; gap: 0.25rem; margin-top: 0.25rem; font-size: 0.875rem; color: {{ $stat['descriptionColor'] ?? '#6b7280' }};">
                    @if($stat['descriptionIcon'] && ($stat['descriptionIconPosition'] ?? 'before') === 'before')
                        <span><!-- icon: {{ $stat['descriptionIcon'] }} --></span>
                    @endif
                    <span>{{ $stat['description'] }}</span>
                    @if($stat['descriptionIcon'] && ($stat['descriptionIconPosition'] ?? 'before') === 'after')
                        <span><!-- icon: {{ $stat['descriptionIcon'] }} --></span>
                    @endif
                </div>
            @endif

            @if($stat['chart'])
                <div class="fi-wi-stats-overview-stat-chart" style="margin-top: 0.75rem; height: 2.5rem;">
                    <svg viewBox="0 0 {{ count($stat['chart']) * 10 }} 40" style="width: 100%; height: 100%;" preserveAspectRatio="none">
                        @php
                            $max = max($stat['chart']);
                            $min = min($stat['chart']);
                            $range = $max - $min ?: 1;
                            $points = collect($stat['chart'])->map(function ($v, $i) use ($range, $min, $stat) {
                                $x = $i * 10;
                                $y = 40 - (($v - $min) / $range * 36);
                                return "$x,$y";
                            })->implode(' ');
                        @endphp
                        <polyline
                            fill="none"
                            stroke="{{ $stat['color'] ?? '#6366f1' }}"
                            stroke-width="2"
                            points="{{ $points }}"
                        />
                    </svg>
                </div>
            @endif
        </div>
    @endforeach
</div>
