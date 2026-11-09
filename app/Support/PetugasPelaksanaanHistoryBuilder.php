<?php

namespace App\Support;

use App\Models\PemeliharaanTaman;
use App\Models\PemangkasanProgres;
use App\Models\Petugas;
use Illuminate\Support\Collection;

class PetugasPelaksanaanHistoryBuilder
{
    /**
     * @return Collection<int, object{
     *     tanggal: \Illuminate\Support\Carbon|null,
     *     jenis: string,
     *     jenis_label: string,
     *     lokasi: string,
     *     tim: ?string,
     *     ringkasan: ?string,
     *     detail_url: ?string
     * }>
     */
    public function forPetugas(Petugas $petugas): Collection
    {
        $pemeliharaan = $petugas->pemeliharaanTamans()
            ->with('taman')
            ->orderByDesc('tanggal')
            ->get()
            ->map(function (PemeliharaanTaman $row) {
                $ringkasan = $row->progressSummaryLabel();
                $uraian = filled($row->uraian_pekerjaan) ? trim((string) $row->uraian_pekerjaan) : null;

                if ($uraian !== null) {
                    $ringkasan = $ringkasan !== null ? $ringkasan.' — '.$uraian : $uraian;
                }

                return (object) [
                    'tanggal' => $row->tanggal,
                    'jenis' => 'pemeliharaan',
                    'jenis_label' => 'Pemeliharaan rutin',
                    'lokasi' => $row->lokasiLabel(),
                    'tim' => $row->tim,
                    'ringkasan' => $ringkasan,
                    'detail_url' => route('admin.pemeliharaan-tamans.edit', $row),
                ];
            });

        $progres = $petugas->pemangkasanProgres()
            ->with(['pemangkasan.taman'])
            ->orderByDesc('tanggal')
            ->get()
            ->map(function (PemangkasanProgres $row) {
                $permohonan = $row->pemangkasan;
                $lokasi = $permohonan ? $permohonan->lokasiLabel() : '—';
                $ringkasan = $row->hari_ke !== null
                    ? 'Hari ke-'.$row->hari_ke
                    : null;

                if (filled($row->catatan)) {
                    $catatan = trim((string) $row->catatan);
                    $ringkasan = $ringkasan !== null ? $ringkasan.' — '.$catatan : $catatan;
                }

                $detailUrl = null;
                if ($permohonan !== null) {
                    $detailUrl = route('admin.pemangkasans.show', $permohonan);
                }

                return (object) [
                    'tanggal' => $row->tanggal,
                    'jenis' => 'permohonan',
                    'jenis_label' => 'Pelaksanaan permohonan',
                    'lokasi' => $lokasi,
                    'tim' => $row->tim,
                    'ringkasan' => $ringkasan,
                    'detail_url' => $detailUrl,
                ];
            });

        return $pemeliharaan
            ->concat($progres)
            ->sortByDesc(fn ($item) => $item->tanggal?->timestamp ?? 0)
            ->values();
    }
}
