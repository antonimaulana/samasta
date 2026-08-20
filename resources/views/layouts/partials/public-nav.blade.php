@php

    $jelajahiActive = request()->routeIs('tamans.*', 'rth.*');

    $masukanActive = request()->routeIs('masukan.*', 'aduan.*', 'survey.*');

    $variant = $variant ?? 'desktop';

@endphp



@if ($variant === 'desktop')

    <a href="{{ route('home') }}"

       class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-lime-100 hover:text-green-700 {{ request()->routeIs('home') ? 'bg-lime-100 text-green-700' : '' }}">

        Beranda

    </a>



    <div class="relative" id="jelajahi-menu">

        <button type="button"

                id="jelajahi-toggle"

                class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-semibold transition hover:bg-lime-100 hover:text-green-700 {{ $jelajahiActive ? 'bg-lime-100 text-green-700' : 'text-gray-600' }}"

                aria-expanded="false"

                aria-haspopup="true">

            Jelajahi

            <svg class="h-4 w-4 transition-transform duration-200" id="jelajahi-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>

            </svg>

        </button>



        <div id="jelajahi-dropdown"

             class="absolute right-0 top-full z-50 hidden min-w-[200px] pt-2 lg:right-auto">

            <div class="rounded-xl border border-green-200 bg-white py-1 shadow-lg shadow-green-200/50">

                @include('layouts.partials.public-nav-jelajahi-links', ['linkClass' => 'block px-4 py-2.5 text-sm font-semibold transition hover:bg-lime-50'])

            </div>

        </div>

    </div>



    <a href="{{ route('masukan.index') }}"

       class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-lime-100 hover:text-green-700 {{ $masukanActive ? 'bg-lime-100 text-green-700' : '' }}">

        Masukan

    </a>



    <a href="{{ route('ensiklopedia.index') }}"

       class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-lime-100 hover:text-green-700 {{ request()->routeIs('ensiklopedia.*') ? 'bg-lime-100 text-green-700' : '' }}">

        Ensiklopedia

    </a>



    @auth

        <a href="{{ route('admin.dashboard') }}"

           class="ml-1 rounded-lg bg-gradient-to-r from-lime-500 to-green-500 px-4 py-2 text-sm font-bold text-white shadow-md shadow-green-400/40 transition hover:from-lime-400 hover:to-green-400">

            Admin

        </a>

    @else

        <a href="{{ route('login') }}"

           class="ml-1 rounded-lg border-2 border-green-300 bg-white px-4 py-2 text-sm font-bold text-green-600 transition hover:bg-lime-50">

            Login

        </a>

    @endauth

@else

    <nav class="flex flex-col gap-1 p-4">

        <a href="{{ route('home') }}"

           class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('home') ? 'bg-lime-100 text-green-700' : 'text-gray-700 hover:bg-lime-50' }}">

            Beranda

        </a>



        <p class="mt-3 px-4 text-xs font-bold uppercase tracking-wider text-gray-400">Jelajahi</p>

        @include('layouts.partials.public-nav-jelajahi-links', ['linkClass' => 'block rounded-xl px-4 py-2.5 text-sm font-semibold hover:bg-lime-50'])



        <a href="{{ route('masukan.index') }}"

           class="mt-3 rounded-xl px-4 py-3 text-sm font-semibold {{ $masukanActive ? 'bg-lime-100 text-green-700' : 'text-gray-700 hover:bg-lime-50' }}">

            💬 Masukan Masyarakat

        </a>



        <p class="mt-3 px-4 text-xs font-bold uppercase tracking-wider text-gray-400">Informasi</p>

        <a href="{{ route('ensiklopedia.index') }}"

           class="rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('ensiklopedia.*') ? 'bg-lime-100 text-green-700' : 'text-gray-700 hover:bg-lime-50' }}">

            📚 Ensiklopedia

        </a>



        <div class="mt-4 border-t border-green-100 pt-4">

            @auth

                <a href="{{ route('admin.dashboard') }}"

                   class="block rounded-xl bg-gradient-to-r from-lime-500 to-green-500 px-4 py-3 text-center text-sm font-bold text-white">

                    Panel Admin

                </a>

            @else

                <a href="{{ route('login') }}"

                   class="block rounded-xl border-2 border-green-300 px-4 py-3 text-center text-sm font-bold text-green-600">

                    Login Admin

                </a>

            @endauth

        </div>

    </nav>

@endif

