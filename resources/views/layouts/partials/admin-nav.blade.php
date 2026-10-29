@php
    $tamanActive = request()->routeIs('admin.tamans.*', 'admin.taman-laporan.*');
    $bibitActive = request()->routeIs('admin.bibits.*', 'admin.bibit-masuks.*', 'admin.bibit-keluars.*', 'admin.bibit-laporan.*');
    $operasionalActive = request()->routeIs('admin.pemangkasans.*', 'admin.operasional-pertamanan-laporan.*', 'admin.pemeliharaan-tamans.*', 'admin.armadas.*');
    $masyarakatActive = request()->routeIs('admin.aduan-masyarakats.*', 'admin.survey-kepuasan.*');
    $evaluasiActive = request()->routeIs('admin.evaluasi.*');
    $sistemActive = request()->routeIs(
        'admin.tim-pelaksanas.*',
        'admin.users.*',
        'admin.activity-logs.*',
        'admin.beranda-slides.*',
        'admin.faq.*',
        'admin.ensiklopedia-pengaturan.*',
        'admin.ensiklopedia-kategoris.*',
        'admin.ensiklopedia-artikels.*',
        'admin.ensiklopedia-portal.*',
    );
    $tautanActive = request()->routeIs('lapangan.*');
    $lapanganActive = request()->routeIs('lapangan.*');

    $navTop = fn (bool $active): string => 'admin-nav-link'.($active ? ' admin-nav-link--active' : '');
    $navTrigger = fn (bool $active): string => 'admin-nav-link admin-nav-link--trigger'.($active ? ' admin-nav-link--section-active' : '');
    $navSub = fn (bool $active): string => 'admin-nav-sublink'.($active ? ' admin-nav-sublink--active' : '');
@endphp

<nav class="admin-nav space-y-0.5 px-2 py-3">
    <p class="admin-nav-section-label">Menu</p>

    <a href="{{ route('admin.dashboard') }}" class="{{ $navTop(request()->routeIs('admin.dashboard')) }}">
        <x-admin.nav-icon name="dashboard" />
        <span class="min-w-0 flex-1">Dashboard</span>
    </a>

    <div class="sidebar-menu-group {{ $tamanActive ? 'is-open' : '' }}">
        <button type="button" class="sidebar-menu-trigger {{ $navTrigger($tamanActive) }}">
            <x-admin.nav-icon name="taman" />
            <span class="min-w-0 flex-1 text-left">Data Taman</span>
            <svg class="sidebar-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div class="sidebar-submenu">
            <a href="{{ route('admin.tamans.index') }}" class="{{ $navSub(request()->routeIs('admin.tamans.*')) }}">
                Kelola Taman
            </a>
            <a href="{{ route('admin.taman-laporan.index') }}" class="{{ $navSub(request()->routeIs('admin.taman-laporan.*')) }}">
                Laporan Taman
            </a>
        </div>
    </div>

    <div class="sidebar-menu-group {{ $bibitActive ? 'is-open' : '' }}">
        <button type="button" class="sidebar-menu-trigger {{ $navTrigger($bibitActive) }}">
            <x-admin.nav-icon name="bibit" />
            <span class="min-w-0 flex-1 text-left">Data Bibit</span>
            <svg class="sidebar-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div class="sidebar-submenu">
            <a href="{{ route('admin.bibits.index') }}" class="{{ $navSub(request()->routeIs('admin.bibits.*', 'admin.bibit-masuks.*', 'admin.bibit-keluars.*')) }}">
                Kelola Bibit
            </a>
            <a href="{{ route('admin.bibit-laporan.index') }}" class="{{ $navSub(request()->routeIs('admin.bibit-laporan.*')) }}">
                Laporan Bibit
            </a>
        </div>
    </div>

    <div class="sidebar-menu-group {{ $operasionalActive ? 'is-open' : '' }}">
        <button type="button" class="sidebar-menu-trigger {{ $navTrigger($operasionalActive) }}">
            <x-admin.nav-icon name="operasional" />
            <span class="flex min-w-0 flex-1 items-center gap-1.5 text-left">
                <span class="min-w-0">Operasional Pertamanan</span>
                @if (($jadwalTerlambatCount ?? 0) > 0)
                    <span class="admin-nav-badge">{{ $jadwalTerlambatCount > 99 ? '99+' : $jadwalTerlambatCount }}</span>
                @endif
            </span>
            <svg class="sidebar-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div class="sidebar-submenu">
            <a href="{{ route('admin.pemeliharaan-tamans.index') }}" class="{{ $navSub(request()->routeIs('admin.pemeliharaan-tamans.*')) }}">
                Pemeliharaan Rutin
            </a>
            <a href="{{ route('admin.pemangkasans.index') }}" class="{{ $navSub(request()->routeIs('admin.pemangkasans.*')) }}">
                Permohonan
            </a>
            <a href="{{ route('admin.armadas.index') }}" class="{{ $navSub(request()->routeIs('admin.armadas.*')) }}">
                Armada
            </a>
            <a href="{{ route('admin.operasional-pertamanan-laporan.index') }}" class="{{ $navSub(request()->routeIs('admin.operasional-pertamanan-laporan.*')) }}">
                Laporan Operasional
            </a>
        </div>
    </div>

    <a href="{{ route('admin.evaluasi.index') }}" class="{{ $navTop($evaluasiActive) }}">
        <x-admin.nav-icon name="chart" />
        <span class="min-w-0 flex-1">Evaluasi</span>
    </a>

    <div class="sidebar-menu-group {{ $masyarakatActive ? 'is-open' : '' }}">
        <button type="button" class="sidebar-menu-trigger {{ $navTrigger($masyarakatActive) }}">
            <x-admin.nav-icon name="chat" />
            <span class="flex min-w-0 flex-1 items-center gap-1.5 text-left">
                <span>Masukan</span>
                @if (($masukanBaruCount ?? 0) > 0)
                    <span class="admin-nav-badge" title="Masukan belum ditinjau">{{ $masukanBaruCount > 99 ? '99+' : $masukanBaruCount }}</span>
                @endif
            </span>
            <svg class="sidebar-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div class="sidebar-submenu">
            <a href="{{ route('admin.aduan-masyarakats.index') }}" class="{{ $navSub(request()->routeIs('admin.aduan-masyarakats.*')) }}">
                <span class="flex min-w-0 flex-1 items-center justify-between gap-2">
                    <span>Aduan Masyarakat</span>
                    @if (($aduanBaruCount ?? 0) > 0)
                        <span class="admin-nav-badge">{{ $aduanBaruCount > 99 ? '99+' : $aduanBaruCount }}</span>
                    @endif
                </span>
            </a>
            <a href="{{ route('admin.survey-kepuasan.index') }}" class="{{ $navSub(request()->routeIs('admin.survey-kepuasan.*')) }}">
                <span class="flex min-w-0 flex-1 items-center justify-between gap-2">
                    <span>Survey Kepuasan</span>
                    @if (($surveyBaruCount ?? 0) > 0)
                        <span class="admin-nav-badge">{{ $surveyBaruCount > 99 ? '99+' : $surveyBaruCount }}</span>
                    @endif
                </span>
            </a>
        </div>
    </div>

    @if ($canManageUsers ?? false)
        <div class="sidebar-menu-group {{ $sistemActive ? 'is-open' : '' }}">
            <button type="button" class="sidebar-menu-trigger {{ $navTrigger($sistemActive) }}">
                <x-admin.nav-icon name="cog" />
                <span class="min-w-0 flex-1 text-left">Sistem</span>
                <svg class="sidebar-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="sidebar-submenu">
                <a href="{{ route('admin.tim-pelaksanas.index') }}" class="{{ $navSub(request()->routeIs('admin.tim-pelaksanas.*')) }}">
                    Tim Kerja
                </a>
                <a href="{{ route('admin.users.index') }}" class="{{ $navSub(request()->routeIs('admin.users.*')) }}">
                    Kelola Pengguna
                </a>
                <a href="{{ route('admin.activity-logs.index') }}" class="{{ $navSub(request()->routeIs('admin.activity-logs.*')) }}">
                    Log Aktivitas
                </a>
                <a href="{{ route('admin.beranda-slides.index') }}" class="{{ $navSub(request()->routeIs('admin.beranda-slides.*')) }}">
                    Slide Beranda
                </a>
                <a href="{{ route('admin.faq.index') }}" class="{{ $navSub(request()->routeIs('admin.faq.*')) }}">
                    FAQ Beranda
                </a>
                <a href="{{ route('admin.ensiklopedia-pengaturan.index') }}" class="{{ $navSub(request()->routeIs('admin.ensiklopedia-pengaturan.*', 'admin.ensiklopedia-kategoris.*', 'admin.ensiklopedia-artikels.*', 'admin.ensiklopedia-portal.*')) }}">
                    Ensiklopedia
                </a>
            </div>
        </div>
    @endif

    <div class="sidebar-menu-group {{ $tautanActive ? 'is-open' : '' }}">
        <button type="button" class="sidebar-menu-trigger {{ $navTrigger($tautanActive) }}">
            <x-admin.nav-icon name="link" />
            <span class="min-w-0 flex-1 text-left">Tautan</span>
            <svg class="sidebar-chevron h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div class="sidebar-submenu">
            <a href="{{ route('home') }}"
               target="_blank"
               rel="noopener noreferrer"
               class="{{ $navSub(false) }}">
                Laman Publik
            </a>
            @if (auth()->user()?->canInputLapangan())
                <a href="{{ route('lapangan.index') }}" class="{{ $navSub($lapanganActive) }}">
                    Input Lapangan
                </a>
            @endif
        </div>
    </div>
</nav>
