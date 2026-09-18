<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name')) — Profil Pertamanan Kota Batam</title>
    @include('layouts.partials.meta-seo')
    @include('layouts.partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @include('layouts.partials.mobile-nav-styles')
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen @yield('body_class', 'bg-green-50') text-gray-900 antialiased">
    <header class="sticky top-0 z-50 border-b border-green-200/60 bg-white/90 backdrop-blur-lg shadow-sm shadow-green-100/50">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3">
                @include('layouts.partials.app-logo', ['class' => 'transition group-hover:scale-105'])
                <div class="min-w-0">
                    <span class="block truncate text-base font-bold leading-tight text-green-700">{{ config('app.name') }}</span>
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-lime-500">{{ config('app.full_name') }}</span>
                </div>
            </a>

            <nav class="hidden items-center gap-1 md:flex lg:gap-2">
                @include('layouts.partials.public-nav', ['variant' => 'desktop'])
            </nav>

            <button type="button"
                    id="public-mobile-menu-toggle"
                    class="relative z-[51] h-10 w-10 flex-shrink-0 touch-manipulation items-center justify-center rounded-xl border border-green-200 bg-white text-green-700 shadow-sm"
                    aria-label="Buka menu"
                    aria-expanded="false"
                    aria-controls="public-mobile-drawer">
                <svg class="h-5 w-5" id="public-menu-icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg class="h-5 w-5" id="public-menu-icon-close" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </header>

    @yield('hero')

    <main class="@yield('main_class', 'mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8')">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800">
                {{ session('info') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-green-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-8 sm:flex-row sm:px-6 lg:px-8">
            <div class="text-center sm:text-left">
                <p class="font-bold text-green-700">{{ config('app.name') }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ config('app.full_name') }} · Kota Batam</p>
            </div>
            <p class="text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </footer>

    {{-- Mobile drawer --}}
    <div id="public-mobile-backdrop" style="display:none" aria-hidden="true"></div>
    <aside id="public-mobile-drawer" style="display:none" aria-hidden="true">
        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #dcfce7;padding:1rem;">
            <p style="margin:0;font-weight:700;color:#166534;">Menu</p>
            <button type="button" id="public-mobile-menu-close" style="border:0;background:transparent;padding:0.5rem;color:#6b7280;" aria-label="Tutup menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @include('layouts.partials.public-nav', ['variant' => 'mobile'])
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
    </script>
    <script>
        onPageReady(function () {
            initMobileDrawer({
                toggleId: 'public-mobile-menu-toggle',
                closeId: 'public-mobile-menu-close',
                drawerId: 'public-mobile-drawer',
                backdropId: 'public-mobile-backdrop',
                iconOpenId: 'public-menu-icon-open',
                iconCloseId: 'public-menu-icon-close',
                bodyClass: 'mobile-nav-open',
                side: 'right',
                width: 320,
                background: '#ffffff',
                shadow: '-10px 0 40px rgba(0,0,0,0.12)',
            });

            const menu = document.getElementById('jelajahi-menu');
            const jelajahiToggle = document.getElementById('jelajahi-toggle');
            const dropdown = document.getElementById('jelajahi-dropdown');
            const chevron = document.getElementById('jelajahi-chevron');

            const desktopQuery = window.matchMedia('(min-width: 768px)');

            function initNavDropdown(menuEl, toggleEl, dropdownEl, chevronEl) {
                if (!toggleEl || !dropdownEl) {
                    return;
                }

                let closeTimer = null;

                function setOpen(isOpen) {
                    dropdownEl.classList.toggle('hidden', !isOpen);
                    toggleEl.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    chevronEl?.classList.toggle('rotate-180', isOpen);
                }

                toggleEl.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    setOpen(dropdownEl.classList.contains('hidden'));
                });

                document.addEventListener('click', function (e) {
                    if (menuEl && !menuEl.contains(e.target)) {
                        setOpen(false);
                    }
                });

                menuEl?.addEventListener('mouseenter', function () {
                    if (desktopQuery.matches) {
                        clearTimeout(closeTimer);
                        setOpen(true);
                    }
                });

                menuEl?.addEventListener('mouseleave', function () {
                    if (desktopQuery.matches) {
                        closeTimer = setTimeout(function () { setOpen(false); }, 350);
                    }
                });
            }

            initNavDropdown(
                menu,
                jelajahiToggle,
                dropdown,
                chevron,
            );
        });
    </script>
    @stack('scripts')
</body>
</html>
