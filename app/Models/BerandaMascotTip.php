<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BerandaMascotTip extends Model
{
    public const SECTION_HERO = 'hero';

    public const SECTION_INFO = 'info-terkini';

    public const SECTION_PIMPINAN = 'pimpinan';

    public const SECTION_PENJAGA = 'penjaga-hijau';

    public const SECTION_FAQ = 'faq';

    /** @var list<string> */
    public const SECTION_KEYS = [
        self::SECTION_HERO,
        self::SECTION_INFO,
        self::SECTION_PIMPINAN,
        self::SECTION_PENJAGA,
        self::SECTION_FAQ,
    ];

    /** @var array<string, string> */
    public const SECTION_DOM_IDS = [
        self::SECTION_HERO => 'home-hero',
        self::SECTION_INFO => 'info-terkini-slider',
        self::SECTION_PIMPINAN => 'pemerintah-kota',
        self::SECTION_PENJAGA => 'penjaga-hijau',
        self::SECTION_FAQ => 'faq-beranda',
    ];

    protected $fillable = [
        'section_key',
        'section_label',
        'title',
        'body',
        'image_asset',
        'cta_label',
        'cta_url',
        'is_active',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return Collection<int, self>
     */
    public static function activeForHome(): Collection
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('beranda_mascot_tips')) {
            return collect();
        }

        return static::query()
            ->where('is_active', true)
            ->whereIn('section_key', self::SECTION_KEYS)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
    }

    public function imageUrl(): string
    {
        return asset($this->image_asset);
    }

    /**
     * @return array<string, mixed>
     */
    public function toHomePayload(): array
    {
        return [
            'section_key' => $this->section_key,
            'dom_id' => self::SECTION_DOM_IDS[$this->section_key] ?? $this->section_key,
            'title' => $this->title,
            'body' => $this->body,
            'image_url' => $this->imageUrl(),
            'cta_label' => $this->cta_label,
            'cta_url' => $this->cta_url,
        ];
    }

    public static function seedDefaults(): void
    {
        $defaults = [
            [
                'section_key' => self::SECTION_HERO,
                'section_label' => 'Bagian utama (hero)',
                'title' => 'Hai, saya Tama!',
                'body' => "Saya Tama, petugas pemeliharaan taman di Batam. Senang Anda mampir ke SIMTAMAN — tempat warga jelajahi taman, peta, dan profil RTH Kota Batam. Mulai dari kotak pencarian, atau coba Taman terdekat jika lokasi Anda sudah diizinkan.",
                'image_asset' => 'images/mascot/tama/hero.png',
                'cta_label' => null,
                'cta_url' => null,
                'urutan' => 10,
            ],
            [
                'section_key' => self::SECTION_INFO,
                'section_label' => 'Informasi terkini',
                'title' => 'Kabar terbaru seputar taman',
                'body' => "Di sini saya suka cek informasi terkini dari Disperkimtan — kegiatan taman, pengumuman, dan hal penting lain seputar pertamanan. Geser slide pakai panah atau titik di bawah ya; kalau kartunya bisa diklik, silakan buka detailnya.",
                'image_asset' => 'images/mascot/tama/info.png',
                'cta_label' => null,
                'cta_url' => null,
                'urutan' => 20,
            ],
            [
                'section_key' => self::SECTION_PIMPINAN,
                'section_label' => 'Pemerintah Kota Batam',
                'title' => 'Didukung penuh Pemkot Batam',
                'body' => "Bagian ini perkenalan Walikota, Wakil Walikota, Kepala Disperkimtan, plus visi–misi pembangunan hunian dan taman. Saya bangga kerja di bawah dukungan Pemkot — dan SIMTAMAN bantu warga lihat data RTH dan layanan pertamanan lebih jelas.",
                'image_asset' => 'images/mascot/tama/pimpinan.jpg',
                'cta_label' => null,
                'cta_url' => null,
                'urutan' => 30,
            ],
            [
                'section_key' => self::SECTION_PENJAGA,
                'section_label' => 'Penjaga hijau kota',
                'title' => 'Ini tim saya di lapangan',
                'body' => "Penjaga Hijau Kota — tim saya dan rekan — setiap hari merawat, menanam, dan menjaga ruang hijau biar Batam tetap asri. Foto-foto di sini dari kerja nyata di taman. Makasih sudah peduli hijau kota; bareng-bareng kita jaga ya!",
                'image_asset' => 'images/mascot/tama/penjaga.png',
                'cta_label' => 'Lihat statistik RTH',
                'cta_url' => '/rth-kota-batam',
                'urutan' => 40,
            ],
            [
                'section_key' => self::SECTION_FAQ,
                'section_label' => 'FAQ',
                'title' => 'Bingung? Tanya di sini dulu',
                'body' => "Saya sering dengar pertanyaan yang sama — jawabannya ada di FAQ ini. Ketuk pertanyaannya untuk buka jawaban. Kalau masih kurang pas, lewat menu atas Anda bisa aduan masyarakat atau survey kepuasan; nanti tim kami bantu tindak lanjut.",
                'image_asset' => 'images/mascot/tama/faq.png',
                'cta_label' => 'Ajukan aduan',
                'cta_url' => '/aduan-masyarakat',
                'urutan' => 50,
            ],
        ];

        foreach ($defaults as $row) {
            static::query()->updateOrCreate(
                ['section_key' => $row['section_key']],
                $row + ['is_active' => true],
            );
        }
    }
}
