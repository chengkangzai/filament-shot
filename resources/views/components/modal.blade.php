{{-- Renders content inside Filament's actual modal component.
     CSS overrides in base.blade.php force visibility and static positioning
     since Alpine.js doesn't run in screenshots. --}}
<x-filament::modal
    id="shot-modal"
    :close-by-clicking-away="false"
    :close-by-escaping="false"
    width="lg"
    :heading="$heading"
    :description="$description"
    :icon="$icon ?? null"
    :icon-color="$iconColor ?? 'primary'"
>
    @if($content)
        {!! $content !!}
    @endif

    <x-slot name="footer">
        <x-filament::button :color="$color ?? 'primary'">
            {{ $submitLabel }}
        </x-filament::button>
        <x-filament::button color="gray">
            {{ $cancelLabel }}
        </x-filament::button>
    </x-slot>
</x-filament::modal>
