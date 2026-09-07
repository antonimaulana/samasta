<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Pemangkasan extends Model
{
    protected $table = 'pemangkasans';

    public const STATUS = ['Rencana', 'Diproses', 'Selesai'];

    public const KATEGORI = ['Instruksi', 'Laporan Masyarakat', 'Survei Lapangan'];

    public const JENIS_LAYANAN = ['Pemangkasan Pohon', 'Penanganan Pohon Tumbang', 'Pemasangan Mini Garden'];

    protected $fillable = [
        'jenis_layanan',
        'taman_id',
        'lokasi_pohon',
        'asal',
        'penanggungjawab',
        'kontak_permohonan',
        'tanggal_permohonan',
        'kategori',
        'kondisi_sebelum',
        'foto_sebelum',
        'dampak',
        'pendukung_pelaksanaan',
        'tanggal_eksekusi',
        'tanggal_akhir_jadwal',
        'total_hari',
        'hari_tercapai',
        'persentase_progres',
        'tanggal_penyelesaian',
        'pelaksana',
        'status',
        'foto_sesudah',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_permohonan' => 'date',
            'tanggal_eksekusi' => 'date',
            'tanggal_akhir_jadwal' => 'date',
            'tanggal_penyelesaian' => 'date',
            'total_hari' => 'integer',
            'hari_tercapai' => 'integer',
            'persentase_progres' => 'integer',
            'pelaksana' => 'array',
        ];
    }

    /**
     * @return list<string>
     */
    public static function timPelaksana(): array
    {
        return PemeliharaanTaman::timNames();
    }

    public function taman(): BelongsTo
    {
        return $this->belongsTo(Taman::class);
    }

    public function progres(): HasMany
    {
        return $this->hasMany(PemangkasanProgres::class)->orderBy('tanggal');
    }

    public function usesTimArmada(): bool
    {
        $teams = $this->pelaksana;

        return is_array($teams)
            && in_array(PemeliharaanTaman::TIM_ARMADA, $teams, true);
    }

    /**
     * Status yang boleh dipilih petugas lapangan saat menyimpan form.
     *
     * @return list<string>
     */
    public function lapanganStatusOptions(): array
    {
        return match ($this->status) {
            'Rencana' => ['Diproses'],
            'Diproses' => ['Diproses', 'Selesai'],
            default => [],
        };
    }

    public function allowsLapanganStatusTransition(string $newStatus): bool
    {
        if ($this->status === 'Selesai') {
            return false;
        }

        if ($newStatus === $this->status) {
            return $this->status === 'Diproses';
        }

        return in_array($newStatus, $this->lapanganStatusOptions(), true);
    }

    public function lokasiLabel(): string
    {
        if ($this->taman_id && $this->relationLoaded('taman') && $this->taman) {
            return PemeliharaanTaman::lokasiLabelFromTaman($this->taman);
        }

        if ($this->taman_id) {
            $taman = $this->taman()->first(['id', 'nama_taman', 'alamat']);

            if ($taman) {
                return PemeliharaanTaman::lokasiLabelFromTaman($taman);
            }
        }

        return (string) ($this->lokasi_pohon ?? '');
    }

    public function lokasiKategoriLabel(): string
    {
        if ($this->taman_id) {
            $kategori = $this->relationLoaded('taman')
                ? $this->taman?->kategori
                : $this->taman()->value('kategori');

            return $kategori ?? '—';
        }

        return 'Permintaan Masyarakat';
    }

    public function lokasiPelaksanaanPdfLabel(): string
    {
        return '('.$this->lokasiKategoriLabel().') '.$this->lokasiLabel();
    }

    public function asalPermohonanPdfLabel(): string
    {
        $parts = array_filter([
            $this->asal,
            $this->penanggungjawab,
        ]);

        $label = implode(' — ', $parts);

        if (filled($this->kontak_permohonan)) {
            $label .= ' ('.$this->kontak_permohonan.')';
        }

        return $label ?: '—';
    }

    public function pelaksanaLabel(): string
    {
        $teams = $this->pelaksana;

        if (is_array($teams)) {
            return implode(', ', array_filter($teams));
        }

        return (string) ($teams ?? '');
    }

    public function getFotoSesudahUrlAttribute(): ?string
    {
        if (! $this->foto_sesudah) {
            return null;
        }

        return asset('storage/'.$this->foto_sesudah);
    }

    public function getFotoSebelumUrlAttribute(): ?string
    {
        if (! $this->foto_sebelum) {
            return null;
        }

        return asset('storage/'.$this->foto_sebelum);
    }

    public function hasPendukungPelaksanaanFile(): bool
    {
        if (! $this->pendukung_pelaksanaan) {
            return false;
        }

        return Storage::disk('public')->exists($this->pendukung_pelaksanaan);
    }

    public function getPendukungPelaksanaanUrlAttribute(): ?string
    {
        if (! $this->hasPendukungPelaksanaanFile()) {
            return null;
        }

        return asset('storage/'.$this->pendukung_pelaksanaan);
    }

    public function pendukungPelaksanaanIsPdf(): bool
    {
        return str_ends_with(strtolower((string) $this->pendukung_pelaksanaan), '.pdf');
    }

    public function pendukungPelaksanaanFilename(): string
    {
        return basename((string) $this->pendukung_pelaksanaan);
    }

    public static function badgeClass(string $jenisLayanan): string
    {
        return match ($jenisLayanan) {
            'Penanganan Pohon Tumbang' => 'bg-orange-100 text-orange-800',
            'Pemasangan Mini Garden' => 'bg-violet-100 text-violet-800',
            default => 'bg-emerald-100 text-emerald-800',
        };
    }

    public static function layananIcon(string $jenisLayanan): string
    {
        return match ($jenisLayanan) {
            'Penanganan Pohon Tumbang' => '🪵',
            'Pemasangan Mini Garden' => '🪴',
            default => '✂️',
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function chartColors(string $jenisLayanan): array
    {
        return match ($jenisLayanan) {
            'Penanganan Pohon Tumbang' => ['rgba(249, 115, 22, 0.7)', 'rgb(249, 115, 22)'],
            'Pemasangan Mini Garden' => ['rgba(139, 92, 246, 0.7)', 'rgb(139, 92, 246)'],
            default => ['rgba(16, 185, 129, 0.7)', 'rgb(16, 185, 129)'],
        };
    }
}
