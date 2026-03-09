<div class="fi-fo-component-ctn" style="display: flex; flex-direction: column; gap: 1.5rem;">
    @foreach($fields as $component)
        @include('filament-shot::components.form-component', ['component' => $component])
    @endforeach
</div>
