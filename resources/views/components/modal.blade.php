{{-- Static modal wrapper for screenshots. Uses relative positioning instead of
     fixed so Browsershot can measure and capture the content properly. --}}
<div class="fi-modal fi-modal-open fi-absolute-positioning-context" aria-modal="true" role="dialog" style="position: relative; display: flex; align-items: center; justify-content: center; padding: 2rem; border-radius: 0.75rem; background-color: rgba(0, 0, 0, 0.4);">
    <div class="fi-modal-window-ctn" style="position: relative; width: 100%;">
        <div class="fi-modal-window fi-modal-window-has-close-btn fi-modal-window-has-content fi-modal-window-has-footer fi-align-start fi-width-lg" style="position: relative;">
            <div class="fi-modal-header">
                <button type="button" class="fi-modal-close-btn fi-icon-btn fi-icon-btn-size-lg fi-color fi-color-gray">
                    <svg class="fi-icon fi-icon-size-lg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

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
                    <button type="button" class="fi-btn fi-btn-size-md fi-color fi-color-gray">
                        <span class="fi-btn-label">{{ $cancelLabel }}</span>
                    </button>
                    <button type="submit" class="fi-btn fi-btn-size-md fi-color fi-color-primary">
                        <span class="fi-btn-label">{{ $submitLabel }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
