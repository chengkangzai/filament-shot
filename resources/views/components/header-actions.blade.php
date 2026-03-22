<div class="fi-header flex flex-col gap-y-2 p-4">
    @if(!empty($breadcrumbs))
    <nav class="fi-breadcrumbs">
        <ol class="flex flex-wrap items-center gap-x-1 text-sm">
            @foreach($breadcrumbs as $index => $crumb)
                <li class="flex items-center gap-x-1">
                    @if($index < count($breadcrumbs) - 1)
                        <span class="text-gray-500 dark:text-gray-400">{{ $crumb }}</span>
                        <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    @else
                        <span class="font-medium text-gray-950 dark:text-white">{{ $crumb }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
    @endif

    <div class="flex items-center justify-between gap-x-4">
        @if($pageTitle)
        <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
            {{ $pageTitle }}
        </h1>
        @endif

        @if($actionsHtml)
        <div class="flex items-center gap-x-3 shrink-0">
            {!! $actionsHtml !!}
        </div>
        @endif
    </div>
</div>
