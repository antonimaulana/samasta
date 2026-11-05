@extends('layouts.admin')

@section('title', 'Pesan Tama — Beranda')
@section('header', 'Pesan Tama — Beranda')

@section('content')
    <p class="mb-6 max-w-2xl text-sm text-gray-600">
        Maskot <strong>Tama</strong> menampilkan pop-up saat pengunjung scroll ke bagian beranda yang sesuai. Matikan pop-up secara global atau per bagian di bawah.
    </p>

    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-900">Pop-up Tama di beranda</p>
            <p class="mt-1 text-xs text-gray-600">
                Status:
                @if ($setting->popups_enabled)
                    <span class="font-semibold text-green-700">Aktif</span> — pengunjung dapat melihat pop-up (sesuai pesan yang aktif).
                @else
                    <span class="font-semibold text-gray-700">Nonaktif</span> — tidak ada pop-up di halaman beranda.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($setting->popups_enabled)
                <form action="{{ route('admin.beranda-mascot-tips.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="popups_enabled" value="0">
                    <button type="submit"
                            class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-bold text-red-800 transition hover:bg-red-100">
                        Nonaktifkan pop-up
                    </button>
                </form>
            @else
                <form action="{{ route('admin.beranda-mascot-tips.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="popups_enabled" value="1">
                    <button type="submit"
                            class="rounded-xl bg-green-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-green-700">
                        Aktifkan pop-up
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <form action="{{ route('admin.beranda-mascot-tips.activate-all') }}" method="POST">
            @csrf
            <button type="submit"
                    class="rounded-lg border border-green-200 bg-white px-3 py-2 text-xs font-semibold text-green-800 hover:bg-green-50">
                Aktifkan semua pesan
            </button>
        </form>
        <form action="{{ route('admin.beranda-mascot-tips.deactivate-all') }}" method="POST">
            @csrf
            <button type="submit"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                Nonaktifkan semua pesan
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-800">Bagian beranda</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-800">Judul pesan</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-800">Status</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-800">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($tips as $tip)
                    <tr class="hover:bg-gray-50/80">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $tip->section_label }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $tip->title }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($tip->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Aktif</span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                @if ($tip->is_active)
                                    <form action="{{ route('admin.beranda-mascot-tips.toggle', $tip) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.beranda-mascot-tips.toggle', $tip) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="rounded-lg border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-800 hover:bg-green-100">
                                            Aktifkan
                                        </button>
                                    </form>
                                @endif
                                <x-admin.table-action-link variant="edit" :href="route('admin.beranda-mascot-tips.edit', $tip)" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
