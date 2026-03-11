{{-- Renders a Filament notification toast using Notification::toEmbeddedHtml().
     CSS overrides in base.blade.php force visibility since Alpine.js
     doesn't run in screenshots. --}}
<div style="display: flex; justify-content: center;">
    {!! $notificationHtml !!}
</div>
