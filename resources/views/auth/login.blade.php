<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — {{ config('app.name') }}</title>
    @include('layouts.partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="flex min-h-screen flex-col bg-green-50 text-gray-900 antialiased">
    <header class="border-b border-green-200/60 bg-white/90 backdrop-blur-lg shadow-sm shadow-green-100/50">
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3">
                @include('layouts.partials.app-logo', ['class' => 'transition group-hover:scale-105'])
                <div class="min-w-0">
                    <span class="block truncate text-base font-bold leading-tight text-green-700">{{ config('app.name') }}</span>
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-lime-500">{{ config('app.full_name') }}</span>
                </div>
            </a>
        </div>
    </header>

    <main class="flex flex-1 flex-col items-center justify-center px-4 py-10 sm:py-14">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <h1 class="text-xl font-bold leading-snug text-green-800 sm:text-2xl">
                    Halo, Selamat Datang di {{ config('app.name') }}!
                </h1>
                <p class="mt-2 text-sm text-gray-600">Masuk untuk kelola data taman.</p>
            </div>

            <div class="rounded-xl border border-green-200/80 bg-white p-6 shadow-sm shadow-green-100/40 sm:p-8">
                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Kata sandi</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required autocomplete="current-password"
                                   class="w-full rounded-lg border border-gray-300 py-2.5 pl-3 pr-10 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                            <button type="button"
                                    id="toggle-password"
                                    class="absolute inset-y-0 right-0 flex items-center rounded-r-lg px-3 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500"
                                    aria-label="Tampilkan kata sandi"
                                    aria-pressed="false">
                                <svg id="icon-password-hidden" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg id="icon-password-visible" class="hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5 1.447 0 2.813-.29 4.063-.814m-2.068 2.068A9.956 9.956 0 0 1 12 19.5c-4.638 0-8.573-3.007-9.963-7.178a1.012 1.012 0 0 1 0-.639c.825-2.762 2.924-5.013 5.688-6.11M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        Ingat saya
                    </label>

                    <button type="submit"
                            class="w-full rounded-lg bg-green-600 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                        Masuk
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-sm text-gray-500">
                <a href="{{ route('home') }}" class="font-medium text-green-700 hover:underline">← Kembali ke beranda</a>
            </p>
        </div>
    </main>

    @include('layouts.partials.public-footer')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('password');
            var toggle = document.getElementById('toggle-password');
            var iconHidden = document.getElementById('icon-password-hidden');
            var iconVisible = document.getElementById('icon-password-visible');
            if (! input || ! toggle) return;

            toggle.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                toggle.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                iconHidden.classList.toggle('hidden', show);
                iconVisible.classList.toggle('hidden', ! show);
            });
        });
    </script>
</body>
</html>
