<aside class="fi-sidebar fi-main-sidebar fi-sidebar-open" style="position: relative; display: flex; flex-direction: column; height: auto; width: 100%;">
    @if($heading)
        <div class="fi-sidebar-header-ctn">
            <header class="fi-sidebar-header" style="justify-content: flex-start; padding-left: 1.5rem; padding-right: 1.5rem;">
                <div class="fi-sidebar-header-logo-ctn">
                    <span style="font-size: 1.125rem; font-weight: 700;">{{ $heading }}</span>
                </div>
            </header>
        </div>
    @endif

    <nav class="fi-sidebar-nav">
        <ul class="fi-sidebar-nav-groups">
            @foreach($groups as $group)
                <li class="fi-sidebar-group fi-collapsible {{ !empty($group['items']) && collect($group['items'])->contains('isActive', true) ? 'fi-active' : '' }}">
                    @if($group['label'])
                        <div class="fi-sidebar-group-btn">
                            @if($group['icon'])
                                {!! $group['icon'] !!}
                            @endif
                            <span class="fi-sidebar-group-label">{{ $group['label'] }}</span>
                            <button type="button" class="fi-icon-btn fi-size-sm fi-color-gray fi-sidebar-group-collapse-btn">
                                <svg class="fi-icon fi-size-sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if(!$group['isCollapsed'])
                        <ul class="fi-sidebar-group-items">
                            @foreach($group['items'] as $itemIndex => $item)
                                <li class="fi-sidebar-item fi-sidebar-item-has-url {{ $item['isActive'] ? 'fi-active' : '' }} {{ !empty($item['childItems']) && collect($item['childItems'])->contains('isActive', true) ? 'fi-sidebar-item-has-active-child-items' : '' }}">
                                    <a class="fi-sidebar-item-btn">
                                        @if($item['icon'])
                                            {!! $item['icon'] !!}
                                        @elseif($group['label'])
                                            <div class="fi-sidebar-item-grouped-border">
                                                @if($itemIndex > 0)
                                                    <div class="fi-sidebar-item-grouped-border-part-not-first"></div>
                                                @endif
                                                <div class="fi-sidebar-item-grouped-border-part"></div>
                                                @if($itemIndex < count($group['items']) - 1)
                                                    <div class="fi-sidebar-item-grouped-border-part-not-last"></div>
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
                                        <ul class="fi-sidebar-sub-group-items">
                                            @foreach($item['childItems'] as $childIndex => $child)
                                                <li class="fi-sidebar-item fi-sidebar-item-has-url {{ $child['isActive'] ? 'fi-active' : '' }}">
                                                    <a class="fi-sidebar-item-btn">
                                                        @if($child['icon'])
                                                            {!! $child['icon'] !!}
                                                        @else
                                                            <div class="fi-sidebar-item-grouped-border">
                                                                @if($childIndex > 0)
                                                                    <div class="fi-sidebar-item-grouped-border-part-not-first"></div>
                                                                @endif
                                                                <div class="fi-sidebar-item-grouped-border-part"></div>
                                                                @if($childIndex < count($item['childItems']) - 1)
                                                                    <div class="fi-sidebar-item-grouped-border-part-not-last"></div>
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
