@props(['batch'])

@if ($batch->hasFotoBukti())
    <div class="mt-6">
        <h3 class="text-sm font-semibold text-gray-700">Foto bukti</h3>
        <div class="mt-2 flex flex-wrap gap-3">
            @foreach ($batch->buktiFotos as $foto)
                @if ($foto->url())
                    <a href="{{ $foto->url() }}" target="_blank" rel="noopener" class="block">
                        <img src="{{ $foto->url() }}" alt="Foto bukti stok" class="h-40 w-auto max-w-full rounded-lg border border-gray-200 object-cover shadow-sm">
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif
