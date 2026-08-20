@if ($canWrite ?? auth()->user()?->canWrite())
    {{ $slot }}
@endif
