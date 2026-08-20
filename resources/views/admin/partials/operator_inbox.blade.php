@if ($canWrite ?? auth()->user()?->canWrite())
    @php
        $inbox = $operator_inbox ?? ['aduan_baru' => collect(), 'jadwal_hari_ini' => collect()];
    @endphp
    <section class="mb-8 overflow-hidden rounded-2xl border border-sky-100 bg-white shadow-sm">
        <div class="border-b border-sky-50 bg-gradient-to-r from-sky-50 to-blue-50 px-5 py-4">
            <h3 class="font-bold text-gray-900">Antrian Kerja Hari Ini</h3>
            <p class="text-xs text-gray-500">Prioritas operasional untuk tim lapangan</p>
        </div>
        <div class="grid gap-4 p-5 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-100 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-gray-900">Aduan Baru</h4>
                    <a href="{{ route('admin.aduan-masyarakats.index', ['status' => 'Baru']) }}" class="text-xs font-semibold text-green-700 hover:underline">Semua →</a>
                </div>
                @forelse ($inbox['aduan_baru'] as $aduan)
                    <a href="{{ route('admin.aduan-masyarakats.show', $aduan) }}"
                       class="mb-2 block rounded-lg bg-gray-50 px-3 py-2 text-sm transition hover:bg-sky-50">
                        <p class="font-medium text-gray-900">{{ $aduan->nomor_aduan }}</p>
                        <p class="truncate text-xs text-gray-500">{{ $aduan->lokasi }} · {{ $aduan->jenis_aduan }}</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada aduan baru.</p>
                @endforelse
            </div>
            <div class="rounded-xl border border-gray-100 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-gray-900">Jadwal Hari Ini</h4>
                    <a href="{{ route('admin.pemangkasans.index', ['view' => 'hari_ini']) }}" class="text-xs font-semibold text-green-700 hover:underline">Semua →</a>
                </div>
                @forelse ($inbox['jadwal_hari_ini'] as $layanan)
                    <a href="{{ route('admin.pemangkasans.show', $layanan) }}"
                       class="mb-2 block rounded-lg bg-gray-50 px-3 py-2 text-sm transition hover:bg-sky-50">
                        <p class="font-medium text-gray-900">{{ $layanan->jenis_layanan }}</p>
                        <p class="truncate text-xs text-gray-500">{{ $layanan->lokasi_pohon }} · {{ $layanan->status }}</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada jadwal hari ini.</p>
                @endforelse
            </div>
        </div>
    </section>
@endif
