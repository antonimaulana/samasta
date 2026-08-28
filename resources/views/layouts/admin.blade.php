<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    @include('layouts.partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @unless ($isViewer ?? false)
        @include('layouts.partials.mobile-nav-styles')
        <style>
            .sidebar-menu-group .sidebar-submenu {
                overflow: hidden;
                max-height: 0;
                opacity: 0;
                pointer-events: none;
                transition: max-height 0.25s ease, opacity 0.2s ease;
            }
            .sidebar-menu-group:hover .sidebar-submenu,
            .sidebar-menu-group.is-open .sidebar-submenu,
            .admin-nav-mobile .sidebar-submenu {
                max-height: 16rem;
                opacity: 1;
                pointer-events: auto;
            }
            .sidebar-menu-group:hover .sidebar-chevron,
            .sidebar-menu-group.is-open .sidebar-chevron {
                transform: rotate(180deg);
            }
            .sidebar-chevron {
                transition: transform 0.2s ease;
            }
        </style>
    @endunless
</head>
<body class="min-h-screen {{ ($isViewer ?? false) ? 'bg-gradient-to-b from-emerald-50/80 via-gray-50 to-gray-100' : 'bg-gray-100' }} text-gray-900">
    <div class="flex min-h-screen">
        @unless ($isViewer ?? false)
            <aside class="hidden w-64 flex-shrink-0 bg-gray-900 text-white md:block">
                <div class="border-b border-gray-800 px-6 py-5">
                    <div class="flex items-center gap-3">
                        @include('layouts.partials.app-logo', ['size' => 'sm'])
                        <div>
                            <p class="text-lg font-semibold">Admin {{ config('app.name') }}</p>
                            <p class="text-xs text-gray-400">{{ config('app.full_name') }}</p>
                        </div>
                    </div>
                </div>
                @include('layouts.partials.admin-nav')
            </aside>
        @endunless

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="{{ ($isViewer ?? false) ? 'border-b border-emerald-100/80 bg-white/90 backdrop-blur-md shadow-sm' : 'border-b border-gray-200 bg-white' }} px-4 py-4 sm:px-6">
                <div class="mx-auto flex max-w-[1400px] items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        @if ($isViewer ?? false)
                            <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3">
                                @include('layouts.partials.app-logo')
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-900">{{ config('app.name') }}</p>
                                    <p class="truncate text-xs text-gray-500">Ringkasan Pimpinan</p>
                                </div>
                            </a>
                        @else
                            <button type="button"
                                    id="admin-mobile-menu-toggle"
                                    class="relative z-[51] h-10 w-10 flex-shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                                    aria-label="Buka menu admin"
                                    aria-expanded="false"
                                    aria-controls="admin-mobile-drawer">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                            <h1 class="truncate text-lg font-semibold sm:text-xl">@yield('header', 'Dashboard')</h1>
                        @endif
                    </div>

                    <div class="flex flex-shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-3">
                        @unless ($isViewer ?? false)
                            @if (($operationalAlertsTotal ?? 0) > 0)
                                <a href="{{ route('admin.dashboard') }}#peringatan-operasional"
                                   class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-800 ring-1 ring-amber-200 hover:bg-amber-100 sm:px-3 sm:text-xs">
                                    ⚠️ <span class="hidden sm:inline">{{ $operationalAlertsTotal }} peringatan</span><span class="sm:hidden">{{ $operationalAlertsTotal }}</span>
                                </a>
                            @endif
                            @if (($masukanBaruCount ?? 0) > 0)
                                <a href="{{ route('admin.aduan-masyarakats.index', ['status' => 'Baru']) }}"
                                   class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold text-red-700 ring-1 ring-red-200 hover:bg-red-100 sm:px-3 sm:text-xs"
                                   title="Aduan baru: {{ $aduanBaruCount ?? 0 }}, Survey baru: {{ $surveyBaruCount ?? 0 }}">
                                    📥 <span class="hidden sm:inline">{{ $masukanBaruCount }} masukan baru</span><span class="sm:hidden">{{ $masukanBaruCount }}</span>
                                </a>
                            @endif
                            @include('layouts.partials.admin-notifications')
                        @endunless

                        <span class="hidden text-sm text-gray-500 md:inline">{{ auth()->user()->name }}</span>
                        <span class="rounded-full {{ ($isViewer ?? false) ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }} px-2 py-0.5 text-[10px] font-bold">{{ $authUserRole ?? 'User' }}</span>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:underline">Keluar</button>
                        </form>
                    </div>
                </div>

                @if (($isViewer ?? false) && ! request()->routeIs('admin.dashboard'))
                    <div class="mx-auto mt-3 flex max-w-[1400px] flex-wrap items-center gap-3 border-t border-emerald-50 pt-3">
                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-flex items-center gap-1 text-sm font-semibold text-green-700 hover:underline">
                            ← Kembali ke Dashboard
                        </a>
                        <span class="text-gray-300">|</span>
                        <h1 class="text-sm font-semibold text-gray-700">@yield('header', 'Dashboard')</h1>
                    </div>
                @endif
            </header>

            <main class="min-w-0 flex-1 p-4 sm:p-6 {{ ($isViewer ?? false) ? 'mx-auto w-full max-w-[1400px]' : '' }}">
                @if (($isViewer ?? false) && request()->routeIs('admin.dashboard'))
                    {{-- Banner hanya di halaman detail, dashboard sudah punya hero sendiri --}}
                @elseif (isset($canWrite) && ! $canWrite)
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-green-50 px-4 py-3 text-sm text-emerald-900">
                        <strong>Mode Ringkasan Pimpinan</strong> — Tampilan read-only untuk monitoring capaian dinas.
                    </div>
                @endif

                @if (session('viewer_redirect'))
                    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        Halaman tersebut tidak tersedia dalam <strong>mode ringkasan pimpinan</strong>. Anda diarahkan kembali ke dashboard.
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                        <ul class="list-inside list-disc text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @unless ($isViewer ?? false)
        {{-- Admin mobile drawer --}}
        <div id="admin-mobile-backdrop" style="display:none" aria-hidden="true"></div>
        <aside id="admin-mobile-drawer" style="display:none" aria-hidden="true">
            <div class="flex items-center justify-between border-b border-gray-800 px-5 py-4">
                <div class="flex items-center gap-3">
                    @include('layouts.partials.app-logo', ['size' => 'sm'])
                    <div>
                        <p class="font-semibold">Admin {{ config('app.name') }}</p>
                        <p class="text-xs text-gray-400">Panel Manajemen Hijau</p>
                    </div>
                </div>
                <button type="button" id="admin-mobile-menu-close" class="rounded-lg p-2 text-gray-400 hover:bg-gray-800" aria-label="Tutup menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @include('layouts.partials.admin-nav', ['mobileExpanded' => true])
        </aside>

        @include('layouts.partials.mobile-nav-script')
        <script>
            window.onPageReady = window.onPageReady || function (fn) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', fn);
                } else {
                    fn();
                }
            };

            window.onPageReady(function () {
                initMobileDrawer({
                    toggleId: 'admin-mobile-menu-toggle',
                    closeId: 'admin-mobile-menu-close',
                    drawerId: 'admin-mobile-drawer',
                    backdropId: 'admin-mobile-backdrop',
                    bodyClass: 'admin-nav-open',
                    side: 'left',
                    width: 280,
                    background: '#111827',
                    color: '#ffffff',
                    shadow: '10px 0 40px rgba(0,0,0,0.25)',
                });
            });
        </script>
    @endunless

    <script src="{{ asset('js/searchable-select.js') }}"></script>
    @stack('scripts')
</body>
</html>
