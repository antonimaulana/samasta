<?php

namespace App\Support;

use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use Illuminate\Support\Carbon;

class RthArProfileBuilder
{
    /**
     * @return array{
     *     taman: Taman,
     *     title: string,
     *     subtitle: string,
     *     location_label: string,
     *     luasan: int,
     *     luasan_formatted: string,
     *     data_freshness: array{is_fresh: bool, label: string, emoji: string, detail: string|null},
     *     kondisi: array{is_terawat: bool, label: string, emoji: string},
     *     maintenance: array{date: Carbon|null, date_label: string, uraian_pekerjaan: string|null, progres: int|null, summary_label: string},
     *     fasilitas: array{items: list<array{nama: string, kondisi: string}>, count: int, label: string},
     *     ar_model_url: string,
     *     poster_url: string|null,
     *     ar_scan_url: string,
     *     web_detail_url: string,
     * }
     */
    public function build(Taman $taman): array
    {
        $taman->loadMissing(['kelurahan.kecamatan', 'images']);

        $dataFreshDays = config('simtaman.ar.data_fresh_days', 90);
        $maintenanceFreshDays = config('simtaman.ar.maintenance_fresh_days', 60);

        $verifiedAt = $taman->data_verified_at;
        $isDataFresh = $verifiedAt !== null
            && $verifiedAt->greaterThanOrEqualTo(now()->subDays($dataFreshDays));

        $latestMaintenance = PemeliharaanTaman::query()
            ->where('taman_id', $taman->id)
            ->orderByDesc('tanggal')
            ->first();

        $isTerawat = $latestMaintenance !== null
            && $latestMaintenance->tanggal->greaterThanOrEqualTo(now()->subDays($maintenanceFreshDays));

        $fasilitasItems = $taman->fasilitas_items;

        return [
            'taman' => $taman,
            'title' => $taman->nama_taman,
            'subtitle' => $this->buildSubtitle($taman),
            'location_label' => $this->buildLocationLabel($taman),
            'luasan' => (int) $taman->luasan,
            'luasan_formatted' => number_format((int) $taman->luasan, 0, ',', '.'),
            'data_freshness' => [
                'is_fresh' => $isDataFresh,
                'label' => $isDataFresh ? 'Diperbarui' : 'Kedaluwarsa',
                'emoji' => $isDataFresh ? '🟢' : '🔴',
                'detail' => $verifiedAt?->timezone(config('app.timezone'))->format('d M Y H:i'),
            ],
            'kondisi' => [
                'is_terawat' => $isTerawat,
                'label' => $isTerawat ? 'Terawat' : 'Perlu Perhatian',
                'emoji' => $isTerawat ? '🟢' : '🟠',
            ],
            'maintenance' => $this->buildMaintenanceSummary($latestMaintenance),
            'fasilitas' => [
                'items' => $fasilitasItems,
                'count' => count($fasilitasItems),
                'label' => $fasilitasItems !== []
                    ? count($fasilitasItems).' fasilitas tersedia'
                    : 'Belum ada data fasilitas',
            ],
            'ar_model_url' => (string) config('simtaman.ar.model_url'),
            'poster_url' => $taman->foto_url,
            'ar_scan_url' => route('rth.ar-scan', $taman),
            'web_detail_url' => route('tamans.show', $taman),
        ];
    }

    private function buildSubtitle(Taman $taman): string
    {
        $parts = array_filter([
            $taman->kategori,
            $taman->kelurahan?->kecamatan?->nama,
        ]);

        return $parts !== [] ? implode(' · ', $parts) : 'RTH Kota Batam';
    }

    private function buildLocationLabel(Taman $taman): string
    {
        if ($taman->kelurahan) {
            return $taman->kelurahan->nama.', '.$taman->kelurahan->kecamatan->nama;
        }

        return $taman->alamat ?: 'Kota Batam';
    }

    /**
     * @return array{
     *     date: Carbon|null,
     *     date_label: string,
     *     uraian_pekerjaan: string|null,
     *     progres: int|null,
     *     summary_label: string,
     * }
     */
    private function buildMaintenanceSummary(?PemeliharaanTaman $maintenance): array
    {
        if ($maintenance === null) {
            return [
                'date' => null,
                'date_label' => 'Belum tercatat',
                'uraian_pekerjaan' => null,
                'progres' => null,
                'summary_label' => 'Belum tercatat',
            ];
        }

        $dateLabel = $maintenance->tanggal->timezone(config('app.timezone'))->format('d M Y');
        $uraian = filled($maintenance->uraian_pekerjaan)
            ? trim($maintenance->uraian_pekerjaan)
            : 'Uraian belum diisi';
        $progres = $maintenance->persentase_progres;
        $progresLabel = $progres !== null ? $progres.'% progres' : 'Progres belum diisi';

        return [
            'date' => $maintenance->tanggal,
            'date_label' => $dateLabel,
            'uraian_pekerjaan' => filled($maintenance->uraian_pekerjaan) ? trim($maintenance->uraian_pekerjaan) : null,
            'progres' => $progres,
            'summary_label' => $dateLabel.' - '.$uraian.' - '.$progresLabel,
        ];
    }
}
