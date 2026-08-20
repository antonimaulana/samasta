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
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        body.mobile-nav-open { overflow: hidden; }
        #public-mobile-drawer.is-open { transform: translateX(0); }
        #public-mobile-backdrop.is-open { opacity: 1; pointer-events: auto; }
    </style>
</head>
<body class="min-h-screen @yield('body_class', 'bg-green-50') text-gray-900 antialiased">
    <header class="sticky top-0 z-50 border-b border-green-200/60 bg-white/90 backdrop-blur-lg shadow-sm shadow-green-100/50">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3">
                @include('layouts.partials.app-logo', ['class' => 'transition group-hover:scale-105'])
                <div class="min-w-0">
                    <span class="block truncate text-base font-bold leading-tight text-green-700">{{ config('app.name') }}</span>
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-lime-500">Profil Pertamanan</span>
                </div>
            </a>

            <nav class="hidden items-center gap-1 md:flex lg:gap-2">
                @include('layouts.partials.public-nav', ['variant' => 'desktop'])
            </nav>

            <button type="button"
                    id="public-mobile-menu-toggle"
                    class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-green-200 bg-white text-green-700 shadow-sm md:hidden"
                    aria-label="Buka menu"
                    aria-expanded="false"
                    aria-controls="public-mobile-drawer">
                <svg class="h-5 w-5" id="public-menu-icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg class="hidden h-5 w-5" id="public-menu-icon-close" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </header>

    {{-- Mobile drawer --}}
    <div id="public-mobile-backdrop"
         class="fixed inset-0 z-[60] bg-gray-900/40 opacity-0 pointer-events-none transition-opacity duration-300 md:hidden"
         aria-hidden="true"></div>
    <aside id="public-mobile-drawer"
           class="fixed inset-y-0 right-0 z-[70] w-[min(100vw-3rem,320px)] translate-x-full transform overflow-y-auto border-l border-green-100 bg-white shadow-2xl transition-transform duration-300 md:hidden"
           aria-hidden="true">
        <div class="flex items-center justify-between border-b border-green-100 px-4 py-4">
            <p class="font-bold text-green-800">Menu</p>
            <button type="button" id="public-mobile-menu-close" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100" aria-label="Tutup menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @include('layouts.partials.public-nav', ['variant' => 'mobile'])
    </aside>

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
                <p class="mt-1 text-sm text-gray-500">Portal Profil Pertamanan Kota Batam</p>
            </div>
            <p class="text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </footer>

    <script>
        window.onPageReady = function (fn) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fn);
            } else {
                fn();
            }
        };
    </script>
    <script>
        onPageReady(function () {
            const toggle = document.getElementById('public-mobile-menu-toggle');
            const closeBtn = document.getElementById('public-mobile-menu-close');
            const drawer = document.getElementById('public-mobile-drawer');
            const backdrop = document.getElementById('public-mobile-backdrop');
            const iconOpen = document.getElementById('public-menu-icon-open');
            const iconClose = document.getElementById('public-menu-icon-close');

            function setMobileNav(open) {
                if (!drawer || !backdrop) return;
                drawer.classList.toggle('is-open', open);
                backdrop.classList.toggle('is-open', open);
                document.body.classList.toggle('mobile-nav-open', open);
                toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
                drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
                backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
                iconOpen?.classList.toggle('hidden', open);
                iconClose?.classList.toggle('hidden', !open);
            }

            toggle?.addEventListener('click', function () {
                setMobileNav(!drawer.classList.contains('is-open'));
            });
            closeBtn?.addEventListener('click', function () { setMobileNav(false); });
            backdrop?.addEventListener('click', function () { setMobileNav(false); });
            drawer?.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () { setMobileNav(false); });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setMobileNav(false);
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
                    if (desktopQuery.matches) return;
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
