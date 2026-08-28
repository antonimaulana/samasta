@extends('layouts.admin')

@section('title', 'Survey Kepuasan')
@section('header', 'Survey Kepuasan Masyarakat')

@section('content')
    <div class="mb-6 overflow-hidden rounded-2xl border border-violet-100 bg-white shadow-sm">
        <div class="border-b border-violet-50 bg-gradient-to-r from-violet-50 to-fuchsia-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-violet-900">Filter Periode</h2>
            <p class="mt-1 text-sm text-violet-700/80">Laporan penilaian kepuasan masyarakat · {{ $labelPeriode }}</p>
        </div>

        <form action="{{ route('admin.survey-kepuasan.index') }}" method="GET" class="space-y-4 p-6">
            <div class="flex flex-wrap gap-4">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="radio" name="mode" value="bulan" @checked($mode === 'bulan')
                           class="text-violet-600 focus:ring-violet-500" onchange="this.form.submit()">
                    <span class="text-sm font-medium text-gray-700">Per Bulan</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="radio" name="mode" value="periode" @checked($mode === 'periode')
                           class="text-violet-600 focus:ring-violet-500" onchange="togglePeriodeFields()">
                    <span class="text-sm font-medium text-gray-700">Rentang Tanggal</span>
                </label>
            </div>

            <div id="filter-bulan" class="grid gap-4 sm:grid-cols-3 {{ $mode === 'periode' ? 'hidden' : '' }}">
                <div>
                    <label for="bulan" class="mb-1 block text-sm font-medium text-gray-700">Bulan</label>
                    <select name="bulan" id="bulan"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        @foreach ($daftarBulan as $num => $nama)
                            <option value="{{ $num }}" @selected($bulan == $num)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tahun" class="mb-1 block text-sm font-medium text-gray-700">Tahun</label>
                    <select name="tahun" id="tahun"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        @foreach ($daftarTahun as $thn)
                            <option value="{{ $thn }}" @selected($tahun == $thn)>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="filter-periode" class="grid gap-4 sm:grid-cols-2 {{ $mode === 'bulan' ? 'hidden' : '' }}">
                <div>
                    <label for="dari" class="mb-1 block text-sm font-medium text-gray-700">Dari Tanggal</label>
                    <input type="date" name="dari" id="dari" value="{{ $dari }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                </div>
                <div>
                    <label for="sampai" class="mb-1 block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                    <input type="date" name="sampai" id="sampai" value="{{ $sampai }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="kategori" class="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
                    <select name="kategori" id="kategori"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        <option value="">Semua kategori</option>
                        @foreach (\App\Models\SurveyKepuasan::KATEGORI as $kategori)
                            <option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>{{ $kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="rating" class="mb-1 block text-sm font-medium text-gray-700">Rating</label>
                    <select name="rating" id="rating"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        <option value="">Semua rating</option>
                        @foreach (range(5, 1) as $star)
                            <option value="{{ $star }}" @selected(request('rating') == $star)>{{ $star }} — {{ \App\Models\SurveyKepuasan::ratingLabel($star) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="search" class="mb-1 block text-sm font-medium text-gray-700">Cari</label>
                    <input type="search" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Nama, saran..."
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                </div>
            </div>

            <button type="submit"
                    class="rounded-lg bg-violet-600 px-5 py-2 text-sm font-medium text-white hover:bg-violet-700">
                Terapkan Filter
            </button>
        </form>
    </div>

    {{-- Ringkasan --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Rata-rata Skor</p>
            <p class="mt-2 text-3xl font-black text-violet-700">{{ number_format($summary['average'], 1) }}<span class="text-lg text-gray-400">/5</span></p>
            <p class="mt-1 text-xs text-gray-500">{{ number_format($summary['total']) }} respons</p>
        </div>

        @foreach ($summary['by_kategori'] as $row)
            @if ($row['total'] > 0)
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">{{ \App\Models\SurveyKepuasan::kategoriLabel($row['kategori']) }}</p>
                    <p class="mt-2 text-2xl font-black text-gray-800">{{ number_format($row['average'], 1) }}<span class="text-sm text-gray-400">/5</span></p>
                    <p class="mt-1 text-xs text-gray-500">{{ $row['total'] }} respons</p>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Distribusi bintang --}}
    @if ($summary['total'] > 0)
        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-bold text-gray-800">Distribusi Penilaian</h3>
            <div class="space-y-2">
                @foreach (range(5, 1) as $star)
                    @php
                        $count = $summary['distribution'][$star] ?? 0;
                        $percent = $summary['total'] > 0 ? ($count / $summary['total']) * 100 : 0;
                    @endphp
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-16 font-bold text-violet-700">{{ $star }} ★</span>
                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-violet-500" style="width: {{ $percent }}%"></div>
                        </div>
                        <span class="w-10 text-right text-gray-600">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabel --}}
    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tanggal</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Kategori</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Rating</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Taman</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Saran</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($surveys as $survey)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">{{ $survey->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-800">{{ \App\Models\SurveyKepuasan::kategoriLabel($survey->kategori) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-violet-700">{{ $survey->rating }}/5</span>
                                <span class="text-xs text-gray-500">{{ \App\Models\SurveyKepuasan::ratingLabel($survey->rating) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $survey->taman?->nama_taman ?? '—' }}</td>
                            <td class="px-4 py-3 max-w-xs">
                                <p class="truncate text-gray-600" title="{{ $survey->saran }}">{{ $survey->saran ?: '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $survey->nama ?: 'Anonim' }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-admin.can-delete>
                                <form action="{{ route('admin.survey-kepuasan.destroy', $survey) }}" method="POST"
                                      onsubmit="return confirm('Hapus data survey ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50">Hapus</button>
                                </form>
                                </x-admin.can-delete>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                Belum ada data survey untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
        @if ($surveys->hasPages())
            <x-slot:footer>
                {{ $surveys->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endsection

@push('scripts')
    <script>
        function togglePeriodeFields() {
            const mode = document.querySelector('input[name="mode"]:checked')?.value;
            document.getElementById('filter-bulan').classList.toggle('hidden', mode === 'periode');
            document.getElementById('filter-periode').classList.toggle('hidden', mode !== 'periode');
        }
    </script>
@endpush
