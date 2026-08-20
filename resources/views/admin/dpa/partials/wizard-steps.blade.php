@props([
    'step' => 1,
    'tahunAnggaran' => null,
    'subKegiatanLabel' => null,
])

@php
    $steps = [
        1 => ['label' => 'Pilih Tahun Anggaran', 'route' => route('admin.dpa.dashboard')],
        2 => ['label' => 'Pilih Sub Kegiatan', 'route' => $tahunAnggaran ? route('admin.dpa.tahun-anggarans.show', $tahunAnggaran) : null],
        3 => ['label' => 'Input & Import DPA/Paket', 'route' => null],
    ];
@endphp

<div class="mb-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-green-700">Menu Monitoring DPA</p>
            <h2 class="mt-1 text-lg font-semibold text-gray-900">{{ \App\Support\DpaMonitoring::KEGIATAN_UTAMA }}</h2>
            @if ($tahunAnggaran && $subKegiatanLabel)
                <p class="mt-1 text-sm text-gray-600">Tahun {{ $tahunAnggaran->tahun }} · {{ $subKegiatanLabel }}</p>
            @elseif ($tahunAnggaran)
                <p class="mt-1 text-sm text-gray-600">Tahun Anggaran {{ $tahunAnggaran->tahun }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2 text-xs">
            <a href="{{ route('admin.dpa.penyedias.index') }}"
               class="rounded-lg border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-50">Daftar Penyedia</a>
            <a href="{{ route('admin.dpa.document-templates.index') }}"
               class="rounded-lg border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-50">Template Dokumen</a>
            @if ($step > 1)
                <a href="{{ route('admin.dpa.tahun-anggarans.create') }}"
                   class="rounded-lg border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-50">+ Tahun Baru</a>
            @endif
        </div>
    </div>

    <ol class="mt-6 grid gap-3 sm:grid-cols-3">
        @foreach ($steps as $number => $info)
            @php
                $isActive = $step === $number;
                $isDone = $step > $number;
            @endphp
            <li class="relative rounded-xl border px-4 py-3 {{ $isActive ? 'border-green-500 bg-green-50' : ($isDone ? 'border-green-200 bg-white' : 'border-gray-200 bg-gray-50') }}">
                <div class="flex items-start gap-3">
                    <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $isActive ? 'bg-green-600 text-white' : ($isDone ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600') }}">
                        @if ($isDone)
                            ✓
                        @else
                            {{ $number }}
                        @endif
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-wide {{ $isActive ? 'text-green-700' : 'text-gray-500' }}">Langkah {{ $number }}</p>
                        @if ($isDone && $info['route'])
                            <a href="{{ $info['route'] }}" class="mt-0.5 block text-sm font-semibold text-green-800 hover:underline">{{ $info['label'] }}</a>
                        @else
                            <p class="mt-0.5 text-sm font-semibold {{ $isActive ? 'text-gray-900' : 'text-gray-600' }}">{{ $info['label'] }}</p>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
</div>
