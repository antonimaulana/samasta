@extends('layouts.admin')

@section('title', 'Profil Taman')
@section('header', 'Profil Taman')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <x-admin.rth-page-toolbar :back-url="route('admin.tamans.index')" back-label="← Kembali ke Kelola Taman">
            <x-slot:actions>
                <a href="{{ route('tamans.show', $taman) }}" target="_blank" rel="noopener"
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Lihat Halaman Publik
                </a>
                <x-admin.taman-ar-qr-modal :taman="$taman" />
                <x-admin.can-write>
                    <a href="{{ route('admin.tamans.edit', $taman) }}"
                       class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-100">
                        Edit Taman
                    </a>
                </x-admin.can-write>
            </x-slot:actions>
        </x-admin.rth-page-toolbar>

        <x-admin.rth-section title="Profil RTH / Taman">
            <x-taman-gallery
                :taman="$taman"
                variant="admin"
                :show-header="false"
                nested
                class="mb-6" />

            <x-admin.rth-detail-table>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <x-admin.rth-detail-row label="Nama Taman">{{ $taman->nama_taman }}</x-admin.rth-detail-row>
                    <x-admin.rth-detail-row label="Kategori">
                        <x-admin.taman-kategori-badge :kategori="$taman->kategori" />
                    </x-admin.rth-detail-row>
                    <x-admin.rth-detail-row label="Luasan">{{ number_format($taman->luasan, 0, ',', '.') }} m²</x-admin.rth-detail-row>
                    <x-admin.rth-detail-row label="Wilayah">
                        @if ($taman->kelurahan)
                            {{ $taman->kelurahan->nama }}, {{ $taman->kelurahan->kecamatan->nama }}
                        @else
                            —
                        @endif
                    </x-admin.rth-detail-row>
                    <x-admin.rth-detail-row label="Koordinat">
                        @if ($taman->latitude && $taman->longitude)
                            {{ $taman->latitude }}, {{ $taman->longitude }}
                        @else
                            —
                        @endif
                    </x-admin.rth-detail-row>
                    <x-admin.rth-detail-row label="Alamat" :align-top="true">{{ $taman->alamat ?: '—' }}</x-admin.rth-detail-row>
                    <x-admin.rth-detail-row label="Deskripsi" :align-top="true">{{ $taman->deskripsi ?: '—' }}</x-admin.rth-detail-row>
                    <x-admin.rth-detail-row label="Waktu Pemutakhiran">
                        <div class="flex flex-wrap items-center gap-3">
                            <span>{{ $taman->data_verified_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?? '—' }}</span>
                            <x-admin.taman-status-data-badge :status="$taman->status_data" />
                        </div>
                    </x-admin.rth-detail-row>
                </tbody>
            </x-admin.rth-detail-table>
        </x-admin.rth-section>

        <x-admin.rth-section title="Data Pembangunan">
            <x-admin.rth-detail-table>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <x-admin.rth-detail-row label="Tahun Pembangunan">{{ $taman->tahun_pembangunan ?? '—' }}</x-admin.rth-detail-row>
                    <x-admin.rth-detail-row label="Nilai Pembangunan">
                        @if ($taman->nilai_pembangunan)
                            Rp {{ number_format($taman->nilai_pembangunan, 0, ',', '.') }}
                        @else
                            —
                        @endif
                    </x-admin.rth-detail-row>
                    <x-admin.rth-detail-row label="Kontraktor">{{ $taman->kontraktor ?: '—' }}</x-admin.rth-detail-row>
                    <x-admin.rth-detail-row label="Konsultan Perencana">{{ $taman->konsultan_perencana ?: '—' }}</x-admin.rth-detail-row>
                </tbody>
            </x-admin.rth-detail-table>
        </x-admin.rth-section>

        @if ($taman->fasilitas_items !== [])
            <x-admin.rth-section
                title="Fasilitas & Kondisi"
                :description="count($taman->fasilitas_items).' fasilitas tercatat'">
                <x-admin.rth-detail-table>
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Fasilitas</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($taman->fasilitas_items as $item)
                            <tr>
                                <td class="px-4 py-3 text-gray-900">{{ $item['nama'] }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $kondisiClass = match ($item['kondisi']) {
                                            'Baik' => 'bg-green-100 text-green-800',
                                            'Rusak Ringan' => 'bg-amber-100 text-amber-800',
                                            default => 'bg-red-100 text-red-800',
                                        };
                                    @endphp
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $kondisiClass }}">
                                        {{ $item['kondisi'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-admin.rth-detail-table>
            </x-admin.rth-section>
        @endif

        <x-admin.rth-section
            title="Riwayat Pemeliharaan"
            :description="$kinerjas->total().' catatan operasional'">
            <x-admin.data-table>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tim</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Uraian Pekerjaan</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($kinerjas as $kinerja)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $kinerja->tanggal->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">{{ $kinerja->tim }}</span>
                            </td>
                            <td class="max-w-xs px-4 py-3 text-sm text-gray-700">
                                {{ $kinerja->uraian_pekerjaan ? Str::limit($kinerja->uraian_pekerjaan, 80) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('admin.pemeliharaan-tamans.export-pdf-operasional', $kinerja) }}"
                                   class="rounded-lg border border-red-200 bg-white px-3 py-1 text-sm font-medium text-red-700 hover:bg-red-50">
                                    PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">
                                Belum ada riwayat pemeliharaan untuk taman ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($kinerjas->hasPages())
                    <x-slot:footer>
                        {{ $kinerjas->links() }}
                    </x-slot:footer>
                @endif
            </x-admin.data-table>
        </x-admin.rth-section>
    </div>
@endsection
