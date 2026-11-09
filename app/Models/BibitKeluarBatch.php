<?php

namespace App\Models;

use App\Casts\OperasionalDateTime;
use App\Models\Concerns\HasBibitStokBuktiFotos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class BibitKeluarBatch extends Model
{
    use HasBibitStokBuktiFotos;

    public const PERUNTUKAN_PERMINTAAN = 'Permintaan Masyarakat/Instansi';

    public const PERUNTUKAN_AFKIR = 'Afkir/Tanaman Mati';

    public const PERUNTUKAN = [
        'Penanaman Awal',
        'Pergantian',
        'Perbanyakan',
        self::PERUNTUKAN_PERMINTAAN,
        self::PERUNTUKAN_AFKIR,
    ];

    protected $fillable = [
        'tanggal_keluar',
        'peruntukan',
        'taman_id',
        'lokasi',
        'dokumen_pendukung',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_keluar' => OperasionalDateTime::class,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BibitKeluar::class, 'bibit_keluar_batch_id');
    }

    public function taman(): BelongsTo
    {
        return $this->belongsTo(Taman::class);
    }

    public function lokasiLabel(): string
    {
        if (filled($this->lokasi)) {
            return (string) $this->lokasi;
        }

        if ($this->taman_id) {
            $taman = $this->relationLoaded('taman')
                ? $this->taman
                : $this->taman()->first(['id', 'nama_taman', 'alamat']);

            if ($taman) {
                return PemeliharaanTaman::lokasiLabelFromTaman($taman);
            }
        }

        return '—';
    }

    public function totalJumlah(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items->sum('jumlah');
        }

        return (int) $this->items()->sum('jumlah');
    }

    public function sumberUnikLabel(): string
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get(['sumber']);

        $sumber = $items->pluck('sumber')->filter()->unique()->values();

        return $sumber->isEmpty() ? '—' : $sumber->join(', ');
    }

    public function requiresDokumenPendukung(): bool
    {
        return $this->peruntukan === self::PERUNTUKAN_PERMINTAAN;
    }

    public function requiresLokasi(): bool
    {
        return $this->peruntukan !== self::PERUNTUKAN_AFKIR;
    }

    public static function isAfkirPeruntukan(?string $peruntukan): bool
    {
        return $peruntukan === self::PERUNTUKAN_AFKIR;
    }

    public function dokumenPendukungUrl(): ?string
    {
        if (! filled($this->dokumen_pendukung)) {
            return null;
        }

        $path = str_replace('\\', '/', (string) $this->dokumen_pendukung);

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
