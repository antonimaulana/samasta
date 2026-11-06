<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bibit extends Model
{
    /** @var list<string> */
    public const JENIS = [
        'Palem',
        'Penutup Tanah',
        'Perdu',
        'Perdu / Pagar',
        'Perdu / Pohon',
        'Perdu / Semak',
        'Perdu Rambat',
        'Pohon',
        'Semak',
        'Semak / Perdu',
        'Sukulen',
        'Tanaman Merambat',
        'Tanaman Rias',
        'Terna',
    ];

    /** @var array<string, string> Singkatan / label CSV → kategori standar */
    public const JENIS_ALIASES = [
        'Penutup Ta' => 'Penutup Tanah',
        'Perdu / Pa' => 'Perdu / Pagar',
        'Perdu / Po' => 'Perdu / Pohon',
        'Perdu / Se' => 'Perdu / Semak',
        'Perdu Ram' => 'Perdu Rambat',
        'Semak / Pe' => 'Semak / Perdu',
        'Tanaman M' => 'Tanaman Merambat',
        'Tanaman R' => 'Tanaman Rias',
    ];

    /** @var array<string, string> Kategori standar sebelumnya → kategori terbaru */
    public const STANDARD_JENIS_REVISION_MAP = [
        'Tanaman Pagar' => 'Perdu / Pagar',
        'Perdu / Palem' => 'Perdu / Pagar',
    ];

    /** @var array<string, string> */
    public const LEGACY_JENIS_MAP = [
        'Pohon' => 'Pohon',
        'Tanaman Hias' => 'Terna',
        'Semak' => 'Semak',
    ];

    /** @var array<string, string> Label jenis detail lama → kategori standar */
    public const DETAILED_JENIS_MAP = [
        'Semak / Tanaman Hias Pagar' => 'Perdu / Pagar',
        'Semak / Tanaman Hias Bunga' => 'Semak',
        'Semak Rendah / Tanaman Hias' => 'Semak',
        'Tumbuhan Terna / Groundcover' => 'Penutup Tanah',
        'Tumbuhan Terna / Tanaman Hias' => 'Terna',
        'Sukulen / Tanaman Hias' => 'Sukulen',
        'Semak Rendah / Groundcover' => 'Penutup Tanah',
        'Perdu / Tanaman Merambat' => 'Perdu Rambat',
        'Terna / Groundcover' => 'Penutup Tanah',
        'Pohon Pelindung / Hias' => 'Pohon',
        'Terna / Tanaman Hias Daun' => 'Terna',
        'Tanaman Merambat / Climber' => 'Tanaman Merambat',
        'Semak / Tanaman Pagar/Bonsai' => 'Perdu / Pagar',
        'Terna / Tanaman Hias Bunga' => 'Terna',
        'Semak / Tanaman Hias Daun' => 'Semak',
        'Pohon Pelindung / Peneduh' => 'Pohon',
        'Pohon Kecil / Semak Besar' => 'Perdu / Semak',
        'Pohon Peneduh / Pelindung' => 'Pohon',
        'Terna / Tanaman Obat & Hias' => 'Terna',
        'Groundcover / Penutup Tanah' => 'Penutup Tanah',
        'Tanaman Juntai / Merambat' => 'Tanaman Merambat',
        'Groundcover / Tanaman Hias Pembatas' => 'Penutup Tanah',
        'Semak Rendah / Tanaman Bunga' => 'Semak',
        'Perdu Merambat / Tanaman Hias' => 'Perdu Rambat',
        'Semak / Tanaman Hias Daun & Bunga' => 'Semak',
        'Semak / Perdu Hias' => 'Semak / Perdu',
        'Perdu / Semak Besar' => 'Perdu / Semak',
        'Pohon Kecil / Semak Pagar' => 'Perdu / Pohon',
        'Semak Unik / Tanaman Hias' => 'Semak',
        'Semak / Pandan Hias' => 'Semak',
        'Sukulen / Semak Hias' => 'Sukulen',
        'Palem / Tanaman Hias' => 'Palem',
        'Pohon Pelindung / Peneduh Bunga' => 'Pohon',
        'Semak / Bonsai / Hias' => 'Semak',
        'Pohon Kecil / Semak Besar Bunga' => 'Perdu / Semak',
        'Semak / Tanaman Pagar Ringan' => 'Perdu / Pagar',
        'Pohon Pelindung / Kayu' => 'Pohon',
        'Pohon Kecil / Semak Bunga' => 'Perdu / Semak',
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

    public static function resolveJenis(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        foreach (self::JENIS as $jenis) {
            if (strcasecmp($value, $jenis) === 0) {
                return $jenis;
            }
        }

        foreach (self::DETAILED_JENIS_MAP as $old => $canonical) {
            if (strcasecmp($old, $value) === 0) {
                return $canonical;
            }
        }

        foreach (self::LEGACY_JENIS_MAP as $old => $canonical) {
            if (strcasecmp($old, $value) === 0) {
                return $canonical;
            }
        }

        foreach (self::STANDARD_JENIS_REVISION_MAP as $old => $canonical) {
            if (strcasecmp($old, $value) === 0) {
                return $canonical;
            }
        }

        foreach (self::JENIS_ALIASES as $alias => $canonical) {
            if (strcasecmp($alias, $value) === 0) {
                return $canonical;
            }
        }

        return null;
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
