<?php

namespace App\Support;

use App\Models\AlatSaranaOperasional;
use App\Models\PemangkasanProgresArmada;
use App\Models\PemeliharaanTamanArmada;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ArmadaUsageHistory
{
    /**
     * @return Collection<int, array{
     *     sumber: string,
     *     tanggal: ?Carbon,
     *     pekerjaan: string,
     *     lokasi: string,
     *     sopir: string,
     *     url: ?string,
     * }>
     */
    public function forAlat(AlatSaranaOperasional $alat): Collection
    {
        $pemeliharaan = PemeliharaanTamanArmada::query()
            ->where('alat_sarana_operasional_id', $alat->id)
            ->with(['pemeliharaanTaman.taman'])
            ->get()
            ->map(function (PemeliharaanTamanArmada $row) {
                $pemeliharaan = $row->pemeliharaanTaman;

                return [
                    'sumber' => 'Pemeliharaan Rutin',
                    'tanggal' => $pemeliharaan?->tanggal,
                    'pekerjaan' => filled($pemeliharaan?->uraian_pekerjaan)
                        ? (string) $pemeliharaan->uraian_pekerjaan
                        : 'Pemeliharaan rutin '.$pemeliharaan?->tim,
                    'lokasi' => $pemeliharaan?->lokasiLabel() ?? '—',
                    'sopir' => $row->sopir,
                    'url' => $pemeliharaan
                        ? route('admin.pemeliharaan-tamans.edit', $pemeliharaan)
                        : null,
                ];
            });

        $permohonan = PemangkasanProgresArmada::query()
            ->where('alat_sarana_operasional_id', $alat->id)
            ->with(['progres.pemangkasan.taman'])
            ->get()
            ->map(function (PemangkasanProgresArmada $row) {
                $progres = $row->progres;
                $permohonan = $progres?->pemangkasan;

                return [
                    'sumber' => 'Permohonan',
                    'tanggal' => $progres?->tanggal ?? $permohonan?->tanggal_eksekusi ?? $permohonan?->tanggal_permohonan,
                    'pekerjaan' => $permohonan?->jenis_layanan ?? 'Permohonan operasional',
                    'lokasi' => $permohonan?->lokasiLabel() ?? '—',
                    'sopir' => $row->sopir,
                    'url' => ($permohonan && $progres)
                        ? route('admin.pemangkasans.progres.edit', [
                            'pemangkasan' => $permohonan,
                            'pemangkasanProgres' => $progres,
                        ])
                        : null,
                ];
            });

        return $pemeliharaan
            ->concat($permohonan)
            ->sortByDesc(fn (array $entry) => $entry['tanggal']?->timestamp ?? 0)
            ->values();
    }
}
