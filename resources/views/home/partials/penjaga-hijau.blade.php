<section class="penjaga-hijau" id="penjaga-hijau" aria-labelledby="penjaga-hijau-title">
    <x-home-section-heading
        tag="Disperkimtan"
        title="Penjaga Hijau Kota"
        lead="Tim lapangan yang setiap hari merawat, menanam, dan menjaga ruang hijau agar Kota Batam semakin indah dan nyaman bagi seluruh warga."
        heading-id="penjaga-hijau-title"
    />

    <ul class="penjaga-hijau__grid" role="list">
        @foreach ($penjagaHijauGallery as $foto)
            <li>
                <article class="penjaga-hijau__card">
                    <div class="penjaga-hijau__media">
                        <img src="{{ asset($foto['image']) }}"
                             alt="Petugas pertamanan lapangan — Penjaga Hijau Kota Batam"
                             width="600"
                             height="800"
                             loading="lazy"
                             decoding="async"
                             class="penjaga-hijau__photo {{ $foto['photo_modifier'] ?? '' }}"
                             style="object-position: {{ $foto['focus'] ?? 'center 22%' }}">
                    </div>
                </article>
            </li>
        @endforeach
    </ul>
</section>
