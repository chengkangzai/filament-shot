<aside class="fi-sidebar fi-main-sidebar fi-sidebar-open" style="position: relative; display: flex; flex-direction: column; height: auto; width: 100%;">
    @if($heading)
        <div class="fi-sidebar-header-ctn" style="overflow-x: clip;">
            <header class="fi-sidebar-header" style="display: flex; height: 4rem; align-items: center; padding-left: 1.5rem; padding-right: 1.5rem;">
                <div class="fi-sidebar-header-logo-ctn" style="flex: 1;">
                    <span style="font-size: 1.125rem; font-weight: 700;">{{ $heading }}</span>
                </div>
            </header>
        </div>
    @endif

    <nav class="fi-sidebar-nav" style="display: flex; flex-grow: 1; flex-direction: column; gap: 1.5rem; padding: 1rem 1.5rem;">
        <ul class="fi-sidebar-nav-groups" style="display: flex; flex-direction: column; gap: 1rem; list-style: none; margin: 0; padding: 0;">
            @foreach($groups as $group)
                <li class="fi-sidebar-group {{ !empty($group['items']) && collect($group['items'])->contains('isActive', true) ? 'fi-active' : '' }}" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    @if($group['label'])
                        <div class="fi-sidebar-group-btn" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem;">
                            @if($group['icon'])
                                {!! $group['icon'] !!}
                            @endif
                            <span class="fi-sidebar-group-label">{{ $group['label'] }}</span>
                        </div>
                    @endif

                    @if(!$group['isCollapsed'])
                        <ul class="fi-sidebar-group-items" style="display: flex; flex-direction: column; gap: 0.25rem; list-style: none; margin: 0; padding: 0;">
                            @foreach($group['items'] as $itemIndex => $item)
                                <li class="fi-sidebar-item {{ $item['isActive'] ? 'fi-active' : '' }} {{ !empty($item['childItems']) && collect($item['childItems'])->contains('isActive', true) ? 'fi-sidebar-item-has-active-child-items' : '' }}">
                                    <a class="fi-sidebar-item-btn" style="display: flex; align-items: center; gap: 0.75rem; border-radius: 0.5rem; padding: 0.5rem;">
                                        @if($item['icon'])
                                            {!! $item['icon'] !!}
                                        @elseif($group['label'])
                                            <div class="fi-sidebar-item-grouped-border" style="display: flex; flex-direction: column; align-items: center; width: 1.5rem;">
                                                @if($itemIndex > 0)
                                                    <div class="fi-sidebar-item-grouped-border-part fi-sidebar-item-grouped-border-part-not-first" style="width: 2px; height: 0.5rem; background: var(--gray-300);"></div>
                                                @endif
                                                <div class="fi-sidebar-item-grouped-border-part" style="width: 6px; height: 6px; border-radius: 50%; background: {{ $item['isActive'] ? 'var(--primary-600)' : 'var(--gray-300)' }};"></div>
                                                @if($itemIndex < count($group['items']) - 1)
                                                    <div class="fi-sidebar-item-grouped-border-part fi-sidebar-item-grouped-border-part-not-last" style="width: 2px; flex-grow: 1; background: var(--gray-300);"></div>
                                                @endif
                                            </div>
                                        @endif

                                        <span class="fi-sidebar-item-label">{{ $item['label'] }}</span>

                                        @if($item['badge'])
                                            <span class="fi-sidebar-item-badge-ctn">
                                                <span class="fi-badge fi-size-sm {{ $item['badgeColor'] ? 'fi-color-' . $item['badgeColor'] : 'fi-color-primary' }}">
                                                    <span class="fi-badge-label">{{ $item['badge'] }}</span>
                                                </span>
                                            </span>
                                        @endif
                                    </a>

                                    @if($item['isActive'] && !empty($item['childItems']))
                                        <ul class="fi-sidebar-sub-group-items" style="display: flex; flex-direction: column; gap: 0.25rem; list-style: none; margin: 0; padding: 0; padding-left: 1rem;">
                                            @foreach($item['childItems'] as $childIndex => $child)
                                                <li class="fi-sidebar-item {{ $child['isActive'] ? 'fi-active' : '' }}">
                                                    <a class="fi-sidebar-item-btn" style="display: flex; align-items: center; gap: 0.75rem; border-radius: 0.5rem; padding: 0.5rem;">
                                                        @if($child['icon'])
                                                            {!! $child['icon'] !!}
                                                        @else
                                                            <div class="fi-sidebar-item-grouped-border" style="display: flex; flex-direction: column; align-items: center; width: 1.5rem;">
                                                                @if($childIndex > 0)
                                                                    <div class="fi-sidebar-item-grouped-border-part fi-sidebar-item-grouped-border-part-not-first" style="width: 2px; height: 0.5rem; background: var(--gray-300);"></div>
                                                                @endif
                                                                <div class="fi-sidebar-item-grouped-border-part" style="width: 6px; height: 6px; border-radius: 50%; background: {{ $child['isActive'] ? 'var(--primary-600)' : 'var(--gray-300)' }};"></div>
                                                                @if($childIndex < count($item['childItems']) - 1)
                                                                    <div class="fi-sidebar-item-grouped-border-part fi-sidebar-item-grouped-border-part-not-last" style="width: 2px; flex-grow: 1; background: var(--gray-300);"></div>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        <span class="fi-sidebar-item-label">{{ $child['label'] }}</span>

                                                        @if($child['badge'])
                                                            <span class="fi-sidebar-item-badge-ctn">
                                                                <span class="fi-badge fi-size-sm {{ $child['badgeColor'] ? 'fi-color-' . $child['badgeColor'] : 'fi-color-primary' }}">
                                                                    <span class="fi-badge-label">{{ $child['badge'] }}</span>
                                                                </span>
                                                            </span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>
</aside>
