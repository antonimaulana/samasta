@props([
    'bibit',
    'layout' => 'stack',
    'pdf' => false,
])

@if ($layout === 'inline')
    {{ $bibit->nama_tanaman }}@if ($bibit->nama_ilmiah) <span class="italic text-gray-500">({{ $bibit->nama_ilmiah }})</span>@endif
@elseif ($pdf)
    <strong>{{ $bibit->nama_tanaman }}</strong>@if ($bibit->nama_ilmiah)<br><em style="font-size:10px;color:#666;">{{ $bibit->nama_ilmiah }}</em>@endif
@else
    <p @class(['font-medium text-gray-900' => ! ($attributes->has('plain') ?? false)])>{{ $bibit->nama_tanaman }}</p>
    @if ($bibit->nama_ilmiah)
        <p class="mt-0.5 text-xs italic text-gray-500">{{ $bibit->nama_ilmiah }}</p>
    @endif
@endif
