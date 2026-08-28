@extends('layouts.admin')

@section('title', 'Operasional Pertamanan')
@section('header', 'Operasional Pertamanan')

@section('content')
    @php
        $currentView = collect($viewOptions)->firstWhere('key', $view);
        $filterParams = array_filter([
            'view' => $view,
            'pelaksana' => $pelaksana,
            'jenis' => $jenis,
            'status' => request('status'),
            'search' => request('search'),
            'alert' => request('alert'),
        ]);
        $cards = [
            ['key' => 'semua', 'label' => 'Semua', 'icon' => '📂', 'bg' => 'bg-gray-50', 'text' => 'text-gray-800'],
            ['key' => 'hari_ini', 'label' => 'Hari Ini', 'icon' => '📅', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-800'],
            ['key' => 'besok', 'label' => 'Besok', 'icon' => '⏰', 'bg' => 'bg-blue-50', 'text' => 'text-blue-800'],
            ['key' => 'rencana', 'label' => 'Antrian Rencana', 'icon' => '📋', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-800'],
            ['key' => 'diproses', 'label' => 'Diproses', 'icon' => '🚧', 'bg' => 'bg-amber-50', 'text' => 'text-amber-800'],
            ['key' => 'terlambat', 'label' => 'Terlambat', 'icon' => '⚠️', 'bg' => 'bg-red-50', 'text' => 'text-red-800', 'danger' => true],
            ['key' => 'minggu', 'label' => 'Minggu Ini', 'icon' => '🗓️', 'bg' => 'bg-violet-50', 'text' => 'text-violet-800'],
        ];
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-600">Kelola dan pantau antrian jadwal operasional pertamanan di Batam.</p>
        <x-admin.can-write>
            <a href="{{ route('admin.pemangkasans.create') }}"
               class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                + Permohonan Baru
            </a>
        </x-admin.can-write>
    </div>

    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
        @foreach ($cards as $card)
            @php
                $active = $view === $card['key'];
                $countKey = $card['key'] === 'minggu' ? 'minggu_ini' : $card['key'];
                $hasDanger = ($card['danger'] ?? false) && ($counts[$countKey] ?? 0) > 0;
            @endphp
            <a href="{{ route('admin.pemangkasans.index', array_filter(['view' => $card['key'], 'pelaksana' => $pelaksana, 'jenis' => $jenis])) }}"
               class="rounded-xl border p-3 transition hover:shadow-sm {{ $active ? 'border-green-500 ring-2 ring-green-200 ' . $card['bg'] : ($hasDanger ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-white') }}">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-base">{{ $card['icon'] }}</span>
                    <span class="text-xl font-black {{ $active ? $card['text'] : ($hasDanger ? 'text-red-700' : 'text-gray-800') }}">{{ $counts[$countKey] }}</span>
                </div>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-wide {{ $active ? $card['text'] : 'text-gray-500' }}">{{ $card['label'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="mb-3">
        <h2 class="text-base font-bold text-gray-900">{{ $currentView['label'] ?? 'Operasional' }}</h2>
        <p class="text-sm text-gray-500">{{ $currentView['description'] ?? '' }}</p>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="view" value="{{ $view }}">
        <div class="min-w-[200px] flex-1 sm:max-w-md">
            <label for="search" class="mb-1 block text-xs font-medium text-gray-600">Cari</label>
            <input type="search" name="search" id="search" value="{{ request('search') }}"
                   placeholder="Cari lokasi, pelaksana, asal, status..."
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label for="pelaksana" class="mb-1 block text-xs font-medium text-gray-600">Tim Pelaksana</label>
            <select name="pelaksana" id="pelaksana"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <option value="">Semua tim</option>
                @foreach ($timList as $tim)
                    <option value="{{ $tim }}" @selected($pelaksana === $tim)>{{ $tim }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="jenis" class="mb-1 block text-xs font-medium text-gray-600">Jenis Operasional</label>
            <select name="jenis" id="jenis"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <option value="">Semua jenis</option>
                @foreach ($jenisList as $item)
                    <option value="{{ $item }}" @selected($jenis === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-medium text-gray-600">Status</label>
            <select name="status" id="status"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">
                <option value="">Semua status</option>
                @foreach (\App\Models\Pemangkasan::STATUS as $statusOption)
                    <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ $statusOption }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
            Terapkan
        </button>
        @if (request()->anyFilled(['search', 'pelaksana', 'jenis', 'status']))
            <a href="{{ route('admin.pemangkasans.index', ['view' => $view]) }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Reset filter
            </a>
        @endif
    </form>

    <x-admin.data-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jadwal</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Jenis</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Lokasi</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Asal</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Pelaksana</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Aksi</th>
            </tr>
        </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pemangkasans as $pemangkasan)
                        @php
                            $isLate = \App\Support\PemangkasanSchedule::isLate($pemangkasan);
                            $isActiveToday = \App\Support\PemangkasanSchedule::isActiveToday($pemangkasan);
                            $statusClass = match ($pemangkasan->status) {
                                'Selesai' => 'bg-emerald-100 text-emerald-800',
                                'Diproses' => 'bg-amber-100 text-amber-800',
                                default => 'bg-blue-100 text-blue-800',
                            };
                            $jenisClass = \App\Models\Pemangkasan::badgeClass($pemangkasan->jenis_layanan);
                            $detailQuery = $filterParams;
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isLate ? 'bg-red-50/40' : '' }}">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ \App\Support\PemangkasanSchedule::labelRentang($pemangkasan) }}</p>
                                <p class="text-xs text-gray-500">{{ \App\Support\PemangkasanSchedule::progressSummary($pemangkasan) }}</p>
                                @if ($isLate)
                                    <p class="text-xs font-medium text-red-600">Terlambat</p>
                                @elseif ($isActiveToday)
                                    <p class="text-xs font-medium text-emerald-700">Hari ini</p>
                                @elseif (\App\Support\PemangkasanSchedule::tanggalWithinSchedule($pemangkasan, today()->addDay()))
                                    <p class="text-xs font-medium text-blue-700">Besok</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $jenisClass }}">
                                    {{ $pemangkasan->jenis_layanan }}
                                </span>
                            </td>
                            <td class="max-w-xs px-4 py-3 font-medium">{{ $pemangkasan->lokasi_pohon }}</td>
                            <td class="max-w-[140px] truncate px-4 py-3 text-gray-600">{{ $pemangkasan->asal }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $pemangkasan->pelaksanaLabel() ?: '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($canWrite ?? auth()->user()?->canWrite())
                                    <form action="{{ route('admin.pemangkasans.update-status', $pemangkasan) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status"
                                                onchange="this.form.submit()"
                                                class="cursor-pointer rounded-full border-0 px-2.5 py-1 text-xs font-medium {{ $statusClass }} focus:ring-2 focus:ring-green-500">
                                            @foreach (\App\Models\Pemangkasan::STATUS as $status)
                                                <option value="{{ $status }}" @selected($pemangkasan->status === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">
                                        {{ $pemangkasan->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.pemangkasans.show', array_merge(['pemangkasan' => $pemangkasan], $detailQuery)) }}"
                                       class="rounded border border-gray-300 px-3 py-1 text-gray-700 hover:bg-gray-50">
                                        Detail
                                    </a>
                                    <x-admin.can-write>
                                        <a href="{{ route('admin.pemangkasans.edit', $pemangkasan) }}"
                                           class="rounded border border-blue-300 px-3 py-1 text-blue-700 hover:bg-blue-50">
                                            Edit
                                        </a>
                                    </x-admin.can-write>
                                    <x-admin.can-delete>
                                        <form action="{{ route('admin.pemangkasans.destroy', $pemangkasan) }}" method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus data operasional ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded border border-red-300 px-3 py-1 text-red-700 hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </x-admin.can-delete>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                Tidak ada operasional pada tampilan ini.
                                @if ($canWrite ?? auth()->user()?->canWrite())
                                    <a href="{{ route('admin.pemangkasans.create') }}" class="text-green-700 underline">Tambah operasional</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
        @if ($pemangkasans->hasPages())
            <x-slot:footer>
                {{ $pemangkasans->withQueryString()->links() }}
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endsection
