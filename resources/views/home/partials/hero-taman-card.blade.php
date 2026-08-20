<a href="{{ route('tamans.show', $taman) }}"

   class="group flex h-full flex-col overflow-hidden rounded-2xl border border-green-100 bg-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl">

    <div class="home-hero-card-media relative w-full overflow-hidden">

        @if ($label ?? null)

            <span class="absolute left-2 top-2 z-10 rounded-full bg-lime-400/95 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-green-950 shadow-sm sm:px-2.5 sm:text-[10px]">

                {{ $label }}

            </span>

        @endif

        @if ($taman->foto_url)

            <img src="{{ $taman->foto_url }}" alt="{{ $taman->nama_taman }}"

                 class="h-full w-full object-cover object-center transition duration-500 group-hover:scale-105">

        @else

            <div class="relative h-full w-full bg-gradient-to-br from-emerald-100 via-green-100 to-lime-200">

                <img src="{{ asset('images/hero-taman-hijau.jpg') }}" alt=""

                     aria-hidden="true"

                     class="absolute inset-0 h-full w-full object-cover opacity-35">

                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 p-3 text-center">

                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-lg shadow-sm">🌳</span>

                    <span class="rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-green-800">

                        {{ $taman->kategori }}

                    </span>

                </div>

            </div>

        @endif

    </div>

    <div class="border-t border-green-50 bg-white p-3">

        <p class="line-clamp-2 text-sm font-bold leading-snug text-gray-900">{{ $taman->nama_taman }}</p>

        <p class="mt-1 truncate text-xs text-gray-600">

            {{ $taman->kategori }}@if ($taman->luasan > 0) · {{ number_format($taman->luasan, 0, ',', '.') }} m² @endif

        </p>

    </div>

</a>

