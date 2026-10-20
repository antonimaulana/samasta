<div class="kuis-row rounded-2xl border border-violet-100 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-bold uppercase tracking-wider text-violet-600">Soal #{{ (int) $index + 1 }}</p>
        <button type="button" class="remove-kuis-row text-sm text-red-600 hover:underline">Hapus</button>
    </div>
    <textarea name="kuis[{{ $index }}][pertanyaan]" rows="2" required class="mt-3 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">{{ $soal['pertanyaan'] ?? '' }}</textarea>
    <div class="mt-3 grid gap-2 sm:grid-cols-2">
        @foreach (range(0, 3) as $oi)
            <input type="text" name="kuis[{{ $index }}][opsi][{{ $oi }}]" value="{{ $soal['opsi'][$oi] ?? '' }}" required
                   class="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Opsi {{ $oi + 1 }}">
        @endforeach
    </div>
    <div class="mt-3 grid gap-3 sm:grid-cols-4">
        <div>
            <label class="text-xs text-gray-500">Index jawaban benar (0–3)</label>
            <input type="number" min="0" max="3" name="kuis[{{ $index }}][jawaban]" value="{{ $soal['jawaban'] ?? 0 }}" required
                   class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
        </div>
        <div class="sm:col-span-3">
            <label class="text-xs text-gray-500">Penjelasan setelah jawab</label>
            <input type="text" name="kuis[{{ $index }}][penjelasan]" value="{{ $soal['penjelasan'] ?? '' }}" required
                   class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
        </div>
    </div>
</div>
