<?php

namespace App\Models;

use App\Casts\OperasionalDateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PemeliharaanTaman extends Model
{
    protected $table = 'pemeliharaan_tamans';

    public const FOTO_FIELDS = [
        'foto_sebelum_1' => 'Sebelum Pelaksanaan 1',
        'foto_sebelum_2' => 'Sebelum Pelaksanaan 2',
        'foto_saat_1' => 'Saat Pelaksanaan 1',
        'foto_saat_2' => 'Saat Pelaksanaan 2',
        'foto_sesudah_1' => 'Sesudah Pelaksanaan 1',
        'foto_sesudah_2' => 'Sesudah Pelaksanaan 2',
    ];

    public const TIM_ARMADA = 'Tim Armada';

    public const JENIS_ARMADA = [
        'Dump Truck',
        'Truck',
        'Crane',
    ];

    /** @deprecated Gunakan timNames() */
    public const TIM = [
        'Tim Wilayah 1',
        'Tim Wilayah 2',
        'Tim Wilayah 3',
        'Tim Wilayah 4',
        'Tim Nursery',
        'Tim Armada',
    ];

    /**
     * @return list<string>
     */
    public static function timNames(): array
    {
        if (TimPelaksana::query()->exists()) {
            return TimPelaksana::activeNames();
        }

        return self::TIM;
    }

    protected $fillable = [
        'tanggal',
        'tim',
        'taman_id',
        'lokasi_pelaksanaan',
        'jumlah_personil',
        'hari_ke',
        'total_hari',
        'persentase_progres',
        'uraian_pekerjaan',
        'foto_sebelum_1',
        'foto_sebelum_2',
        'foto_saat_1',
        'foto_saat_2',
        'foto_sesudah_1',
        'foto_sesudah_2',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => OperasionalDateTime::class,
            'jumlah_personil' => 'integer',
            'hari_ke' => 'integer',
            'total_hari' => 'integer',
            'persentase_progres' => 'integer',
        ];
    }

    public function armadas(): HasMany
    {
        return $this->hasMany(PemeliharaanTamanArmada::class)->orderBy('urutan');
    }

    public function petugas(): BelongsToMany
    {
        return $this->belongsToMany(Petugas::class, 'pemeliharaan_taman_petugas')->orderBy('nama');
    }

    public function petugasLabel(): string
    {
        if ($this->relationLoaded('petugas') && $this->petugas->isNotEmpty()) {
            return $this->petugas->pluck('nama')->implode(', ');
        }

        return $this->jumlah_personil ? number_format((int) $this->jumlah_personil).' personil' : '—';
    }

    public function isTimArmada(): bool
    {
        return $this->tim === self::TIM_ARMADA;
    }

    public function hariProgressLabel(): ?string
    {
        if ($this->hari_ke === null || $this->total_hari === null) {
            return null;
        }

        return $this->hari_ke.' / '.$this->total_hari.' hari';
    }

    public function progressSummaryLabel(): ?string
    {
        $hari = $this->hariProgressLabel();

        if ($hari !== null && $this->persentase_progres !== null) {
            return $hari.' ('.$this->persentase_progres.'% progres)';
        }

        if ($hari !== null) {
            return $hari;
        }

        if ($this->persentase_progres !== null) {
            return $this->persentase_progres.'% progres';
        }

        return null;
    }

    public function namaPengawas(): ?string
    {
        return TimPelaksana::query()
            ->where('nama', $this->tim)
            ->value('nama_pengawas');
    }

    public function lokasiKategoriLabel(): string
    {
        if ($this->taman_id === null) {
            return 'Permintaan Masyarakat';
        }

        return $this->taman?->kategori ?? '—';
    }

    public function lokasiPelaksanaanPdfLabel(): string
    {
        return '('.$this->lokasiKategoriLabel().') '.$this->lokasi_pelaksanaan;
    }

    public function armadaPdfHtml(): ?string
    {
        if ($this->armadas->isEmpty()) {
            return null;
        }

        return $this->armadas
            ->map(fn (PemeliharaanTamanArmada $armada) => $armada->pdfEntryHtml())
            ->implode(', ');
    }

    public function taman(): BelongsTo
    {
        return $this->belongsTo(Taman::class);
    }

    public static function lokasiLabelFromTaman(Taman $taman): string
    {
        $label = $taman->nama_taman;

        if ($taman->alamat) {
            $label .= ' — '.$taman->alamat;
        }

        return $label;
    }

    public function lokasiLabel(): string
    {
        if ($this->taman_id) {
            $taman = $this->relationLoaded('taman')
                ? $this->taman
                : $this->taman()->first(['id', 'nama_taman', 'alamat']);

            if ($taman) {
                return self::lokasiLabelFromTaman($taman);
            }
        }

        return (string) ($this->lokasi_pelaksanaan ?: '—');
    }

    /**
     * @return list<string>
     */
    public static function fotoFieldKeys(): array
    {
        return array_keys(self::FOTO_FIELDS);
    }

    public function fotoUrl(string $field): ?string
    {
        $path = $this->{$field};

        if (! $path) {
            return null;
        }

        return asset('storage/'.$path);
    }

    public function fotoBase64(string $field): ?string
    {
        $path = $this->{$field};

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($path));
    }

    /**
     * Ukuran foto untuk PDF (Dompdf tidak mendukung object-fit).
     *
     * @return array{width: int, height: int}|null
     */
    public function fotoPdfSize(string $field, int $maxWidth = 250, int $maxHeight = 100): ?array
    {
        $path = $this->{$field};

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $size = @getimagesize(Storage::disk('public')->path($path));

        if ($size === false) {
            return null;
        }

        [$width, $height] = $size;
        $scale = min($maxWidth / $width, $maxHeight / $height, 1);

        return [
            'width' => max(1, (int) round($width * $scale)),
            'height' => max(1, (int) round($height * $scale)),
        ];
    }

    /**
     * @return list<array{field: string, label: string, url: string|null}>
     */
    public function fotoItems(): array
    {
        return collect(self::FOTO_FIELDS)
            ->map(fn (string $label, string $field) => [
                'field' => $field,
                'label' => $label,
                'url' => $this->{$field} ? $this->fotoUrl($field) : null,
            ])
            ->values()
            ->all();
    }
}
