<?php

namespace App\Support;

use App\Models\AduanMasyarakat;
use App\Models\Bibit;
use App\Models\Pemangkasan;
use App\Models\Taman;

class OperationalAlertService
{
    /**
     * @return list<array{key: string, label: string, description: string, count: int, severity: string, url: string, samples: list<string>}>
     */
    public function all(): array
    {
        return [
            $this->bibitLowStock(),
            $this->aduanOverdue(),
            $this->layananStuck(),
            $this->tamanIncomplete(),
        ];
    }

    /**
     * @return list<array{key: string, label: string, description: string, count: int, severity: string, url: string, samples: list<string>}>
     */
    public function active(): array
    {
        return array_values(array_filter($this->all(), fn (array $alert) => $alert['count'] > 0));
    }

    public function totalCount(): int
    {
        return (int) collect($this->active())->sum('count');
    }

    /**
     * @return array{key: string, label: string, description: string, count: int, severity: string, url: string, samples: list<string>}
     */
    private function bibitLowStock(): array
    {
        $threshold = config('alerts.bibit.minimum_stock');
        $bibits = Bibit::query()
            ->where('stok_tersedia', '<', $threshold)
            ->orderBy('stok_tersedia')
            ->get(['nama_tanaman', 'stok_tersedia']);

        return [
            'key' => 'bibit_low_stock',
            'label' => 'Stok Bibit Rendah',
            'description' => "Varietas dengan stok di bawah {$threshold} unit",
            'count' => $bibits->count(),
            'severity' => 'warning',
            'url' => route('admin.bibits.index', ['alert' => 'low_stock']),
            'samples' => $bibits->take(3)->map(
                fn (Bibit $bibit) => "{$bibit->nama_tanaman} ({$bibit->stok_tersedia} unit)"
            )->all(),
        ];
    }

    /**
     * @return array{key: string, label: string, description: string, count: int, severity: string, url: string, samples: list<string>}
     */
    private function aduanOverdue(): array
    {
        $days = config('alerts.aduan.unreviewed_days');
        $aduans = AduanMasyarakat::query()
            ->where('status', config('alerts.aduan.unreviewed_status'))
            ->where('created_at', '<=', now()->subDays($days))
            ->latest()
            ->get(['nomor_aduan', 'lokasi', 'created_at']);

        return [
            'key' => 'aduan_overdue',
            'label' => 'Aduan Belum Ditinjau',
            'description' => "Aduan baru lebih dari {$days} hari belum ditinjau",
            'count' => $aduans->count(),
            'severity' => 'danger',
            'url' => route('admin.aduan-masyarakats.index', ['alert' => 'overdue']),
            'samples' => $aduans->take(3)->map(
                fn (AduanMasyarakat $aduan) => "{$aduan->nomor_aduan} · {$aduan->lokasi} ({$aduan->created_at->diffForHumans()})"
            )->all(),
        ];
    }

    /**
     * @return array{key: string, label: string, description: string, count: int, severity: string, url: string, samples: list<string>}
     */
    private function layananStuck(): array
    {
        $days = config('alerts.layanan.diproses_days');
        $status = config('alerts.layanan.stuck_status');
        $layanans = Pemangkasan::query()
            ->where('status', $status)
            ->where('updated_at', '<=', now()->subDays($days))
            ->latest('updated_at')
            ->get(['lokasi_pohon', 'jenis_layanan', 'updated_at']);

        return [
            'key' => 'layanan_stuck',
            'label' => 'Operasional Terlambat',
            'description' => "Status Diproses lebih dari {$days} hari belum selesai",
            'count' => $layanans->count(),
            'severity' => 'warning',
            'url' => route('admin.pemangkasans.index', ['alert' => 'stuck']),
            'samples' => $layanans->take(3)->map(
                fn (Pemangkasan $layanan) => "{$layanan->jenis_layanan} · {$layanan->lokasi_pohon}"
            )->all(),
        ];
    }

    /**
     * @return array{key: string, label: string, description: string, count: int, severity: string, url: string, samples: list<string>}
     */
    private function tamanIncomplete(): array
    {
        $tamans = $this->incompleteTamansQuery()
            ->orderBy('nama_taman')
            ->get(['nama_taman', 'status_data']);

        return [
            'key' => 'taman_incomplete',
            'label' => 'Data Taman Belum Lengkap',
            'description' => 'Taman dengan status data Belum Lengkap',
            'count' => $tamans->count(),
            'severity' => 'info',
            'url' => route('admin.tamans.index', ['alert' => 'incomplete']),
            'samples' => $tamans->take(3)->map(
                fn (Taman $taman) => "{$taman->nama_taman} ({$taman->status_data_text})"
            )->all(),
        ];
    }

    public function incompleteTamansQuery()
    {
        return $this->filterIncompleteTamans(Taman::query());
    }

    public function filterIncompleteTamans($query)
    {
        return $query->where('status_data', Taman::STATUS_DATA_BELUM_LENGKAP);
    }

    public static function clearCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('admin_operational_alerts');
    }
}
