@php
    $pimpinan = $pimpinan ?? \App\Support\PemerintahKotaBatam::pimpinan();
    $visiMisi = $visiMisi ?? \App\Support\PemerintahKotaBatam::visiMisi();
    [$walikota, $wakilWalikota, $kadisPerkim] = $pimpinan;
@endphp

<section class="pemerintah-kota-section" aria-labelledby="pemerintah-kota-heading">
    <div class="mb-8 text-center sm:mb-10">
        <div class="relative inline-block">
            <h2 id="pemerintah-kota-heading" class="relative z-10 text-2xl font-black text-gray-900 sm:text-3xl">
                Pemerintah Kota Batam
            </h2>
            <span class="absolute -bottom-1 left-1/2 z-0 h-3 w-[110%] -translate-x-1/2 rounded-full bg-gradient-to-r from-violet-200/80 via-pink-200/70 to-violet-200/80" aria-hidden="true"></span>
        </div>
        <p class="mx-auto mt-4 max-w-2xl text-sm text-gray-600 sm:text-base">
            Walikota, Wakil Walikota, dan Kepala Dinas Perkimtan Kota Batam
        </p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4 lg:gap-6">
        @include('home.partials.pimpinan-card', ['pejabat' => $walikota])
        @include('home.partials.pimpinan-card', ['pejabat' => $wakilWalikota])
        @include('home.partials.pimpinan-visi-misi', ['visiMisi' => $visiMisi])
        @include('home.partials.pimpinan-card', ['pejabat' => $kadisPerkim])
    </div>
</section>
