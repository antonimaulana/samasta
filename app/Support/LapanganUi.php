<?php

namespace App\Support;

use App\Models\User;

class LapanganUi
{
    /**
     * @return list<array{icon: string, title: string, body: string}>
     */
    public static function k3Tips(): array
    {
        return [
            [
                'icon' => '🦺',
                'title' => 'Pakai APD lengkap',
                'body' => 'Helm, sarung tangan, sepatu safety, dan rompi. APD melindungi Anda sebelum melindungi taman.',
            ],
            [
                'icon' => '👀',
                'title' => 'Cek area kerja dulu',
                'body' => 'Pastikan tidak ada kabel, pipa, atau orang lewat sebelum mulai pemangkasan atau pembersihan.',
            ],
            [
                'icon' => '💧',
                'title' => 'Jaga stamina di lapangan',
                'body' => 'Minum cukup air, istirahat sejenak di tempat teduh, terutama saat cuaca panas.',
            ],
            [
                'icon' => '🚧',
                'title' => 'Amankan lokasi kerja',
                'body' => 'Pasang pembatas atau tanda peringatan agar pengguna taman tetap aman saat pekerjaan berlangsung.',
            ],
            [
                'icon' => '🔧',
                'title' => 'Alat dalam kondisi baik',
                'body' => 'Periksa alat sebelum dipakai. Alat rusak berisiko cedera — laporkan ke pengawas jika perlu perbaikan.',
            ],
            [
                'icon' => '🤝',
                'title' => 'Komunikasi tim jelas',
                'body' => 'Sepakati isyarat dan pembagian tugas. Satu perintah jelas mencegah salah langkah di lapangan.',
            ],
        ];
    }

    /**
     * @return array{icon: string, title: string, body: string}
     */
    public static function k3TipOfDay(): array
    {
        $tips = self::k3Tips();
        $index = (int) now()->format('z') % count($tips);

        return $tips[$index];
    }

    public static function motivationQuote(): string
    {
        $quotes = [
            'Setiap daun yang dirapikan adalah wajah Batam yang lebih asri.',
            'Kerja rapi hari ini, taman indah untuk semua esok hari.',
            'Tim hebat dimulai dari langkah kecil yang konsisten di lapangan.',
            'Semangat hijau Anda menumbuhkan kebanggaan kota kita.',
            'Pekerjaan tangan yang jujur, hasil taman yang membanggakan.',
            'Safety first — pulang selamat, keluarga menunggu.',
            'Disiplin K3 kecil mencegah kecelakaan besar.',
            'Batam hijau karena tangan-tangan pekerja yang peduli.',
        ];

        return $quotes[(int) now()->format('z') % count($quotes)];
    }

    public static function greetingName(?User $user, bool $guestMode = false): string
    {
        if ($user) {
            return $user->name;
        }

        if ($guestMode) {
            return 'Petugas Lapangan';
        }

        return 'Rekan Tim';
    }

    /**
     * @return list<array{step: int, label: string, hint: string}>
     */
    public static function pemeliharaanSteps(): array
    {
        return [
            ['step' => 1, 'label' => 'Isi tanggal & lokasi', 'hint' => 'Pilih taman atau lokasi pekerjaan hari ini'],
            ['step' => 2, 'label' => 'Catat personil & pekerjaan', 'hint' => 'Jumlah tim dan uraian singkat'],
            ['step' => 3, 'label' => 'Upload foto', 'hint' => 'Sebelum, saat, dan sesudah pekerjaan'],
            ['step' => 4, 'label' => 'Simpan', 'hint' => 'Tekan tombol hijau di bawah form'],
        ];
    }

    /**
     * @return list<array{step: int, label: string, hint: string}>
     */
    public static function permohonanSteps(): array
    {
        return [
            ['step' => 1, 'label' => 'Pilih permohonan', 'hint' => 'Dari daftar yang dibuat admin (status awal: Rencana)'],
            ['step' => 2, 'label' => 'Catat progres harian', 'hint' => 'Tanggal, personil, dan foto di lapangan'],
            ['step' => 3, 'label' => 'Konfirmasi status', 'hint' => 'Mulai pekerjaan (Diproses) atau tandai selesai'],
        ];
    }

}
