@extends('layouts.admin')

@section('title', 'Armada')
@section('header', 'Armada')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Inventaris armada operasional Bidang Pertamanan dan Pemakaman.</p>
        <x-admin.can-write>
            <a href="{{ route('admin.armadas.create') }}"
               class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                + Tambah Armada
            </a>
        </x-admin.can-write>
    </div>

    <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-3 p-5">
            <div>
                <label for="jenis" class="mb-1 block text-xs font-medium text-gray-600">Jenis armada</label>
                <select name="jenis" id="jenis"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">Semua jenis</option>
                    @foreach (\App\Models\Armada::ARMADA_JENIS as $jenis)
                        <option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="kondisi" class="mb-1 block text-xs font-medium text-gray-600">Kondisi</label>
                <select name="kondisi" id="kondisi"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">Semua kondisi</option>
                    @foreach (\App\Models\Armada::KONDISI as $kondisi)
                        <option value="{{ $kondisi }}" @selected(request('kondisi') === $kondisi)>{{ $kondisi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[200px] flex-1 sm:max-w-xs">
                <label for="search" class="mb-1 block text-xs font-medium text-gray-600">Cari</label>
                <input type="search" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Nama, plat, keterangan..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
            <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900">Terapkan</button>
            @if (request()->anyFilled(['jenis', 'kondisi', 'search']))
                <a href="{{ route('admin.armadas.index') }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama Armada</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jenis</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Jumlah</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Kondisi</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Keterangan</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('admin.armadas.show', $item) }}"
                           class="text-green-800 hover:text-green-900 hover:underline">
                            {{ $item->nama }}
                        </a>
                        @if ($item->no_plat)
                            <p class="mt-0.5 text-xs text-gray-500">Plat: {{ $item->no_plat }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $item->jenis }}</td>
                    <td class="px-4 py-3 text-center text-gray-700">{{ number_format($item->jumlah) }}</td>
                    <td class="px-4 py-3">
                        @php
                            $kondisiClass = match ($item->kondisi) {
                                'Baik' => 'bg-emerald-100 text-emerald-800',
                                'Rusak Ringan' => 'bg-amber-100 text-amber-800',
                                'Rusak Berat' => 'bg-orange-100 text-orange-800',
                                default => 'bg-red-100 text-red-800',
                            };
                        @endphp
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $kondisiClass }}">{{ $item->kondisi }}</span>
                    </td>
                    <td class="max-w-xs px-4 py-3 text-gray-600">{{ $item->keterangan ? Str::limit($item->keterangan, 80) : '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.armadas.show', $item) }}"
                               class="rounded border border-green-300 px-3 py-1 text-green-700 hover:bg-green-50">Riwayat</a>
                            <x-admin.can-write>
                                <a href="{{ route('admin.armadas.edit', $item) }}"
                                   class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">Edit</a>
                            </x-admin.can-write>
                            <x-admin.can-delete>
                                <form action="{{ route('admin.armadas.destroy', $item) }}" method="POST"
                                      onsubmit="return confirm('Hapus data armada ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50">Hapus</button>
                                </form>
                            </x-admin.can-delete>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                        Belum ada data armada.
                        @if (auth()->user()?->canWrite())
                            <a href="{{ route('admin.armadas.create') }}" class="text-green-700 underline">Tambah sekarang</a>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($items->hasPages())
            <x-slot:footer>
                {{ $items->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>

    <section class="mt-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 bg-gray-50/80 px-5 py-4">
            <h3 class="font-bold text-gray-900">Riwayat Penggunaan Armada Terbaru</h3>
            <p class="text-xs text-gray-500">Entri terbaru dari pemeliharaan rutin dan operasional permohonan</p>
        </div>
        <x-admin.data-table>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tanggal</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Armada</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Sumber</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Pekerjaan</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Lokasi</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Sopir</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($recentUsage as $entry)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                            {{ $entry['tanggal']?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ($entry['armada_url'])
                                <a href="{{ $entry['armada_url'] }}" class="font-medium text-green-800 hover:underline">{{ $entry['armada'] }}</a>
                            @else
                                {{ $entry['armada'] }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs font-semibold',
                                'bg-blue-100 text-blue-800' => $entry['sumber'] === 'Permohonan',
                                'bg-emerald-100 text-emerald-800' => $entry['sumber'] === 'Pemeliharaan Rutin',
                            ])>
                                {{ $entry['sumber'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ Str::limit($entry['pekerjaan'], 60) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ Str::limit($entry['lokasi'], 40) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $entry['sopir'] ?: '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($entry['url'])
                                <a href="{{ $entry['url'] }}" class="text-sm font-medium text-green-700 hover:text-green-800">Lihat</a>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                            Belum ada riwayat penggunaan armada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.data-table>
    </section>
@endsection
