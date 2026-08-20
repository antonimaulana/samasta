@if ($canManageUsers ?? auth()->user()?->canManageUsers())
    {{ $slot }}
@endif
