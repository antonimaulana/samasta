<section>
    <div class="mb-8 sm:mb-10">
        <h2 class="text-center text-2xl font-black text-gray-900 sm:text-3xl">Ensiklopedia</h2>
    </div>

    <div class="grid items-stretch gap-4 sm:gap-6 lg:grid-cols-5 lg:gap-8">
        <div class="fakta-edukasi-card relative flex min-h-[280px] flex-col rounded-3xl p-6 shadow-xl shadow-green-500/25 sm:p-8 lg:col-span-3 lg:min-h-0">
            <div class="relative z-10 flex flex-1 flex-col gap-8 lg:flex-row lg:items-stretch lg:gap-10">
                <div class="flex shrink-0 flex-col justify-center lg:w-[38%] lg:max-w-xs">
                    <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-emerald-100">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/15 text-sm">💡</span>
                        Fakta Edukasi
                    </p>
                    <h2 class="mt-4 text-2xl font-black leading-tight text-white sm:text-3xl">Tahukah Anda?</h2>
                    <p class="mt-3 text-sm leading-relaxed text-white/75">
                        Fakta singkat seputar penghijauan &amp; RTH Kota Batam
                    </p>
                </div>

                <div class="flex flex-1 flex-col justify-center">
                    <div id="fakta-rotator"
                         class="fakta-pod flex min-h-[7.5rem] flex-col justify-center rounded-2xl px-4 py-4 sm:px-5 sm:py-5"
                         data-fakta='@json($faktaEdukasi)'>
                        <div class="flex items-start gap-3">
                            <span id="fakta-rotator-index" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/20 text-xs font-black text-white">1</span>
                            <p id="fakta-rotator-text" class="fakta-rotator-text text-sm leading-relaxed text-white sm:text-[0.9375rem]">{{ $faktaEdukasi[0] ?? '' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div id="fakta-rotator-dots" class="flex flex-wrap gap-1.5"></div>
                        <button type="button" id="fakta-rotator-next"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-white/15 px-3 py-2 text-xs font-bold text-white ring-1 ring-white/25 transition hover:bg-white/25">
                            Fakta berikutnya →
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('ensiklopedia.quiz') }}"
           class="group flex min-h-[280px] flex-col justify-between rounded-3xl border border-violet-200/80 bg-gradient-to-br from-violet-50 via-white to-purple-50 p-6 shadow-lg shadow-violet-100/60 ring-1 ring-violet-100 transition hover:-translate-y-1 hover:border-violet-300 hover:shadow-violet-200/50 lg:col-span-2">
            <div>
                <span class="inline-flex rounded-full bg-violet-600 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white shadow-sm">
                    Interaktif
                </span>
                <div class="mt-5 flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white text-3xl shadow-md ring-1 ring-violet-100">🧠</span>
                    <div>
                        <h2 class="text-xl font-black text-gray-900">Kuis Tanaman</h2>
                        <p class="mt-0.5 text-sm text-gray-500">{{ $totalKuisPertanyaan }} pertanyaan</p>
                    </div>
                </div>
                <p class="mt-5 text-sm leading-relaxed text-gray-600">
                    Uji pengetahuan Anda tentang RTH, tanaman, dan pertamanan Batam. Dapatkan skor dan penjelasan setiap jawaban.
                </p>
            </div>
            <span class="mt-8 inline-flex items-center gap-2 rounded-xl bg-violet-100/80 px-4 py-2.5 text-sm font-bold text-violet-700 transition group-hover:gap-3 group-hover:bg-violet-100">
                Mulai kuis sekarang →
            </span>
        </a>
    </div>
</section>
