@php
    $dpaActive = request()->routeIs('admin.dpa.*');
    $tamanActive = request()->routeIs('admin.tamans.*', 'admin.taman-laporan.*');
    $bibitActive = request()->routeIs('admin.bibits.*', 'admin.bibit-masuks.*', 'admin.bibit-keluars.*', 'admin.bibit-laporan.*');
    $operasionalActive = request()->routeIs('admin.pemangkasans.*', 'admin.operasional-pertamanan-laporan.*', 'admin.pemeliharaan-tamans.*', 'admin.alat-sarana-operasionals.*');
    $masyarakatActive = request()->routeIs('admin.aduan-masyarakats.*', 'admin.survey-kepuasan.*');
    $ensiklopediaActive = request()->routeIs('admin.ensiklopedia-kategoris.*', 'admin.ensiklopedia-artikels.*');
    $evaluasiActive = request()->routeIs('admin.evaluasi.*');
    $sistemActive = request()->routeIs('admin.tim-pelaksanas.*', 'admin.users.*', 'admin.activity-logs.*');
@endphp

<nav class="space-y-1 px-4 py-4">
    @if ($canManageUsers ?? false)
        <p class="px-3 pb-1 pt-2 text-[10px] font-bold uppercase tracking-wider text-gray-500">Menu Operasional</p>
    @endif

    <a href="{{ route('admin.dashboard') }}"
       class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
        Dashboard
    </a>
    <div class="sidebar-menu-group {{ $tamanActive ? 'is-open' : '' }}">
        <button type="button" class="sidebar-menu-trigger flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ $tamanActive ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
            <span>Data Taman</span>
            <svg class="sidebar-chevron h-4 w-4 flex-shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div class="sidebar-submenu">
            <a href="{{ route('admin.tamans.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.tamans.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Kelola Taman
            </a>
            <a href="{{ route('admin.taman-laporan.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.taman-laporan.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Laporan Taman
            </a>
        </div>
    </div>

    <div class="sidebar-menu-group {{ $bibitActive ? 'is-open' : '' }}">
        <button type="button" class="sidebar-menu-trigger flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ $bibitActive ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
            <span>Data Bibit</span>
            <svg class="sidebar-chevron h-4 w-4 flex-shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div class="sidebar-submenu">
            <a href="{{ route('admin.bibits.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.bibits.*', 'admin.bibit-masuks.*', 'admin.bibit-keluars.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Kelola Bibit
            </a>
            <a href="{{ route('admin.bibit-laporan.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.bibit-laporan.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Laporan Bibit
            </a>
        </div>
    </div>

    <div class="sidebar-menu-group {{ $operasionalActive ? 'is-open' : '' }}">
        <button type="button" class="sidebar-menu-trigger flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ $operasionalActive ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
            <span class="flex min-w-0 flex-1 flex-nowrap items-center gap-1.5 pr-1">
                <span class="whitespace-nowrap text-sm leading-tight">Operasional Pertamanan</span>
                @if (($jadwalTerlambatCount ?? 0) > 0)
                    <span class="inline-flex shrink-0 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white">
                        {{ $jadwalTerlambatCount > 99 ? '99+' : $jadwalTerlambatCount }}
                    </span>
                @endif
            </span>
            <svg class="sidebar-chevron h-4 w-4 flex-shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div class="sidebar-submenu">
            <a href="{{ route('admin.pemeliharaan-tamans.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.pemeliharaan-tamans.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Pemeliharaan Rutin
            </a>
            <a href="{{ route('admin.pemangkasans.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.pemangkasans.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Permohonan
            </a>
            <a href="{{ route('admin.alat-sarana-operasionals.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.alat-sarana-operasionals.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Alat/Sarana
            </a>
            <a href="{{ route('admin.operasional-pertamanan-laporan.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.operasional-pertamanan-laporan.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Laporan Operasional
            </a>
        </div>
    </div>

    <div class="sidebar-menu-group {{ $evaluasiActive ? 'is-open' : '' }}">
        <button type="button" class="sidebar-menu-trigger flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ $evaluasiActive ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
            <span>Evaluasi</span>
            <svg class="sidebar-chevron h-4 w-4 flex-shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div class="sidebar-submenu">
            <a href="{{ route('admin.evaluasi.pemeliharaan.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.evaluasi.pemeliharaan.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Pemeliharaan per Taman
            </a>
            <a href="{{ route('admin.evaluasi.armada.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.evaluasi.armada.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Utilisasi Armada
            </a>
            <a href="{{ route('admin.evaluasi.kinerja-tim.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.evaluasi.kinerja-tim.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Kinerja Tim
            </a>
            <a href="{{ route('admin.evaluasi.operasional-permohonan.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.evaluasi.operasional-permohonan.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Operasional Permohonan
            </a>
            <a href="{{ route('admin.evaluasi.rth-terpelihara.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.evaluasi.rth-terpelihara.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                RTH Terpelihara
            </a>
            <a href="{{ route('admin.evaluasi.masukan-masyarakat.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.evaluasi.masukan-masyarakat.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Masukan Masyarakat
            </a>
            <a href="{{ route('admin.evaluasi.kelengkapan-data.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.evaluasi.kelengkapan-data.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                Kelengkapan Data
            </a>
            <a href="{{ route('admin.evaluasi.rap-konsolidasi.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm font-semibold {{ request()->routeIs('admin.evaluasi.rap-konsolidasi.*') ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-gray-200' }}">
                RAP Konsolidasi
            </a>
        </div>
    </div>

    <div class="sidebar-menu-group {{ $masyarakatActive ? 'is-open' : '' }}">
        <button type="button" class="sidebar-menu-trigger flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ $masyarakatActive ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
            <span class="flex items-center gap-2">
                Masukan
                @if (($masukanBaruCount ?? 0) > 0)
                    <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white" title="Masukan belum ditinjau">
                        {{ $masukanBaruCount > 99 ? '99+' : $masukanBaruCount }}
                    </span>
                @endif
            </span>
            <svg class="sidebar-chevron h-4 w-4 flex-shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div class="sidebar-submenu">
            <a href="{{ route('admin.aduan-masyarakats.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.aduan-masyarakats.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                <span class="flex items-center justify-between gap-2">
                    Aduan Masyarakat
                    @if (($aduanBaruCount ?? 0) > 0)
                        <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white">
                            {{ $aduanBaruCount > 99 ? '99+' : $aduanBaruCount }}
                        </span>
                    @endif
                </span>
            </a>
            <a href="{{ route('admin.survey-kepuasan.index') }}"
               class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.survey-kepuasan.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                <span class="flex items-center justify-between gap-2">
                    Survey Kepuasan
                    @if (($surveyBaruCount ?? 0) > 0)
                        <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white">
                            {{ $surveyBaruCount > 99 ? '99+' : $surveyBaruCount }}
                        </span>
                    @endif
                </span>
            </a>
        </div>
    </div>

    @if ($canManageUsers ?? false)
        <div class="sidebar-menu-group {{ $ensiklopediaActive ? 'is-open' : '' }}">
            <button type="button" class="sidebar-menu-trigger flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ $ensiklopediaActive ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>Ensiklopedia</span>
                <svg class="sidebar-chevron h-4 w-4 flex-shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="sidebar-submenu">
                <a href="{{ route('admin.ensiklopedia-kategoris.index') }}"
                   class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.ensiklopedia-kategoris.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                    Kategori
                </a>
                <a href="{{ route('admin.ensiklopedia-artikels.index') }}"
                   class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.ensiklopedia-artikels.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                    Artikel
                </a>
            </div>
        </div>

        <div class="sidebar-menu-group {{ $sistemActive ? 'is-open' : '' }}">
            <button type="button" class="sidebar-menu-trigger flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-left text-sm {{ $sistemActive ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <span>Sistem</span>
                <svg class="sidebar-chevron h-4 w-4 flex-shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="sidebar-submenu">
                <a href="{{ route('admin.tim-pelaksanas.index') }}"
                   class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.tim-pelaksanas.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                    Wilayah Kerja
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.users.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                    Kelola Pengguna
                </a>
                <a href="{{ route('admin.activity-logs.index') }}"
                   class="block rounded-lg py-2 pl-8 pr-3 text-sm {{ request()->routeIs('admin.activity-logs.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                    Log Aktivitas
                </a>
            </div>
        </div>

        <div class="my-3 border-t border-gray-700"></div>
        <p class="px-3 pb-1 pt-2 text-[10px] font-bold uppercase tracking-wider text-gray-500">Menu Monitoring DPA</p>

        <a href="{{ route('admin.dpa.dashboard') }}"
           class="block rounded-lg px-3 py-2 text-sm {{ $dpaActive ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
            Monitoring DPA
        </a>
    @endif

        <a href="{{ route('home') }}"
           class="block rounded-lg px-3 py-2 text-sm text-gray-300 hover:bg-gray-800">
            Lihat Situs Publik
        </a>
        @if (auth()->user()?->canWrite())
            <a href="{{ route('lapangan.index') }}"
               class="mt-2 block rounded-lg bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                Input Lapangan
            </a>
        @endif
    </nav>
