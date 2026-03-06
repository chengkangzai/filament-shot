<div class="fi-wi-stats-overview" style="display: grid; grid-template-columns: repeat({{ count($stats) }}, minmax(0, 1fr)); gap: 1.5rem;">
    @foreach($stats as $stat)
        <div class="fi-wi-stats-overview-stat">
            <div class="fi-wi-stats-overview-stat-content">
                <div class="fi-wi-stats-overview-stat-label-ctn">
                    <span class="fi-wi-stats-overview-stat-label">
                        {{ $stat['label'] }}
                    </span>
                </div>

                <div class="fi-wi-stats-overview-stat-value">
                    {{ $stat['value'] }}
                </div>

                @if($stat['description'])
                    <div class="fi-wi-stats-overview-stat-description {{ $stat['descriptionColorClasses'] ?? '' }}">
                        @if($stat['descriptionIcon'] && ($stat['descriptionIconPosition'] ?? 'before') === 'before')
                            <x-filament::icon
                                :icon="$stat['descriptionIcon']"
                                class="fi-wi-stats-overview-stat-description-icon"
                            />
                        @endif
                        <span>{{ $stat['description'] }}</span>
                        @if($stat['descriptionIcon'] && ($stat['descriptionIconPosition'] ?? 'before') === 'after')
                            <x-filament::icon
                                :icon="$stat['descriptionIcon']"
                                class="fi-wi-stats-overview-stat-description-icon"
                            />
                        @endif
                    </div>
                @endif
            </div>

            @if($stat['chart'])
                <div class="fi-wi-stats-overview-stat-chart" style="position: absolute; inset-inline: 0; bottom: 0; overflow: hidden; border-radius: 0 0 0.75rem 0.75rem;">
                    <svg viewBox="0 0 {{ (count($stat['chart']) - 1) * 10 }} 40" style="width: 100%; height: 1.5rem; display: block;" preserveAspectRatio="none">
                        @php
                            $max = max($stat['chart']);
                            $min = min($stat['chart']);
                            $range = $max - $min ?: 1;
                            $chartPoints = collect($stat['chart'])->map(function ($v, $i) use ($range, $min) {
                                $x = $i * 10;
                                $y = 40 - (($v - $min) / $range * 36);
                                return "$x,$y";
                            })->implode(' ');
                            $fillPoints = '0,40 ' . $chartPoints . ' ' . ((count($stat['chart']) - 1) * 10) . ',40';
                            $primaryColor = config('filament-shot.theme.primary_color', '#6366f1');
                        @endphp
                        <polygon
                            fill="{{ $primaryColor }}"
                            fill-opacity="0.1"
                            points="{{ $fillPoints }}"
                        />
                        <polyline
                            fill="none"
                            stroke="{{ $primaryColor }}"
                            stroke-width="2"
                            points="{{ $chartPoints }}"
                        />
                    </svg>
                </div>
            @endif
        </div>
    @endforeach
</div>
