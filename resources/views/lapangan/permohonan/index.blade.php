@extends('layouts.lapangan')

@section('title', 'Daftar Permohonan')
@section('header', 'Permohonan Operasional')

@section('content')
    <x-lapangan.back-link :href="route('lapangan.index')" label="Kembali ke menu utama" />

    <div class="lapangan-card mb-5 border-blue-200 bg-gradient-to-br from-blue-50 to-white p-5">
        <div class="flex items-start gap-3">
            <span class="text-3xl" aria-hidden="true">📋</span>
            <div>
                <p class="text-sm font-black text-blue-900">Apa yang perlu dilakukan?</p>
                <p class="mt-1 text-sm leading-relaxed text-blue-800">
                    Admin sudah membuat daftar permohonan dengan status awal <strong>Rencana</strong>.
                    Anda <strong>mulai pekerjaan</strong> (→ Diproses), isi <strong>progres per hari</strong>,
                    lalu <strong>konfirmasi selesai</strong> di bagian bawah form.
                </p>
            </div>
        </div>
    </div>

    <x-lapangan.step-guide
        title="Alur permohonan (3 langkah)"
        :steps="\App\Support\LapanganUi::permohonanSteps()"
    />

    @php
        $tabs = [
            'Rencana' => ['label' => 'Menunggu', 'emoji' => '📅', 'active' => 'bg-blue-600 text-white ring-2 ring-blue-300', 'idle' => 'bg-white text-blue-800 ring-1 ring-blue-200 hover:bg-blue-50'],
            'Diproses' => ['label' => 'Dikerjakan', 'emoji' => '🔧', 'active' => 'bg-amber-500 text-white ring-2 ring-amber-300', 'idle' => 'bg-white text-amber-800 ring-1 ring-amber-200 hover:bg-amber-50'],
            'Selesai' => ['label' => 'Selesai', 'emoji' => '✅', 'active' => 'bg-emerald-600 text-white ring-2 ring-emerald-300', 'idle' => 'bg-white text-emerald-800 ring-1 ring-emerald-200 hover:bg-emerald-50'],
        ];
        $currentTab = $statusFilter ?? 'Rencana';
    @endphp

    <div class="mb-5 grid grid-cols-3 gap-2">
        @foreach ($tabs as $value => $tab)
            <a href="{{ route('lapangan.permohonan.index', ['status' => $value]) }}"
               class="rounded-2xl px-2 py-3.5 text-center transition {{ $currentTab === $value ? 'lapangan-tab-active '.$tab['active'] : $tab['idle'] }}">
                <p class="text-lg" aria-hidden="true">{{ $tab['emoji'] }}</p>
                <p class="text-xl font-black">{{ $statusCounts[$value] ?? 0 }}</p>
                <p class="mt-0.5 text-[10px] font-bold uppercase tracking-wide">{{ $tab['label'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($permohonans as $permohonan)
            @php
                $isSelesai = $permohonan->status === 'Selesai';
                $isLate = ! $isSelesai && \App\Support\PemangkasanSchedule::isLate($permohonan);
                $statusClass = match ($permohonan->status) {
                    'Selesai' => 'bg-emerald-100 text-emerald-800',
                    'Diproses' => 'bg-amber-100 text-amber-800',
                    default => 'bg-blue-100 text-blue-800',
                };
                $cardClass = 'lapangan-card lapangan-card-hover block p-4 '
                    .($isLate ? 'border-red-200 bg-red-50/30' : '')
                    .($isSelesai ? '' : ' cursor-pointer');
            @endphp

            @if ($isSelesai)
                <div class="{{ $cardClass }}">
                    @include('lapangan.permohonan.partials.list-card-body', [
                        'permohonan' => $permohonan,
                        'statusClass' => $statusClass,
                        'isLate' => false,
                        'actionLabel' => $permohonan->tanggal_penyelesaian
                            ? 'Selesai '.$permohonan->tanggal_penyelesaian->format('d/m/Y')
                            : 'Selesai',
                        'actionMuted' => true,
                    ])
                </div>
            @else
                <a href="{{ route('lapangan.permohonan.edit', $permohonan) }}" class="{{ $cardClass }}">
                    @include('lapangan.permohonan.partials.list-card-body', [
                        'permohonan' => $permohonan,
                        'statusClass' => $statusClass,
                        'isLate' => $isLate,
                        'actionLabel' => 'Isi progres →',
                        'actionMuted' => false,
                    ])
                </a>
            @endif
        @empty
            <div class="lapangan-card px-6 py-14 text-center">
                <p class="text-4xl" aria-hidden="true">📭</p>
                <p class="mt-3 text-base font-bold text-gray-800">
                    Belum ada permohonan {{ strtolower($currentTab) }}
                </p>
                <p class="mt-2 text-sm text-gray-500">
                    @if ($currentTab === 'Rencana')
                        Permohonan baru dibuat oleh admin. Cek lagi nanti atau hubungi admin kantor.
                    @elseif ($currentTab === 'Diproses')
                        Belum ada pekerjaan yang sedang berjalan. Lihat tab Menunggu untuk mulai.
                    @else
                        Belum ada pekerjaan selesai. Isi progres sampai tuntas untuk melihat arsip di sini.
                    @endif
                </p>
            </div>
        @endforelse
    </div>
@endsection
