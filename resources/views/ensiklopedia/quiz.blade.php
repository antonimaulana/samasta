@extends('layouts.public')

@section('title', 'Kuis Tanaman')

@section('hero')
    <section class="relative overflow-hidden bg-gradient-to-br from-violet-500 via-purple-500 to-indigo-600 text-white">
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <a href="{{ route('ensiklopedia.index') }}" class="mb-4 inline-flex text-sm text-white/80 hover:text-white">← Kembali ke Ensiklopedia</a>
            <div class="flex items-center gap-3">
                <span class="text-4xl">🧠</span>
                <div>
                    <h1 class="text-3xl font-black sm:text-4xl">Kuis Tanaman & Lingkungan</h1>
                    <p class="mt-2 text-white/90">{{ $totalPertanyaan }} pertanyaan — uji pengetahuan Anda tentang RTH, tanaman, dan pertamanan Batam</p>
                </div>
            </div>
        </div>
        <div class="relative -mb-1 text-[#f0fdf4]">
            <svg viewBox="0 0 1440 40" fill="currentColor" class="block w-full"><path d="M0,20 Q360,40 720,20 T1440,20 L1440,40 L0,40 Z"/></svg>
        </div>
    </section>
@endsection

@section('main_class', 'mx-auto max-w-3xl px-4 sm:px-6 lg:px-8')

@section('content')
    <div id="quiz-app" class="relative -mt-4 pb-12">
        {{-- Progress --}}
        <div id="quiz-progress-wrap" class="mb-6">
            <div class="mb-2 flex justify-between text-sm font-medium text-gray-600">
                <span>Pertanyaan <span id="quiz-current">1</span> / {{ $totalPertanyaan }}</span>
                <span id="quiz-score-label" class="hidden text-green-700">Skor: <span id="quiz-score">0</span></span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                <div id="quiz-progress-bar" class="h-full rounded-full bg-gradient-to-r from-lime-400 to-green-500 transition-all duration-300"
                     style="width: {{ $totalPertanyaan > 0 ? round(100 / $totalPertanyaan) : 0 }}%"></div>
            </div>
        </div>

        {{-- Questions --}}
        <div id="quiz-questions">
            @foreach ($pertanyaan as $index => $item)
                <div class="quiz-step {{ $index === 0 ? '' : 'hidden' }}" data-step="{{ $index }}">
                    <div class="rounded-2xl border border-green-100 bg-white p-6 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-green-500">Pertanyaan {{ $index + 1 }}</p>
                        <h2 class="mt-2 text-lg font-bold text-gray-900">{{ $item['pertanyaan'] }}</h2>
                        <div class="mt-5 space-y-2">
                            @foreach ($item['opsi'] as $opsiIndex => $opsi)
                                <button type="button"
                                        class="quiz-option w-full rounded-xl border border-gray-200 px-4 py-3 text-left text-sm font-medium text-gray-800 transition hover:border-green-400 hover:bg-green-50"
                                        data-correct="{{ $opsiIndex === $item['jawaban'] ? '1' : '0' }}"
                                        data-explanation="{{ $item['penjelasan'] }}">
                                    <span class="mr-2 font-bold text-green-600">{{ chr(65 + $opsiIndex) }}.</span>
                                    {{ $opsi }}
                                </button>
                            @endforeach
                        </div>
                        <div class="quiz-feedback mt-4 hidden rounded-xl p-4 text-sm"></div>
                        <button type="button"
                                class="quiz-next mt-4 hidden w-full rounded-xl bg-green-600 py-3 text-sm font-bold text-white hover:bg-green-700">
                            {{ $index + 1 === $totalPertanyaan ? 'Lihat Hasil' : 'Pertanyaan Berikutnya →' }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Result --}}
        <div id="quiz-result" class="hidden rounded-2xl border border-green-200 bg-white p-8 text-center shadow-lg">
            <p id="result-emoji" class="text-6xl">🌿</p>
            <h2 class="mt-4 text-2xl font-black text-gray-900">Kuis Selesai!</h2>
            <p class="mt-2 text-gray-600">Skor Anda</p>
            <p class="mt-2 text-5xl font-black text-green-600"><span id="result-score">0</span> / {{ $totalPertanyaan }}</p>
            <p id="result-message" class="mt-4 text-sm font-medium text-gray-700"></p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <button type="button" id="quiz-restart"
                        class="rounded-xl bg-green-600 px-6 py-3 text-sm font-bold text-white hover:bg-green-700">
                    Ulangi Kuis
                </button>
                <a href="{{ route('ensiklopedia.index') }}"
                   class="rounded-xl border border-green-300 px-6 py-3 text-sm font-bold text-green-700 hover:bg-green-50">
                    Kembali ke Ensiklopedia
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        onPageReady(function () {
            const total = {{ $totalPertanyaan }};
            let current = 0;
            let score = 0;
            let answered = false;

            const steps = document.querySelectorAll('.quiz-step');
            const progressBar = document.getElementById('quiz-progress-bar');
            const currentLabel = document.getElementById('quiz-current');
            const scoreLabel = document.getElementById('quiz-score-label');
            const scoreEl = document.getElementById('quiz-score');
            const resultPanel = document.getElementById('quiz-result');
            const questionsWrap = document.getElementById('quiz-questions');
            const progressWrap = document.getElementById('quiz-progress-wrap');

            function updateProgress() {
                currentLabel.textContent = current + 1;
                progressBar.style.width = Math.round(((current + 1) / total) * 100) + '%';
            }

            function showResult() {
                questionsWrap.classList.add('hidden');
                progressWrap.classList.add('hidden');
                resultPanel.classList.remove('hidden');

                document.getElementById('result-score').textContent = score;
                const pct = total > 0 ? (score / total) * 100 : 0;
                const emoji = document.getElementById('result-emoji');
                const msg = document.getElementById('result-message');

                if (pct >= 80) {
                    emoji.textContent = '🏆';
                    msg.textContent = 'Luar biasa! Anda paham betul tentang penghijauan dan pertamanan.';
                } else if (pct >= 50) {
                    emoji.textContent = '🌱';
                    msg.textContent = 'Bagus! Terus baca artikel ensiklopedia untuk memperdalam pengetahuan.';
                } else {
                    emoji.textContent = '📚';
                    msg.textContent = @json('Jangan menyerah — jelajahi Ensiklopedia '.config('app.name').' untuk belajar lebih lanjut.');
                }
            }

            steps.forEach(function (step, stepIndex) {
                const options = step.querySelectorAll('.quiz-option');
                const feedback = step.querySelector('.quiz-feedback');
                const nextBtn = step.querySelector('.quiz-next');

                options.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (answered) return;
                        answered = true;

                        const isCorrect = btn.dataset.correct === '1';
                        if (isCorrect) {
                            score++;
                            scoreEl.textContent = score;
                            scoreLabel.classList.remove('hidden');
                        }

                        options.forEach(function (opt) {
                            opt.disabled = true;
                            opt.classList.add('cursor-default');
                            if (opt.dataset.correct === '1') {
                                opt.classList.add('border-green-500', 'bg-green-100', 'text-green-900');
                            } else if (opt === btn && !isCorrect) {
                                opt.classList.add('border-red-400', 'bg-red-50');
                            }
                        });

                        feedback.classList.remove('hidden');
                        if (isCorrect) {
                            feedback.classList.add('bg-green-50', 'text-green-800', 'border', 'border-green-200');
                        } else {
                            feedback.classList.add('bg-amber-50', 'text-amber-900', 'border', 'border-amber-200');
                        }
                        feedback.innerHTML = (isCorrect ? '✅ Benar! ' : '💡 Belum tepat. ') + btn.dataset.explanation;
                        nextBtn.classList.remove('hidden');
                    });
                });

                nextBtn?.addEventListener('click', function () {
                    step.classList.add('hidden');
                    answered = false;

                    if (stepIndex + 1 >= total) {
                        showResult();
                        return;
                    }

                    current = stepIndex + 1;
                    steps[current].classList.remove('hidden');
                    updateProgress();
                });
            });

            document.getElementById('quiz-restart')?.addEventListener('click', function () {
                current = 0;
                score = 0;
                answered = false;
                scoreEl.textContent = '0';
                scoreLabel.classList.add('hidden');
                resultPanel.classList.add('hidden');
                questionsWrap.classList.remove('hidden');
                progressWrap.classList.remove('hidden');

                steps.forEach(function (step, i) {
                    step.classList.toggle('hidden', i !== 0);
                    step.querySelectorAll('.quiz-option').forEach(function (opt) {
                        opt.disabled = false;
                        opt.classList.remove('border-green-500', 'bg-green-100', 'text-green-900', 'border-red-400', 'bg-red-50', 'cursor-default');
                    });
                    step.querySelector('.quiz-feedback')?.classList.add('hidden');
                    step.querySelector('.quiz-next')?.classList.add('hidden');
                });
                updateProgress();
            });
        });
    </script>
@endpush
