@extends('layouts.admin')

@section('title', $dpa->nama_dpa)
@section('header', $dpa->nama_dpa)

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('admin.dpa.tahun-anggarans.show', $dpa->tahunAnggaran) }}"
               class="text-sm text-green-700 hover:text-green-900">&larr; Tahun {{ $dpa->tahunAnggaran->tahun }}</a>
            <p class="mt-1 text-sm text-gray-600">{{ $dpa->subKegiatanLabel() }}</p>
            @if ($dpa->nomor_dpa)
                <p class="text-xs text-gray-500">No. DPA: {{ $dpa->nomor_dpa }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.dpa.paket-pekerjaans.import', $dpa) }}"
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Import CSV
            </a>
            <a href="{{ route('admin.dpa.paket-pekerjaans.create', $dpa) }}"
               class="rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
                + Paket Pekerjaan
            </a>
            <a href="{{ route('admin.dpa.dpas.edit', $dpa) }}"
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Edit DPA
            </a>
        </div>
    </div>

    @if (session('import_result'))
        @php $result = session('import_result'); @endphp
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            Import: {{ number_format($result['imported'] ?? 0) }} berhasil, {{ number_format($result['skipped'] ?? 0) }} dilewati.
        </div>
    @endif

    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Paket Pekerjaan</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Kode RUP</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Pagu</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tahap</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Penyedia</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($dpa->paketPekerjaans as $paket)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">
                        <p class="font-medium text-gray-900">{{ $paket->nama_paket }}</p>
                        @if ($paket->nomor_rekening)
                            <p class="text-xs text-gray-500">{{ $paket->nomor_rekening }} · {{ $paket->nama_rekening }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $paket->kode_rup ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">Rp {{ number_format($paket->pagu_anggaran, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                            {{ $paket->tahapLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $paket->penyedia?->nama ?: '—' }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <a href="{{ route('admin.dpa.paket-pekerjaans.show', $paket) }}"
                           class="text-green-700 hover:text-green-900">Monitoring</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        Belum ada paket pekerjaan. Tambah manual atau import CSV.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-admin.data-table>
@endsection
