@extends('layouts.public')

@section('title', 'Survey Kepuasan Masyarakat')

@section('hero')
    <section class="relative overflow-hidden bg-gradient-to-br from-violet-500 via-purple-500 to-fuchsia-500 text-white">
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <p class="text-xs font-bold uppercase tracking-widest text-violet-100">Partisipasi Masyarakat</p>
            <h1 class="mt-2 text-3xl font-black sm:text-4xl">Survey Kepuasan</h1>
            <p class="mt-3 max-w-2xl text-white/90">
                Berikan penilaian Anda terhadap operasional pertamanan, kondisi taman, dan portal {{ config('app.name') }}.
                Masukan Anda membantu kami meningkatkan pelayanan.
            </p>
        </div>
        <div class="relative -mb-1 text-[#f0fdf4]">
            <svg viewBox="0 0 1440 40" fill="currentColor" class="block w-full"><path d="M0,20 Q360,40 720,20 T1440,20 L1440,40 L0,40 Z"/></svg>
        </div>
    </section>
@endsection

@section('main_class', 'mx-auto max-w-3xl px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="relative -mt-4 pb-16">
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('survey.store') }}"
              class="space-y-6 rounded-2xl border border-violet-100 bg-white p-6 shadow-lg shadow-violet-100/50 sm:p-8">
            @csrf

            <div>
                <label for="kategori" class="mb-1 block text-sm font-semibold text-gray-700">Kategori Penilaian *</label>
                <select name="kategori" id="kategori" required
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                    <option value="">Pilih kategori</option>
                    @foreach (\App\Models\SurveyKepuasan::KATEGORI as $kategori)
                        <option value="{{ $kategori }}" @selected(old('kategori', $selectedKategori) === $kategori)>{{ $kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <p class="mb-2 text-sm font-semibold text-gray-700">Tingkat Kepuasan *</p>
                <input type="hidden" name="rating" id="rating" value="{{ old('rating') }}" required>
                <div class="flex flex-wrap gap-2" id="star-rating" role="radiogroup" aria-label="Penilaian bintang">
                    @foreach ([5, 4, 3, 2, 1] as $star)
                        <button type="button"
                                data-star="{{ $star }}"
                                class="star-btn flex flex-col items-center rounded-xl border border-gray-200 px-4 py-3 text-sm transition hover:border-violet-300 hover:bg-violet-50"
                                aria-label="{{ $star }} bintang">
                            <span class="text-2xl leading-none text-gray-300">★</span>
                            <span class="mt-1 text-[10px] font-bold text-gray-500">{{ \App\Models\SurveyKepuasan::ratingLabel($star) }}</span>
                        </button>
                    @endforeach
                </div>
                <p id="rating-hint" class="mt-2 text-xs text-gray-500">Pilih 1–5 bintang sesuai tingkat kepuasan Anda.</p>
            </div>

            <div>
                <label for="taman_id_search" class="mb-1 block text-sm font-semibold text-gray-700">Taman terkait (opsional)</label>
                <x-admin.taman-select
                    :tamans="$tamans"
                    :selected="old('taman_id', $selectedTamanId)"
                    :required="false"
                    :create-url="null"
                    empty-message="Belum ada data taman dalam sistem."
                    placeholder="Ketik nama taman untuk mencari..."
                    hint="Kosongkan jika tidak terkait taman tertentu."
                    input-class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500"
                />
            </div>

            <div>
                <label for="saran" class="mb-1 block text-sm font-semibold text-gray-700">Saran &amp; masukan (opsional)</label>
                <textarea name="saran" id="saran" rows="4" maxlength="2000"
                          placeholder="Ceritakan pengalaman atau saran perbaikan..."
                          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">{{ old('saran') }}</textarea>
            </div>

            <div>
                <label for="nama" class="mb-1 block text-sm font-semibold text-gray-700">Nama (opsional)</label>
                <input type="text" name="nama" id="nama" value="{{ old('nama') }}"
                       placeholder="Kosongkan jika ingin anonim"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit"
                        class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-violet-700">
                    Kirim Penilaian
                </button>
                <a href="{{ route('home') }}"
                   class="rounded-xl border border-gray-300 px-6 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        onPageReady(function () {
            const input = document.getElementById('rating');
            const hint = document.getElementById('rating-hint');
            const buttons = document.querySelectorAll('.star-btn');
            const labels = {
                1: 'Tidak Puas',
                2: 'Kurang Puas',
                3: 'Cukup',
                4: 'Puas',
                5: 'Sangat Puas',
            };

            function paintStars(value) {
                buttons.forEach(function (btn) {
                    const star = parseInt(btn.dataset.star, 10);
                    const active = star <= value;
                    btn.classList.toggle('border-violet-400', active);
                    btn.classList.toggle('bg-violet-50', active);
                    btn.classList.toggle('ring-2', active);
                    btn.classList.toggle('ring-violet-200', active);
                    btn.querySelector('span:first-child').classList.toggle('text-violet-500', active);
                    btn.querySelector('span:first-child').classList.toggle('text-gray-300', !active);
                });

                if (value) {
                    hint.textContent = 'Penilaian Anda: ' + value + '/5 — ' + (labels[value] || '');
                    hint.classList.add('text-violet-700', 'font-semibold');
                }
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const value = parseInt(btn.dataset.star, 10);
                    input.value = value;
                    paintStars(value);
                });
            });

            if (input.value) {
                paintStars(parseInt(input.value, 10));
            }
        });
    </script>
@endpush
