<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#166534">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>@yield('title', 'Input Lapangan') — {{ config('app.name') }}</title>
    @include('layouts.partials.favicon')
    @vite(['resources/css/app.css', 'resources/css/lapangan.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="lapangan-body min-h-screen text-gray-900 antialiased">
    <header class="lapangan-header sticky top-0 z-20">
        <div class="mx-auto max-w-3xl px-4 py-3.5">
            <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('lapangan.index') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-lg backdrop-blur" aria-label="Menu utama">
                        🌳
                    </a>
                    <div class="min-w-0">
                        <p class="truncate text-[10px] font-bold uppercase tracking-widest text-green-100/90">{{ config('app.name') }} · Input Lapangan</p>
                        <h1 class="truncate text-base font-bold text-white">@yield('header', 'Operasional Pertamanan')</h1>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @if (auth()->user()?->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                           class="rounded-lg bg-white/15 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur hover:bg-white/25">
                            Admin
                        </a>
                    @endif
                    @if (auth()->check())
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg bg-red-500/90 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600">
                                Keluar
                            </button>
                        </form>
                    @elseif (\App\Support\LapanganGuestAccess::isUnlocked(request()))
                        <form action="{{ route('lapangan.lock') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg bg-white/15 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur hover:bg-white/25">
                                🔒 Kunci
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-5 pb-12">
        @if (session('success'))
            <div class="lapangan-alert-success mb-4 flex items-start gap-3 rounded-2xl border border-green-200 bg-green-50 px-4 py-3.5 text-sm text-green-900 shadow-sm">
                <span class="text-xl" aria-hidden="true">✅</span>
                <div>
                    <p class="font-bold">Berhasil!</p>
                    <p class="mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-900 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="text-xl" aria-hidden="true">⚠️</span>
                    <div>
                        <p class="font-bold">Perlu diperbaiki dulu</p>
                        <ul class="mt-2 list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-green-100 bg-white/60 py-4 text-center text-xs text-gray-500 backdrop-blur">
        <p class="font-medium text-green-800">Kerja aman · Taman asri · Batam hijau 🌱</p>
        <p class="mt-1">{{ config('app.name') }} · Input Lapangan</p>
    </footer>

    <script src="{{ asset('js/searchable-select.js') }}"></script>
    @stack('scripts')
</body>
</html>
