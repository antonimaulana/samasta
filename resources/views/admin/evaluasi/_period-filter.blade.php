@props([
    'action',
    'mode',
    'bulan',
    'tahun',
    'dari',
    'sampai',
    'daftarBulan',
    'daftarTahun',
    'title' => 'Filter Periode',
    'description' => 'Pilih rentang waktu evaluasi',
    'accent' => 'green',
])

@php
    $accentClasses = match ($accent) {
        'violet' => ['border' => 'border-violet-100', 'header' => 'from-violet-50 to-purple-50', 'title' => 'text-violet-800', 'desc' => 'text-violet-700/80', 'radio' => 'text-violet-600 focus:ring-violet-500', 'btn' => 'bg-violet-600 hover:bg-violet-700', 'export' => 'border-violet-300 text-violet-800 hover:bg-violet-50'],
        'blue' => ['border' => 'border-blue-100', 'header' => 'from-blue-50 to-indigo-50', 'title' => 'text-blue-800', 'desc' => 'text-blue-700/80', 'radio' => 'text-blue-600 focus:ring-blue-500', 'btn' => 'bg-blue-600 hover:bg-blue-700', 'export' => 'border-blue-300 text-blue-800 hover:bg-blue-50'],
        'orange' => ['border' => 'border-orange-100', 'header' => 'from-orange-50 to-amber-50', 'title' => 'text-orange-800', 'desc' => 'text-orange-700/80', 'radio' => 'text-orange-600 focus:ring-orange-500', 'btn' => 'bg-orange-600 hover:bg-orange-700', 'export' => 'border-orange-300 text-orange-800 hover:bg-orange-50'],
        'rose' => ['border' => 'border-rose-100', 'header' => 'from-rose-50 to-pink-50', 'title' => 'text-rose-800', 'desc' => 'text-rose-700/80', 'radio' => 'text-rose-600 focus:ring-rose-500', 'btn' => 'bg-rose-600 hover:bg-rose-700', 'export' => 'border-rose-300 text-rose-800 hover:bg-rose-50'],
        'slate' => ['border' => 'border-slate-200', 'header' => 'from-slate-50 to-gray-50', 'title' => 'text-slate-800', 'desc' => 'text-slate-600/80', 'radio' => 'text-slate-600 focus:ring-slate-500', 'btn' => 'bg-slate-700 hover:bg-slate-800', 'export' => 'border-slate-300 text-slate-800 hover:bg-slate-50'],
        default => ['border' => 'border-green-100', 'header' => 'from-green-50 to-emerald-50', 'title' => 'text-green-800', 'desc' => 'text-green-700/80', 'radio' => 'text-green-600 focus:ring-green-500', 'btn' => 'bg-green-600 hover:bg-green-700', 'export' => 'border-green-300 text-green-800 hover:bg-green-50'],
    };
@endphp

<div class="mb-6 overflow-hidden rounded-2xl border {{ $accentClasses['border'] }} bg-white shadow-sm">
    <div class="border-b border-green-50 bg-gradient-to-r {{ $accentClasses['header'] }} px-6 py-4">
        <h2 class="text-lg font-semibold {{ $accentClasses['title'] }}">{{ $title }}</h2>
        <p class="mt-1 text-sm {{ $accentClasses['desc'] }}">{{ $description }}</p>
    </div>

    <form action="{{ $action }}" method="GET" class="space-y-4 p-6">
        {{ $filters ?? '' }}

        <div class="flex flex-wrap gap-4">
            <label class="flex cursor-pointer items-center gap-2">
                <input type="radio" name="mode" value="bulan" @checked($mode === 'bulan')
                       class="{{ $accentClasses['radio'] }}" onchange="this.form.submit()">
                <span class="text-sm font-medium text-gray-700">Per Bulan</span>
            </label>
            <label class="flex cursor-pointer items-center gap-2">
                <input type="radio" name="mode" value="periode" @checked($mode === 'periode')
                       class="{{ $accentClasses['radio'] }}" onchange="toggleEvaluasiPeriodeFields()">
                <span class="text-sm font-medium text-gray-700">Rentang Tanggal</span>
            </label>
        </div>

        <div id="evaluasi-filter-bulan" class="grid gap-4 sm:grid-cols-3 {{ $mode === 'periode' ? 'hidden' : '' }}">
            <div>
                <label for="bulan" class="mb-1 block text-sm font-medium text-gray-700">Bulan</label>
                <select name="bulan" id="bulan"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    @foreach ($daftarBulan as $num => $nama)
                        <option value="{{ $num }}" @selected($bulan == $num)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tahun" class="mb-1 block text-sm font-medium text-gray-700">Tahun</label>
                <select name="tahun" id="tahun"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    @foreach ($daftarTahun as $thn)
                        <option value="{{ $thn }}" @selected($tahun == $thn)>{{ $thn }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="evaluasi-filter-periode" class="grid gap-4 sm:grid-cols-2 {{ $mode === 'bulan' ? 'hidden' : '' }}">
            <div>
                <label for="dari" class="mb-1 block text-sm font-medium text-gray-700">Dari Tanggal</label>
                <input type="date" name="dari" id="dari" value="{{ $dari }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <div>
                <label for="sampai" class="mb-1 block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                <input type="date" name="sampai" id="sampai" value="{{ $sampai }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg px-5 py-2 text-sm font-medium text-white {{ $accentClasses['btn'] }}">
                Tampilkan Evaluasi
            </button>
            @if ($exportRoute ?? false)
                <a href="{{ $exportRoute }}"
                   class="inline-flex items-center gap-2 rounded-lg border bg-white px-5 py-2 text-sm font-semibold {{ $accentClasses['export'] }}">
                    📄 Export PDF
                </a>
            @endif
        </div>
    </form>
</div>

@once
    @push('scripts')
        <script>
            function toggleEvaluasiPeriodeFields() {
                const mode = document.querySelector('input[name="mode"]:checked')?.value;
                document.getElementById('evaluasi-filter-bulan')?.classList.toggle('hidden', mode === 'periode');
                document.getElementById('evaluasi-filter-periode')?.classList.toggle('hidden', mode === 'bulan');
            }
        </script>
    @endpush
@endonce
