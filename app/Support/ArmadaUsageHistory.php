<?php

namespace App\Support;

use App\Models\AlatSaranaOperasional;
use App\Models\PemangkasanArmada;
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

        $permohonan = PemangkasanArmada::query()
            ->where('alat_sarana_operasional_id', $alat->id)
            ->with(['pemangkasan.taman'])
            ->get()
            ->map(function (PemangkasanArmada $row) {
                $permohonan = $row->pemangkasan;

                return [
                    'sumber' => 'Permohonan',
                    'tanggal' => $permohonan?->tanggal_eksekusi ?? $permohonan?->tanggal_permohonan,
                    'pekerjaan' => $permohonan?->jenis_layanan ?? 'Permohonan operasional',
                    'lokasi' => $permohonan?->lokasiLabel() ?? '—',
                    'sopir' => $row->sopir,
                    'url' => $permohonan
                        ? route('admin.pemangkasans.show', $permohonan)
                        : null,
                ];
            });

        return $pemeliharaan
            ->concat($permohonan)
            ->sortByDesc(fn (array $entry) => $entry['tanggal']?->timestamp ?? 0)
            ->values();
    }
}
