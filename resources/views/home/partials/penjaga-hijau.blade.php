<section class="penjaga-hijau" id="penjaga-hijau" aria-labelledby="penjaga-hijau-title">
    <div class="penjaga-hijau__header">
        <span class="penjaga-hijau__badge">
            <span aria-hidden="true">🌱</span> Disperkimtan Kota Batam
        </span>
        <h2 id="penjaga-hijau-title" class="penjaga-hijau__title">Penjaga Hijau Kota</h2>
        <p class="penjaga-hijau__lead">
            Tim lapangan yang setiap hari merawat, menanam, dan menjaga ruang hijau agar Kota Batam semakin indah
            dan nyaman bagi seluruh warga.
        </p>
    </div>

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
