@php
    $recentNotifications = $recentNotifications ?? collect();
    $unreadNotificationsCount = $unreadNotificationsCount ?? 0;
@endphp

<div class="relative" id="admin-notifications">
    <button type="button"
            id="admin-notifications-toggle"
            class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
            aria-label="Notifikasi"
            aria-expanded="false"
            aria-controls="admin-notifications-panel">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if ($unreadNotificationsCount > 0)
            <span class="absolute -right-1 -top-1 inline-flex min-w-[1.1rem] items-center justify-center rounded-full bg-red-500 px-1 py-0.5 text-[10px] font-bold text-white">
                {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
            </span>
        @endif
    </button>

    <div id="admin-notifications-panel"
         class="absolute right-0 z-50 mt-2 hidden w-[min(100vw-2rem,22rem)] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <p class="text-sm font-bold text-gray-900">Notifikasi</p>
            <a href="{{ route('admin.notifications.index') }}" class="text-xs font-semibold text-green-700 hover:underline">
                Lihat semua
            </a>
        </div>

        @if ($recentNotifications->isEmpty())
            <p class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada notifikasi baru.</p>
        @else
            <ul class="max-h-80 divide-y divide-gray-100 overflow-y-auto">
                @foreach ($recentNotifications as $notification)
                    @php $data = $notification->data; @endphp
                    <li>
                        <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="block w-full px-4 py-3 text-left hover:bg-gray-50">
                                <p class="text-sm font-semibold text-gray-900">{{ $data['title'] ?? 'Notifikasi' }}</p>
                                <p class="mt-1 line-clamp-2 text-xs text-gray-600">{{ $data['message'] ?? '' }}</p>
                                <p class="mt-1 text-[10px] text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<script>
    window.onPageReady(function () {
        const wrapper = document.getElementById('admin-notifications');
        const toggle = document.getElementById('admin-notifications-toggle');
        const panel = document.getElementById('admin-notifications-panel');

        if (!wrapper || !toggle || !panel) return;

        function setOpen(open) {
            panel.classList.toggle('hidden', !open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggle.addEventListener('click', function (event) {
            event.stopPropagation();
            setOpen(panel.classList.contains('hidden'));
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });
    });
</script>
