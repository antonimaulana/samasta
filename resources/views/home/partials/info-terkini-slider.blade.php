<section class="relative" id="info-terkini-slider">
    <div class="relative mb-6 sm:mb-8">
        <h2 class="text-center text-2xl font-black text-gray-900 sm:text-3xl">Informasi Terkini</h2>
        <div class="absolute right-0 top-1/2 hidden -translate-y-1/2 items-center gap-2 sm:flex">
            <button type="button" id="info-slider-prev"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-green-200 bg-white text-green-600 shadow-sm transition hover:bg-lime-50"
                    aria-label="Slide sebelumnya">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button type="button" id="info-slider-next"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-green-200 bg-white text-green-600 shadow-sm transition hover:bg-lime-50"
                    aria-label="Slide berikutnya">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="info-terkini-panel overflow-hidden rounded-3xl border border-green-100 p-4 shadow-xl shadow-green-100/50 ring-1 ring-green-50 sm:p-6">
        <div class="info-slide-viewport relative overflow-hidden rounded-2xl">
            <div id="info-slider-track" class="flex items-stretch transition-transform duration-500 ease-out">
            @php
                $ensiklopediaSlideTags = $ensiklopediaKategoris
                    ->map(fn ($kat) => trim($kat->icon.' '.$kat->nama))
                    ->all();

                if ($ensiklopediaSlideTags === []) {
                    $ensiklopediaSlideTags = ['Program Pemerintah', 'Tanaman & Flora', 'Edukasi'];
                }
            @endphp

            @include('home.partials.info-slide-card', [
                'href' => route('rth.index'),
                'titleAttr' => 'RTH Kota Batam',
                'leftGradient' => 'from-lime-400 via-green-400 to-emerald-500',
                'badge' => 'Disperakimtan',
                'heroValue' => number_format($rthTotalLuas, 0, ',', '.'),
                'heroLabel' => 'M² luas RTH terpelihara',
                'statLeftValue' => number_format($rthTotalLokasi),
                'statLeftLabel' => 'Lokasi',
                'statRightValue' => (string) ($rthKategoriCount ?? 5),
                'statRightLabel' => 'Kategori',
                'heading' => 'Jumlah Luas Ruang Terbuka Hijau Kota Batam',
                'description' => 'Disperakimtan mengelola taman kota, taman lingkungan, jalur hijau jalan, Kebun Raya Batam, dan TPU — seluruhnya dalam kondisi terpelihara.',
                'tags' => ['Taman Kota', 'Taman Lingkungan', 'Jalur Hijau', 'Kebun Raya', 'TPU'],
                'tagClasses' => 'bg-lime-100 text-green-700 ring-lime-200',
                'ctaText' => 'Baca data lengkap',
            ])

            @include('home.partials.info-slide-simponi')

            @include('home.partials.info-slide-perda-ketertiban')

            @include('home.partials.info-slide-card', [
                'href' => route('masukan.index'),
                'titleAttr' => 'Kegiatan Pertamanan',
                'leftGradient' => 'from-green-500 via-emerald-500 to-teal-600',
                'badge' => 'Kegiatan Pertamanan',
                'heroMode' => 'dual',
                'heroDualLeftValue' => number_format($operasionalPemangkasanSelesai),
                'heroDualLeftLabel' => 'Pemangkasan Pohon',
                'heroDualRightValue' => number_format($operasionalPohonTumbangSelesai),
                'heroDualRightLabel' => 'Penanganan Pohon Tumbang',
                'showStats' => false,
                'heading' => 'Kegiatan Pertamanan Kota Batam',
                'description' => 'Ringkasan kegiatan pemangkasan pohon dan penanganan pohon tumbang yang telah diselesaikan tim Disperakimtan di seluruh Kota Batam.',
                'tags' => ['Pemangkasan Pohon', 'Penanganan Pohon Tumbang', 'Penghijauan', 'Keselamatan Publik'],
                'tagClasses' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                'ctaText' => 'Ajukan layanan pertamanan',
            ])

            @include('home.partials.info-slide-card', [
                'href' => route('ensiklopedia.index'),
                'titleAttr' => 'Ensiklopedia',
                'leftGradient' => 'from-emerald-400 via-teal-400 to-green-500',
                'badge' => 'Pusat Pengetahuan',
                'heroValue' => number_format($ensiklopediaTotalArtikel),
                'heroLabel' => 'Artikel edukasi tersedia',
                'statLeftValue' => $ensiklopediaKategoris->count(),
                'statLeftLabel' => 'Topik',
                'statRightValue' => '📚',
                'statRightLabel' => 'Ensiklopedia',
                'heading' => 'Ensiklopedia '.config('app.name'),
                'description' => 'Pelajari program pemerintah, tanaman & flora, serta edukasi lingkungan seputar pertamanan dan penghijauan Kota Batam.',
                'tags' => $ensiklopediaSlideTags,
                'tagClasses' => 'bg-teal-100 text-teal-800 ring-teal-200',
                'ctaText' => 'Jelajahi ensiklopedia',
            ])

            @include('home.partials.info-slide-card', [
                'href' => route('masukan.index'),
                'titleAttr' => 'Aduan Masyarakat',
                'cardRing' => 'ring-orange-100',
                'cardShadow' => 'shadow-orange-200/60 hover:shadow-orange-300/50',
                'leftGradient' => 'from-orange-400 via-amber-400 to-red-500',
                'badge' => 'Layanan Publik',
                'heroValue' => number_format($aduanTotal),
                'heroLabel' => 'Aduan masuk',
                'statLeftValue' => number_format($aduanSelesai),
                'statLeftLabel' => 'Selesai',
                'statRightValue' => number_format($aduanBulanIni),
                'statRightLabel' => 'Bulan ini',
                'heading' => 'Laporkan Kondisi Taman & Tanaman',
                'headingHover' => 'group-hover:text-orange-600',
                'description' => 'Temukan tanaman rusak, cabang berbahaya, atau fasilitas taman tidak layak? Kirim aduan dengan foto dan lokasi — tim Disperakimtan akan menindaklanjuti.',
                'tags' => ['Tanaman Rusak', 'Tanaman Berbahaya', 'Fasilitas Rusak', 'Kebersihan'],
                'tagClasses' => 'bg-orange-100 text-orange-800 ring-orange-200',
                'ctaText' => 'Buat aduan sekarang',
                'ctaColor' => 'text-orange-600',
            ])
            </div>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-center gap-4 sm:mt-8">
        <button type="button" id="info-slider-prev-mobile"
                class="flex h-8 w-8 items-center justify-center rounded-full border border-green-200 bg-white text-green-600 sm:hidden"
                aria-label="Slide sebelumnya">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <div id="info-slider-dots" class="flex gap-2">
            <button type="button" class="info-slider-dot h-2.5 w-8 rounded-full bg-green-500 transition-all" data-index="0" aria-label="Slide RTH"></button>
            <button type="button" class="info-slider-dot h-2.5 w-2.5 rounded-full bg-green-200 transition-all" data-index="1" aria-label="Slide Layanan Pemakaman Simponi"></button>
            <button type="button" class="info-slider-dot h-2.5 w-2.5 rounded-full bg-green-200 transition-all" data-index="2" aria-label="Slide Perda Ketertiban Umum"></button>
            <button type="button" class="info-slider-dot h-2.5 w-2.5 rounded-full bg-green-200 transition-all" data-index="3" aria-label="Slide Kegiatan Pertamanan"></button>
            <button type="button" class="info-slider-dot h-2.5 w-2.5 rounded-full bg-green-200 transition-all" data-index="4" aria-label="Slide Ensiklopedia"></button>
            <button type="button" class="info-slider-dot h-2.5 w-2.5 rounded-full bg-green-200 transition-all" data-index="5" aria-label="Slide Aduan Masyarakat"></button>
        </div>
        <button type="button" id="info-slider-next-mobile"
                class="flex h-8 w-8 items-center justify-center rounded-full border border-green-200 bg-white text-green-600 sm:hidden"
                aria-label="Slide berikutnya">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</section>
