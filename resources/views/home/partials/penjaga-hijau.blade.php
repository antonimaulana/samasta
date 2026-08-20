<section>
    <div class="mb-8 text-center sm:mb-10">
        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-[10px] font-bold tracking-wide text-emerald-700 sm:px-4 sm:text-xs">
            <span>🌱</span> Disperkimtan Kota Batam
        </span>
        <h2 class="mt-2 text-xl font-black text-gray-900 sm:mt-3 sm:text-2xl lg:text-3xl">Penjaga Hijau Kota</h2>
        <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-gray-600 sm:mt-3 sm:text-base">
            Wajah di balik keindahan taman Batam — tim lapangan yang setiap hari merawat, menanam,
            dan menjaga ruang hijau agar nyaman bagi seluruh warga.
        </p>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4 lg:gap-6">
        @foreach ($penjagaHijauGallery as $foto)
            <figure class="group overflow-hidden rounded-2xl bg-green-900 shadow-lg shadow-green-200/50 ring-1 ring-green-100 sm:rounded-3xl sm:shadow-xl">
                <div class="h-40 overflow-hidden sm:h-auto sm:aspect-[3/4]">
                    <img src="{{ asset($foto['image']) }}"
                         alt="{{ $foto['title'] }} — Penjaga Hijau Kota Batam"
                         loading="lazy"
                         class="h-full w-full object-cover object-center transition duration-700 group-hover:scale-105">
                </div>
            </figure>
        @endforeach
    </div>
</section>
