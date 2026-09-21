@php
    $items = collect($kinerja->fotoItems())->filter(fn (array $item) => filled($item['url']));
@endphp

@if ($items->isNotEmpty())
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-bold text-gray-900">Dokumentasi foto</h3>
        <p class="mt-1 text-xs text-gray-500">6 foto operasional (sebelum, saat, sesudah pelaksanaan)</p>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($items as $item)
                <a href="{{ $item['url'] }}" target="_blank" rel="noopener"
                   class="group block overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                    <img src="{{ $item['url'] }}" alt="{{ $item['label'] }}"
                         class="h-28 w-full object-cover transition group-hover:opacity-90 sm:h-32">
                    <p class="truncate px-2 py-1.5 text-[10px] font-medium text-gray-600">{{ $item['label'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
@elseif (collect(\App\Models\PemeliharaanTaman::fotoFieldKeys())->contains(fn (string $field) => filled($kinerja->{$field})))
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
        <p class="font-semibold">Foto tidak dapat ditampilkan</p>
        <p class="mt-1 text-xs leading-relaxed">
            Path foto ada di database, tetapi file tidak ditemukan di disk publik.
            Jalankan <code class="rounded bg-amber-100 px-1">php artisan storage:link</code> di server
            dan pastikan folder <code class="rounded bg-amber-100 px-1">storage/app/public/pemeliharaan-taman</code> berisi file upload.
        </p>
    </div>
@endif
