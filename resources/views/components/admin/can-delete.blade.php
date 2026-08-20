@if ($canDelete ?? auth()->user()?->canDelete())
    {{ $slot }}
@endif
