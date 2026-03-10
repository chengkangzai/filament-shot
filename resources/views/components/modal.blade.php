{{-- Renders form content inside Filament's actual modal component.
     CSS overrides in base.blade.php force visibility and static positioning
     since Alpine.js doesn't run in screenshots. --}}
<x-filament::modal
    id="shot-modal"
    :close-by-clicking-away="false"
    :close-by-escaping="false"
    width="lg"
    :heading="$heading"
    :description="$description"
>
    {!! $content !!}

    <x-slot name="footer">
        <x-filament::button color="primary">
            {{ $submitLabel }}
        </x-filament::button>
        <x-filament::button color="gray">
            {{ $cancelLabel }}
        </x-filament::button>
    </x-slot>
</x-filament::modal>
