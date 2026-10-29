<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    @include('layouts.partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @include('layouts.partials.mobile-nav-styles')
    @include('layouts.partials.admin-sidebar-styles')
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">
    <div class="flex min-h-screen">
        <aside id="admin-desktop-sidebar" class="hidden w-60 shrink-0 border-r border-gray-800 bg-gray-900 text-white md:block">
            <div class="border-b border-gray-800 px-3 py-3.5">
                @include('layouts.partials.admin-sidebar-brand')
            </div>
            @include('layouts.partials.admin-nav')
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="border-b border-gray-200 bg-white px-4 py-4 sm:px-6">
                <div class="mx-auto flex max-w-[1400px] items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button"
                                id="admin-mobile-menu-toggle"
                                class="relative z-[51] inline-flex h-10 w-10 flex-shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 md:hidden"
                                aria-label="Buka menu admin"
                                aria-expanded="false"
                                aria-controls="admin-mobile-drawer">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <button type="button"
                                id="admin-sidebar-toggle"
                                class="hidden h-10 w-10 flex-shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                                aria-label="Tampilkan menu samping"
                                aria-expanded="true"
                                aria-controls="admin-desktop-sidebar">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h1 class="truncate text-base font-semibold sm:text-lg md:text-xl">@yield('header', 'Dashboard')</h1>
                    </div>

                    <div class="flex flex-shrink-0 flex-wrap items-center justify-end gap-2 sm:gap-3">
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

                        <span class="hidden text-sm text-gray-500 md:inline">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg border-2 border-green-300 bg-white px-4 py-2 text-sm font-bold text-green-600 transition hover:bg-lime-50">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="min-w-0 flex-1 p-4 sm:p-6">
                @if (session('viewer_redirect'))
                    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        Halaman tersebut tidak tersedia dalam mode ringkasan pimpinan. Anda diarahkan kembali ke dashboard.
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

    {{-- Admin mobile drawer --}}
    <div id="admin-mobile-backdrop" style="display:none" aria-hidden="true"></div>
    <aside id="admin-mobile-drawer" style="display:none" aria-hidden="true">
        <div class="flex items-center justify-between gap-2 border-b border-gray-800 px-4 py-3.5">
            <div class="min-w-0 flex-1">
                @include('layouts.partials.admin-sidebar-brand')
            </div>
            <button type="button" id="admin-mobile-menu-close" class="shrink-0 rounded-lg p-2 text-gray-400 hover:bg-gray-800" aria-label="Tutup menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @include('layouts.partials.admin-nav')
    </aside>

    @include('layouts.partials.mobile-nav-script')
    @include('layouts.partials.sidebar-accordion-script')
    <script>
        window.onPageReady = window.onPageReady || function (fn) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fn);
            } else {
                fn();
            }
        };

        window.onPageReady(function () {
            initSidebarAccordion(document);

            var sidebarStorageKey = 'simtaman-admin-sidebar-collapsed';
            var expandBtn = document.getElementById('admin-sidebar-toggle');

            function setSidebarCollapsed(collapsed) {
                document.body.classList.toggle('admin-sidebar-collapsed', collapsed);
                try {
                    localStorage.setItem(sidebarStorageKey, collapsed ? '1' : '0');
                } catch (e) {}
                if (expandBtn) {
                    expandBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                    expandBtn.setAttribute('aria-label', collapsed ? 'Tampilkan menu samping' : 'Sembunyikan menu samping');
                }
            }

            try {
                if (localStorage.getItem(sidebarStorageKey) === '1') {
                    setSidebarCollapsed(true);
                }
            } catch (e) {}

            expandBtn?.addEventListener('click', function () {
                setSidebarCollapsed(! document.body.classList.contains('admin-sidebar-collapsed'));
            });

            initMobileDrawer({
                toggleId: 'admin-mobile-menu-toggle',
                closeId: 'admin-mobile-menu-close',
                drawerId: 'admin-mobile-drawer',
                backdropId: 'admin-mobile-backdrop',
                bodyClass: 'admin-nav-open',
                side: 'left',
                width: 240,
                background: '#111827',
                color: '#ffffff',
                shadow: '10px 0 40px rgba(0,0,0,0.25)',
            });
        });
    </script>

    <script src="{{ asset('js/searchable-select.js') }}"></script>
    @stack('scripts')
</body>
</html>
