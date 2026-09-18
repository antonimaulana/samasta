<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'SIMTAMAN') }} — {{ config('app.full_name', 'Sistem Informasi Manajemen Pertamanan') }}</title>

        <!-- Fonts & Tailwind CDN Langsung untuk Bypass Kendala Build -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                        colors: {
                            emerald: {
                                950: '#022c22',
                            }
                        }
                    }
                }
            }
        </script>
        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; }
            .bg-pattern {
                background-image: radial-gradient(rgba(16, 185, 129, 0.1) 1px, transparent 0);
                background-size: 24px 24px;
            }
        </style>
    </head>
    <body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col bg-pattern antialiased selection:bg-emerald-500 selection:text-white">

        <!-- Top Glowing Bar Accent -->
        <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-500"></div>
        
        <!-- Premium Glassmorphism Navbar -->
        <header class="w-full max-w-7xl mx-auto px-6 py-6 flex items-center justify-between sticky top-0 z-50 backdrop-blur-md bg-slate-900/70 border-b border-slate-800/60">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-slate-900">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm11.378-3.917c-.89-.777-2.366-.777-3.255 0a2.249 2.249 0 0 0 0 3.255c.89.778 2.365.778 3.255 0v3.255a2.249 2.249 0 0 1-3.255 0" />
                    </svg>
                </div>
                <div>
                    <span class="text-lg font-extrabold tracking-tight text-white block leading-none">{{ config('app.name', 'SIMTAMAN') }}</span>
                    <span class="text-[10px] text-emerald-400 uppercase tracking-widest font-bold">{{ config('app.full_name', 'Sistem Informasi Manajemen Pertamanan') }}</span>
                </div>
            </div>

            @if (Route::has('login'))
                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-slate-900 bg-emerald-400 hover:bg-emerald-300 rounded-lg transition-all duration-200 shadow-md shadow-emerald-400/20">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-white transition-colors">
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg transition-all">
                                Registrasi
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <!-- Bold Asymmetric Hero Section -->
        <main class="flex-1 max-w-7xl mx-auto w-full px-6 py-12 lg:py-20 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- KIRI: Heavy Typography & Modern Navigation Grid -->
            <div class="lg:col-span-6 space-y-8">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-emerald-950 border border-emerald-800/60 text-emerald-400 text-xs font-bold uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                    {{ config('app.full_name', 'Sistem Informasi Manajemen Pertamanan') }}
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.05]">
                    Integrasi Tata Kelola <br>
                    <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400 bg-clip-text text-transparent">
                        Lansekap & Ruang Publik
                    </span>
                </h1>

                <p class="text-slate-400 text-base sm:text-lg max-w-xl leading-relaxed">
                    Platform digital pengendalian tata hijau, inventarisasi aset taman kota, koordinasi tim pemeliharaan, hingga pengelolaan area pemakaman secara akurat dan transparan.
                </p>

                <!-- Core Modules Quick Links (Membuat Halaman Terasa Penuh Fungsi) -->
                <div class="grid grid-cols-2 gap-4 max-w-md pt-2">
                    <div class="p-4 bg-slate-800/40 border border-slate-800 rounded-xl hover:border-emerald-500/40 transition-all group">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-3 group-hover:bg-emerald-500 group-hover:text-slate-900 transition-colors">
                            🌳
                        </div>
                        <h4 class="text-sm font-bold text-white mb-0.5">Taman & RTH</h4>
                        <p class="text-xs text-slate-500">Zonasi dan pemeliharaan area hijau</p>
                    </div>
                    <div class="p-4 bg-slate-800/40 border border-slate-800 rounded-xl hover:border-teal-500/40 transition-all group">
                        <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-400 flex items-center justify-center mb-3 group-hover:bg-teal-500 group-hover:text-slate-900 transition-colors">
                            🪦
                        </div>
                        <h4 class="text-sm font-bold text-white mb-0.5">Layanan TPU</h4>
                        <p class="text-xs text-slate-500">Peta dan administrasi pemakaman</p>
                    </div>
                </div>

                <!-- Action Call -->
                <div class="flex flex-col sm:flex-row items-center gap-4 pt-4">
                    <a href="{{ route('login') }}" class="w-full sm:w-auto text-center px-8 py-4 text-sm font-bold uppercase tracking-wider text-slate-900 bg-gradient-to-r from-emerald-400 to-teal-400 hover:from-emerald-300 hover:to-teal-300 rounded-xl transition-all shadow-xl shadow-emerald-500/10 hover:shadow-emerald-500/20 active:scale-[0.98]">
                        Masuk ke Aplikasi
                    </a>
                </div>
            </div>

            <!-- KANAN: Floating Data Metrics Glass Dashboard -->
            <div class="lg:col-span-6 relative flex items-center justify-center">
                <!-- Glowing Aura -->
                <div class="absolute w-72 h-72 bg-emerald-500/20 rounded-full blur-3xl -z-10 animate-pulse"></div>
                <div class="absolute w-48 h-48 bg-cyan-500/10 rounded-full blur-2xl -z-10 translate-x-20 -translate-y-10"></div>

                <!-- Main Glass Container -->
                <div class="w-full max-w-md bg-slate-800/50 border border-slate-700/50 p-6 rounded-2xl shadow-2xl backdrop-blur-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-3 text-[10px] font-mono text-slate-600 uppercase">Live telemetry</div>
                    
                    <h3 class="text-xs font-bold tracking-widest text-emerald-400 uppercase mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Ringkasan Data Lapangan
                    </h3>

                    <!-- Stat Blocks -->
                    <div class="space-y-4">
                        <div class="bg-slate-900/60 border border-slate-800 p-4 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="text-xs text-slate-500 block">Total Aset Lansekap</span>
                                <span class="text-2xl font-black text-white">1,482 <span class="text-xs font-normal text-slate-400">Titik</span></span>
                            </div>
                            <span class="text-xs font-bold text-emerald-400 bg-emerald-950/80 px-2 py-1 rounded">Aktif</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-900/60 border border-slate-800 p-4 rounded-xl">
                                <span class="text-xs text-slate-500 block mb-1">Jadwal Perawatan</span>
                                <span class="text-lg font-bold text-white">24 <span class="text-xs text-slate-400">Lokasi</span></span>
                                <div class="w-full bg-slate-800 h-1 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-teal-400 h-full w-4/5"></div>
                                </div>
                            </div>
                            <div class="bg-slate-900/60 border border-slate-800 p-4 rounded-xl">
                                <span class="text-xs text-slate-500 block mb-1">Zona Hijau Kota</span>
                                <span class="text-lg font-bold text-white">87 <span class="text-xs text-slate-400">Hektar</span></span>
                                <div class="w-full bg-slate-800 h-1 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-cyan-400 h-full w-2/3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Realtime Info Badge -->
                        <div class="p-3 bg-emerald-950/40 border border-emerald-900/40 rounded-xl text-center">
                            <p class="text-xs text-emerald-300">
                                Terintegrasi dengan **Dinas Perumahan, Kawasan Permukiman dan Pertamanan**.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-6 py-8 border-t border-slate-800/60 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'SIMTAMAN') }} — Disperakimtan Kota Batam</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-slate-300 transition-colors">Dokumentasi</a>
                <a href="#" class="hover:text-slate-300 transition-colors">Peta Wilayah</a>
            </div>
        </footer>
    </body>
</html>