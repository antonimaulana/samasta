<?php

namespace App\Support;

use App\Models\Armada;
use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemangkasanProgresArmada;
use App\Models\PemeliharaanTaman;
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
     *     pdf_url: ?string,
     * }>
     */
    public function forArmada(Armada $armada): Collection
    {
        $pemeliharaan = PemeliharaanTamanArmada::query()
            ->where('armada_id', $armada->id)
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
                    'pdf_url' => $this->pdfUrlForPemeliharaan($pemeliharaan),
                ];
            });

        $permohonan = PemangkasanProgresArmada::query()
            ->where('armada_id', $armada->id)
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
                    'pdf_url' => $this->pdfUrlForPermohonanProgres($permohonan, $progres),
                ];
            });

        return $pemeliharaan
            ->concat($permohonan)
            ->sortByDesc(fn (array $entry) => $entry['tanggal']?->timestamp ?? 0)
            ->values();
    }

    /**
     * @return Collection<int, array{
     *     armada: string,
     *     armada_jenis: ?string,
     *     armada_url: ?string,
     *     sumber: string,
     *     tanggal: ?Carbon,
     *     pekerjaan: string,
     *     lokasi: string,
     *     sopir: string,
     *     pdf_url: ?string,
     * }>
     */
    public function recent(int $limit = 20): Collection
    {
        $pemeliharaan = PemeliharaanTamanArmada::query()
            ->with(['armada', 'pemeliharaanTaman.taman'])
            ->whereHas('armada', fn ($query) => $query->armadaInventory())
            ->get()
            ->map(function (PemeliharaanTamanArmada $row) {
                $pemeliharaan = $row->pemeliharaanTaman;
                $armada = $row->armada;

                return [
                    'armada' => $armada?->nama ?? '—',
                    'armada_jenis' => $armada?->jenis,
                    'armada_url' => $armada
                        ? route('admin.armadas.show', $armada)
                        : null,
                    'sumber' => 'Pemeliharaan Rutin',
                    'tanggal' => $pemeliharaan?->tanggal,
                    'pekerjaan' => filled($pemeliharaan?->uraian_pekerjaan)
                        ? (string) $pemeliharaan->uraian_pekerjaan
                        : 'Pemeliharaan rutin '.$pemeliharaan?->tim,
                    'lokasi' => $pemeliharaan?->lokasiLabel() ?? '—',
                    'sopir' => $row->sopir,
                    'pdf_url' => $this->pdfUrlForPemeliharaan($pemeliharaan),
                ];
            });

        $permohonan = PemangkasanProgresArmada::query()
            ->with(['armada', 'progres.pemangkasan.taman'])
            ->whereHas('armada', fn ($query) => $query->armadaInventory())
            ->get()
            ->map(function (PemangkasanProgresArmada $row) {
                $progres = $row->progres;
                $permohonan = $progres?->pemangkasan;
                $armada = $row->armada;

                return [
                    'armada' => $armada?->nama ?? '—',
                    'armada_jenis' => $armada?->jenis,
                    'armada_url' => $armada
                        ? route('admin.armadas.show', $armada)
                        : null,
                    'sumber' => 'Permohonan',
                    'tanggal' => $progres?->tanggal ?? $permohonan?->tanggal_eksekusi ?? $permohonan?->tanggal_permohonan,
                    'pekerjaan' => $permohonan?->jenis_layanan ?? 'Permohonan operasional',
                    'lokasi' => $permohonan?->lokasiLabel() ?? '—',
                    'sopir' => $row->sopir,
                    'pdf_url' => $this->pdfUrlForPermohonanProgres($permohonan, $progres),
                ];
            });

        return $pemeliharaan
            ->concat($permohonan)
            ->sortByDesc(fn (array $entry) => $entry['tanggal']?->timestamp ?? 0)
            ->take($limit)
            ->values();
    }

    private function pdfUrlForPemeliharaan(?PemeliharaanTaman $pemeliharaan): ?string
    {
        if (! $pemeliharaan) {
            return null;
        }

        return route('admin.pemeliharaan-tamans.export-pdf-operasional', $pemeliharaan);
    }

    private function pdfUrlForPermohonanProgres(?Pemangkasan $permohonan, ?PemangkasanProgres $progres): ?string
    {
        if (! $permohonan || ! $progres) {
            return null;
        }

        return route('admin.pemangkasans.export-pdf-progres', [
            'pemangkasan' => $permohonan,
            'pemangkasanProgres' => $progres,
        ]);
    }
}
