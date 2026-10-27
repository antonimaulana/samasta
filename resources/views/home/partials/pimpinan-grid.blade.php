@php
    $pimpinan = $pimpinan ?? \App\Support\PemerintahKotaBatam::pimpinan();
    $visiMisi = $visiMisi ?? \App\Support\PemerintahKotaBatam::visiMisi();
    [$walikota, $wakilWalikota, $kadisPerkim] = $pimpinan;
@endphp

<section class="pemerintah-kota-section" aria-labelledby="pemerintah-kota-heading">
    <x-home-section-heading
        tag="Layanan"
        title="Pemerintah Kota Batam"
        lead="Walikota, Wakil Walikota, dan Kepala Dinas Perkimtan Kota Batam."
        heading-id="pemerintah-kota-heading"
    />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4 lg:gap-6">
        @include('home.partials.pimpinan-card', ['pejabat' => $walikota])
        @include('home.partials.pimpinan-card', ['pejabat' => $wakilWalikota])
        @include('home.partials.pimpinan-visi-misi', ['visiMisi' => $visiMisi])
        @include('home.partials.pimpinan-card', ['pejabat' => $kadisPerkim])
    </div>
</section>
