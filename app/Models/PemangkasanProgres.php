<?php

namespace App\Models;

use App\Casts\OperasionalDateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PemangkasanProgres extends Model
{
    protected $table = 'pemangkasan_progres';

    protected $fillable = [
        'pemangkasan_id',
        'tim',
        'tanggal',
        'hari_ke',
        'jumlah_personil',
        'foto_sebelum_1',
        'foto_sebelum_2',
        'foto_saat_1',
        'foto_saat_2',
        'foto_sesudah_1',
        'foto_sesudah_2',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => OperasionalDateTime::class,
            'hari_ke' => 'integer',
            'jumlah_personil' => 'integer',
        ];
    }

    public function pemangkasan(): BelongsTo
    {
        return $this->belongsTo(Pemangkasan::class);
    }

    public function armadas(): HasMany
    {
        return $this->hasMany(PemangkasanProgresArmada::class, 'pemangkasan_progres_id')->orderBy('urutan');
    }

    public function petugas(): BelongsToMany
    {
        return $this->belongsToMany(Petugas::class, 'pemangkasan_progres_petugas')->orderBy('nama');
    }

    public function petugasLabel(): string
    {
        if ($this->relationLoaded('petugas') && $this->petugas->isNotEmpty()) {
            return $this->petugas->pluck('nama')->implode(', ');
        }

        return $this->jumlah_personil ? number_format((int) $this->jumlah_personil).' personil' : '—';
    }

    public function namaPengawas(): ?string
    {
        $tim = filled($this->tim) ? $this->tim : null;

        if ($tim === null) {
            return null;
        }

        return TimPelaksana::query()
            ->where('nama', $tim)
            ->value('nama_pengawas');
    }

    public function armadaPdfHtml(): ?string
    {
        try {
            if (! Schema::hasTable('pemangkasan_progres_armadas')) {
                return null;
            }

            if ($this->armadas->isEmpty()) {
                return null;
            }

            return $this->armadas
                ->map(fn (PemangkasanProgresArmada $armada) => $armada->pdfEntryHtml())
                ->implode(', ');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return list<string>
     */
    public static function fotoFieldKeys(): array
    {
        return array_keys(PemeliharaanTaman::FOTO_FIELDS);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function fotoGroups(): array
    {
        return [
            'Sebelum Pelaksanaan' => ['foto_sebelum_1', 'foto_sebelum_2'],
            'Saat Pelaksanaan' => ['foto_saat_1', 'foto_saat_2'],
            'Sesudah Pelaksanaan' => ['foto_sesudah_1', 'foto_sesudah_2'],
        ];
    }

    public function fotoUrl(string $field): ?string
    {
        $path = $this->{$field};

        if (! $path || ! Storage::disk('public')->exists($path)) {
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

        try {
            $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';
        } catch (\Throwable) {
            $mime = 'image/jpeg';
        }

        try {
            $binary = Storage::disk('public')->get($path);
        } catch (\Throwable) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    /**
     * Data URI aman untuk Dompdf (JPEG/PNG/GIF; format lain dikonversi ke JPEG bila GD tersedia).
     */
    public function fotoDompdfSrc(string $field): ?string
    {
        $path = $this->{$field};

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        try {
            $mime = Storage::disk('public')->mimeType($path);
        } catch (\Throwable) {
            $mime = null;
        }

        if (in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) {
            return $this->fotoBase64($field);
        }

        if (! extension_loaded('gd')) {
            return null;
        }

        try {
            $binary = Storage::disk('public')->get($path);
        } catch (\Throwable) {
            return null;
        }

        $image = @imagecreatefromstring($binary);

        if ($image === false) {
            return null;
        }

        ob_start();
        imagejpeg($image, null, 85);
        imagedestroy($image);
        $jpeg = ob_get_clean();

        if ($jpeg === false || $jpeg === '') {
            return null;
        }

        return 'data:image/jpeg;base64,'.base64_encode($jpeg);
    }

    /**
     * @return array{width: int, height: int}|null
     */
    public function fotoPdfSize(string $field, int $maxWidth = 520, int $maxHeight = 178): ?array
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

        if ($width < 1 || $height < 1) {
            return null;
        }

        $scale = min($maxWidth / $width, $maxHeight / $height, 1);

        return [
            'width' => max(1, (int) round($width * $scale)),
            'height' => max(1, (int) round($height * $scale)),
        ];
    }
}
