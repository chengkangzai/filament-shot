{{-- Static modal wrapper for screenshots. Uses relative positioning instead of
     fixed so Browsershot can measure and capture the content properly.
     Renders actual Filament button components for correct color styling. --}}
<div class="fi-modal fi-modal-open fi-absolute-positioning-context" aria-modal="true" role="dialog" style="position: relative; display: flex; align-items: center; justify-content: center; padding: 0.25rem; border-radius: 0.75rem; background-color: rgba(0, 0, 0, 0.4);">
    <div class="fi-modal-window-ctn" style="position: relative; width: 100%;">
        <div class="fi-modal-window fi-modal-window-has-close-btn fi-modal-window-has-content fi-modal-window-has-footer fi-align-start fi-width-lg" style="position: relative;">
            <div class="fi-modal-header">
                <x-filament::icon-button
                    color="gray"
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedXMark"
                    icon-size="lg"
                    label="Close"
                    tabindex="-1"
                    class="fi-modal-close-btn"
                />

                <div>
                    <h2 class="fi-modal-heading">{{ $heading }}</h2>

                    @if($description)
                        <p class="fi-modal-description">{{ $description }}</p>
                    @endif
                </div>
            </div>

            <div class="fi-modal-content">
                {!! $content !!}
            </div>

            <div class="fi-modal-footer fi-align-start">
                <div class="fi-modal-footer-actions">
                    <x-filament::button color="gray">
                        {{ $cancelLabel }}
                    </x-filament::button>
                    <x-filament::button color="primary">
                        {{ $submitLabel }}
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
</div>
