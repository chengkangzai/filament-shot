<header class="fi-header{{ !empty($breadcrumbs) ? ' fi-header-has-breadcrumbs' : '' }}">
    <div>
        @if(!empty($breadcrumbs))
        <nav class="fi-breadcrumbs">
            <ol class="fi-breadcrumbs-list">
                @foreach($breadcrumbs as $index => $crumb)
                    <li class="fi-breadcrumbs-item">
                        @if(!$loop->first)
                            <svg class="fi-icon fi-ltr h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        @endif
                        <span class="fi-breadcrumbs-item-label">{{ $crumb }}</span>
                    </li>
                @endforeach
            </ol>
        </nav>
        @endif

        @if($pageTitle)
        <h1 class="fi-header-heading">
            {{ $pageTitle }}
        </h1>
        @endif
    </div>

    @if($actionsHtml)
    <div class="fi-header-actions-ctn">
        {!! $actionsHtml !!}
    </div>
    @endif
</header>
