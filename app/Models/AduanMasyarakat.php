<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AduanMasyarakat extends Model
{
    protected $table = 'aduan_masyarakats';

    public const JENIS = [
        'Kondisi Taman Rusak',
        'Tanaman Rusak / Mati',
        'Tanaman Berbahaya / Tumbang',
        'Fasilitas Taman Rusak',
        'Lainnya',
    ];

    public const STATUS = ['Baru', 'Ditinjau', 'Diproses', 'Selesai', 'Ditolak'];

    protected $fillable = [
        'nomor_aduan',
        'taman_id',
        'lokasi',
        'jenis_aduan',
        'deskripsi',
        'foto',
        'latitude',
        'longitude',
        'nama_pelapor',
        'kontak_pelapor',
        'status',
        'catatan_admin',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function taman(): BelongsTo
    {
        return $this->belongsTo(Taman::class);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        return asset('storage/'.$this->foto);
    }

    public static function generateNomor(): string
    {
        $prefix = 'ADU-'.now()->format('Ymd').'-';
        $last = static::query()
            ->where('nomor_aduan', 'like', $prefix.'%')
            ->orderByDesc('nomor_aduan')
            ->value('nomor_aduan');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'Baru' => 'bg-blue-100 text-blue-800',
            'Ditinjau' => 'bg-amber-100 text-amber-800',
            'Diproses' => 'bg-violet-100 text-violet-800',
            'Selesai' => 'bg-green-100 text-green-800',
            'Ditolak' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function jenisBadgeClass(string $jenis): string
    {
        return match ($jenis) {
            'Tanaman Berbahaya / Tumbang' => 'bg-red-100 text-red-800',
            'Tanaman Rusak / Mati' => 'bg-orange-100 text-orange-800',
            'Fasilitas Taman Rusak' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-emerald-100 text-emerald-800',
        };
    }

    /**
     * Ringkasan aman untuk halaman cek status publik (tanpa PII pelapor).
     *
     * @return array<string, mixed>
     */
    public function publicStatusSummary(): array
    {
        return [
            'nomor_aduan' => $this->nomor_aduan,
            'status' => $this->status,
            'jenis_aduan' => $this->jenis_aduan,
            'lokasi' => $this->lokasi,
            'taman' => $this->taman?->nama_taman,
            'diterima' => $this->created_at,
            'diperbarui' => $this->updated_at,
            'catatan' => $this->catatan_admin,
        ];
    }

    public function kontakMatchesVerification(string $input): bool
    {
        $stored = preg_replace('/\D/', '', (string) $this->kontak_pelapor);
        $verify = preg_replace('/\D/', '', $input);

        if (strlen($stored) < 4 || strlen($verify) !== 4) {
            return false;
        }

        return substr($stored, -4) === $verify;
    }
}
