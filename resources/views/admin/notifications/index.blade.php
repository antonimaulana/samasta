@extends('layouts.admin')

@section('title', 'Notifikasi')
@section('header', 'Notifikasi')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-600">Pengingat jadwal operasional pertamanan dan update operasional.</p>
        @if (auth()->user()->unreadNotifications()->exists())
            <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Tandai semua sudah dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <ul class="divide-y divide-gray-100">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = $notification->read_at === null;
                    $categoryIcon = match ($data['category'] ?? '') {
                        'hari_ini' => '📅',
                        'besok' => '⏰',
                        'terlambat' => '⚠️',
                        default => '🔔',
                    };
                @endphp
                <li class="{{ $isUnread ? 'bg-emerald-50/40' : 'bg-white' }}">
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start gap-3">
                                <span class="text-xl">{{ $categoryIcon }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900">
                                        {{ $data['title'] ?? 'Notifikasi' }}
                                        @if ($isUnread)
                                            <span class="ml-2 inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-800">Baru</span>
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm text-gray-600">{{ $data['message'] ?? '' }}</p>
                                    <p class="mt-2 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            @if (! empty($data['url']))
                                <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                                        Buka
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-5 py-12 text-center text-gray-500">
                    Belum ada notifikasi.
                </li>
            @endforelse
        </ul>

        @if ($notifications->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
