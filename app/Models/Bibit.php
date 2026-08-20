<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bibit extends Model
{
    public const JENIS = [
        'Semak / Tanaman Hias Pagar',
        'Semak / Tanaman Hias Bunga',
        'Semak Rendah / Tanaman Hias',
        'Tumbuhan Terna / Groundcover',
        'Tumbuhan Terna / Tanaman Hias',
        'Sukulen / Tanaman Hias',
        'Semak Rendah / Groundcover',
        'Perdu / Tanaman Merambat',
        'Terna / Groundcover',
        'Pohon Pelindung / Hias',
        'Terna / Tanaman Hias Daun',
        'Tanaman Merambat / Climber',
        'Semak / Tanaman Pagar/Bonsai',
        'Terna / Tanaman Hias Bunga',
        'Semak / Tanaman Hias Daun',
        'Pohon Pelindung / Peneduh',
        'Pohon Kecil / Semak Besar',
        'Pohon Peneduh / Pelindung',
        'Terna / Tanaman Obat & Hias',
        'Groundcover / Penutup Tanah',
        'Tanaman Juntai / Merambat',
        'Groundcover / Tanaman Hias Pembatas',
        'Semak Rendah / Tanaman Bunga',
        'Perdu Merambat / Tanaman Hias',
        'Semak / Tanaman Hias Daun & Bunga',
        'Semak / Perdu Hias',
        'Perdu / Semak Besar',
        'Pohon Kecil / Semak Pagar',
        'Semak Unik / Tanaman Hias',
        'Semak / Pandan Hias',
        'Sukulen / Semak Hias',
        'Palem / Tanaman Hias',
        'Pohon Pelindung / Peneduh Bunga',
        'Semak / Bonsai / Hias',
        'Pohon Kecil / Semak Besar Bunga',
        'Semak / Tanaman Pagar Ringan',
        'Pohon Pelindung / Kayu',
        'Pohon Kecil / Semak Bunga',
    ];

    /** @var array<string, string> */
    public const LEGACY_JENIS_MAP = [
        'Pohon' => 'Pohon Pelindung / Peneduh',
        'Tanaman Hias' => 'Terna / Tanaman Hias',
        'Semak' => 'Semak / Tanaman Hias Daun',
    ];

    public const SUMBER = ['Produksi', 'Pengadaan', 'Hibah'];

    protected $fillable = [
        'nama_tanaman',
        'nama_ilmiah',
        'jenis',
        'stok_tersedia',
        'sumber_bibit',
        'status_siap_tanam',
    ];

    protected function casts(): array
    {
        return [
            'stok_tersedia' => 'integer',
            'status_siap_tanam' => 'boolean',
        ];
    }

    public function keluars(): HasMany
    {
        return $this->hasMany(BibitKeluar::class);
    }

    public function masuks(): HasMany
    {
        return $this->hasMany(BibitMasuk::class);
    }

    /**
     * @return array<string, int>
     */
    public function stokPerSumber(): array
    {
        $totals = array_fill_keys(self::SUMBER, 0);

        $this->masuks
            ->where('sisa_stok', '>', 0)
            ->groupBy('sumber')
            ->each(function ($items, $sumber) use (&$totals) {
                if (array_key_exists($sumber, $totals)) {
                    $totals[$sumber] = (int) $items->sum('sisa_stok');
                }
            });

        return $totals;
    }

    public function stokSiapTanam(): int
    {
        return (int) $this->masuks
            ->where('sisa_stok', '>', 0)
            ->where('status_siap_tanam', true)
            ->sum('sisa_stok');
    }

    public function stokBelumSiap(): int
    {
        return (int) $this->masuks
            ->where('sisa_stok', '>', 0)
            ->where('status_siap_tanam', false)
            ->sum('sisa_stok');
    }

    public function namaDenganIlmiah(): string
    {
        if (! $this->nama_ilmiah) {
            return $this->nama_tanaman;
        }

        return $this->nama_tanaman.' ('.$this->nama_ilmiah.')';
    }
}
