@php
    $fotoGroups = [
        'Sebelum' => ['foto_sebelum_1', 'foto_sebelum_2'],
        'Saat' => ['foto_saat_1', 'foto_saat_2'],
        'Sesudah' => ['foto_sesudah_1', 'foto_sesudah_2'],
    ];
    $entries = isset($progresEntries) ? $progresEntries : $permohonan->progres;
    $includeProgresPhotos = $includeProgresPhotos ?? true;
    $showTanggalCol = $entries->count() > 1;
    $timLabel = $sectionTitle ?? ($permohonan->pelaksanaLabel() ?: 'Operasional');
    $metaMuted = 'color:#6b7280;font-size:9px;';
@endphp

<h2 class="rekap-heading">{{ $timLabel }} ({{ $entries->count() }} progres)</h2>

@if ($entries->isEmpty())
    <p class="rekap-empty">Belum ada progres harian yang tercatat untuk permohonan ini.</p>
@else
    <table class="rekap-table">
        <thead>
            <tr>
                <th style="width:4%">No</th>
                @if ($showTanggalCol)
                    <th style="width:10%">Tanggal</th>
                @endif
                <th style="width:18%">Lokasi Pelaksanaan</th>
                <th style="width:26%">Sebelum Pelaksanaan</th>
                <th style="width:26%">Saat Pelaksanaan</th>
                <th style="width:26%">Sesudah Pelaksanaan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $index => $progres)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @if ($showTanggalCol)
                        <td>{{ \App\Support\OperasionalPelaksanaanTime::displayDateLong($progres->tanggal) }}</td>
                    @endif
                    <td>
                        {{ $permohonan->lokasiPelaksanaanPdfLabel() }}
                        @if ($progres->relationLoaded('petugas') && $progres->petugas->isNotEmpty())
                            <br><span style="{{ $metaMuted }}">Petugas: {{ $progres->petugas->pluck('nama')->implode(', ') }}</span>
                        @elseif ($progres->namaPengawas())
                            <br><span style="{{ $metaMuted }}">Pengawas: {{ $progres->namaPengawas() }}</span>
                        @endif
                        @if ($progres->jumlah_personil)
                            <br><span style="{{ $metaMuted }}">Personil: {{ $progres->jumlah_personil }}</span>
                        @endif
                        <br><span style="{{ $metaMuted }}">Progres: {{ \App\Support\PemangkasanSchedule::dailyProgressLabel($permohonan, $progres) }}</span>
                        @if ($progres->catatan)
                            <br><span style="{{ $metaMuted }}">{{ $progres->catatan }}</span>
                        @endif
                        @if ($progres->armadaPdfHtml())
                            <br><span style="{{ $metaMuted }}">Armada: {!! $progres->armadaPdfHtml() !!}</span>
                        @endif
                    </td>
                    @foreach ($fotoGroups as $groupLabel => $fields)
                        <td>
                            @if ($includeProgresPhotos)
                                @php $hasFoto = collect($fields)->contains(fn ($field) => $progres->fotoDompdfSrc($field)); @endphp
                                @if ($hasFoto)
                                    <div class="foto-group">
                                        <div class="foto-group-label">{{ $groupLabel }}</div>
                                        @foreach ($fields as $field)
                                            @if ($progres->fotoDompdfSrc($field))
                                                @php $size = $progres->fotoPdfSize($field, 115, 68); @endphp
                                                <div class="foto-wrap">
                                                    @if ($size)
                                                        <img src="{{ $progres->fotoDompdfSrc($field) }}"
                                                             alt="{{ $groupLabel }}"
                                                             width="{{ $size['width'] }}"
                                                             height="{{ $size['height'] }}">
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="rekap-empty">—</span>
                                @endif
                            @else
                                <span class="rekap-empty">—</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
