<?php

namespace App\Support;

use Carbon\Carbon;

class EnsiklopediaInteraktif
{
    public static function musim(): string
    {
        $month = (int) Carbon::now()->month;

        // Pola umum iklim tropis Batam: hujan Oktober–April, kemarau Mei–September
        return in_array($month, [5, 6, 7, 8, 9], true) ? 'kemarau' : 'hujan';
    }

    /**
     * @return array{key: string, label: string, icon: string, deskripsi: string, warna: string}
     */
    public static function musimInfo(): array
    {
        return match (self::musim()) {
            'kemarau' => [
                'key' => 'kemarau',
                'label' => 'Musim Kemarau',
                'icon' => '☀️',
                'deskripsi' => 'Periode lebih kering — fokus pada penyiraman efisien, mulsa, dan perlindungan tanaman dari panas.',
                'warna' => 'amber',
            ],
            default => [
                'key' => 'hujan',
                'label' => 'Musim Hujan',
                'icon' => '🌧️',
                'deskripsi' => 'Periode curah hujan tinggi — perhatikan drainase, cegah genangan, dan manfaatkan air hujan.',
                'warna' => 'sky',
            ],
        };
    }

    /**
     * @return list<string>
     */
    public static function artikelSlugsMusim(): array
    {
        return match (self::musim()) {
            'kemarau' => [
                'pohon-penghasil-oksigen',
                'urban-heat-island',
                'tips-berkebun-rumah',
                'rumput-taman',
            ],
            default => [
                'pengelolaan-rth',
                'tanaman-hias-taman',
                'tanaman-mangrove',
                'penanaman-bibit',
            ],
        };
    }

    /**
     * @return list<array{icon: string, judul: string, isi: string}>
     */
    public static function tipsMusim(): array
    {
        return match (self::musim()) {
            'kemarau' => [
                ['icon' => '💧', 'judul' => 'Siram pagi/sore', 'isi' => 'Hindari siang hari agar air tidak cepat menguap. Siram ke pangkal batang, bukan hanya daun.'],
                ['icon' => '🪵', 'judul' => 'Gunakan mulsa', 'isi' => 'Taburkan serasah atau cocopeat di permukaan media untuk menjaga kelembapan tanah.'],
                ['icon' => '✂️', 'judul' => 'Pangkas ringan', 'isi' => 'Kurangi beban daun tanaman agar kebutuhan air lebih efisien selama kemarau.'],
                ['icon' => '🌡️', 'judul' => 'Lindungi dari panas', 'isi' => 'Tanaman muda bisa dinaungi paranet sementara saat matahari terik di puncak kemarau.'],
            ],
            default => [
                ['icon' => '🚰', 'judul' => 'Cek drainase', 'isi' => 'Pastikan tidak ada genangan air di pot dan bedengan agar akar tidak busuk.'],
                ['icon' => '🍂', 'judul' => 'Bersihkan saluran', 'isi' => 'Rutin bersihkan daun kering dari selokan dan area taman agar air lancar.'],
                ['icon' => '🌱', 'judul' => 'Waktu tanam ideal', 'isi' => 'Musim hujan cocok untuk menanam bibit baru karena kelembapan tanah lebih stabil.'],
                ['icon' => '🐛', 'judul' => 'Awasi hama', 'isi' => 'Kelembapan tinggi bisa memicu jamur dan hama — pantau daun dan semprot organik bila perlu.'],
            ],
        };
    }

    /**
     * @return list<array{icon: string, judul: string, isi: string}>
     */
    public static function tipsBerkebun(): array
    {
        return [
            ['icon' => '🪴', 'judul' => 'Mulai dari pot kecil', 'isi' => 'Pemula bisa mulai dengan sayuran daun atau tanaman hias dalam pot — lebih mudah dikontrol.'],
            ['icon' => '☀️', 'judul' => 'Kenali cahaya tanaman', 'isi' => 'Tanaman matahari penuh butuh 6+ jam cahaya; tanaman teduh cukup 2–4 jam pagi.'],
            ['icon' => '🧪', 'judul' => 'Pupuk organik', 'isi' => 'Kompos dari sisa dapur memperbaiki struktur tanah dan menyuburkan tanaman secara alami.'],
            ['icon' => '💦', 'judul' => 'Jangan over-watering', 'isi' => 'Cek jari ke dalam tanah — siram hanya jika 2–3 cm permukaan sudah kering.'],
            ['icon' => '🌿', 'judul' => 'Rotasi tanaman', 'isi' => 'Ganti jenis tanaman di bedengan yang sama agar nutrisi tanah tetap seimbang.'],
            ['icon' => '🧤', 'judul' => 'Rawat alat kebun', 'isi' => 'Gunting dan cangkul yang bersih mencegah penyebaran penyakit antar tanaman.'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function faktaEdukasi(): array
    {
        return [
            '1 pohon dewasa dapat menyerap ~22 kg CO₂ per tahun.',
            'RTH ideal minimal 20% dari luas wilayah kota.',
            'Naungan pohon dapat menurunkan suhu hingga 10°C.',
            'Taman kota membantu mengurangi polusi udara dan kebisingan.',
            'Penghijauan jalur jalan dapat menurunkan suhu permukaan hingga 5°C.',
            'Akar pohon membantu menyerap air hujan dan mencegah banjir.',
            '1 hektar hutan kota dapat menyerap CO₂ setara puluhan mobil per tahun.',
            'Tanaman mangrove melindungi pesisir Batam dari abrasi dan badai.',
            'Rumput taman menahan debu dan membuat udara terasa lebih segar.',
            'Beraktivitas di taman hijau dapat menurunkan stres dan meningkatkan mood.',
            'Kompos dari sisa daun mengurangi sampah organik sekaligus menyuburkan tanah.',
            'Penanaman pohon di musim hujan memiliki tingkat keberhasilan lebih tinggi.',
        ];
    }

    /**
     * @return list<array{pertanyaan: string, opsi: list<string>, jawaban: int, penjelasan: string}>
     */
    public static function kuis(): array
    {
        return [
            [
                'pertanyaan' => 'Berapa persen minimum luas RTH ideal untuk sebuah kota menurut standar perencanaan?',
                'opsi' => ['10%', '20%', '30%', '50%'],
                'jawaban' => 1,
                'penjelasan' => 'Standar perencanaan umumnya menetapkan minimal 20% luas wilayah kota sebagai RTH.',
            ],
            [
                'pertanyaan' => 'Manfaat utama naungan pohon di perkotaan adalah…',
                'opsi' => ['Menambah polusi', 'Menurunkan suhu mikro', 'Mengurangi oksigen', 'Mempercepat erosi'],
                'jawaban' => 1,
                'penjelasan' => 'Naungan pohon dapat menurunkan suhu permukaan dan mengurangi efek pulau panas.',
            ],
            [
                'pertanyaan' => 'Pohon trembesi (Samanea saman) sering ditanam di Batam karena…',
                'opsi' => ['Kanopi lebar & teduh', 'Buah beracun', 'Tumbuh di air asin', 'Tidak perlu air'],
                'jawaban' => 0,
                'penjelasan' => 'Trembesi memiliki kanopi lebar sehingga cocok untuk naungan di taman dan jalur hijau.',
            ],
            [
                'pertanyaan' => 'Waktu terbaik menyiram tanaman di musim kemarau adalah…',
                'opsi' => ['Tengah hari', 'Pagi atau sore', 'Tengah malam saja', 'Hanya saat hujan'],
                'jawaban' => 1,
                'penjelasan' => 'Menyiram pagi/sore mengurangi evaporasi dan membantu tanaman menyerap air optimal.',
            ],
            [
                'pertanyaan' => 'Mangrove di pesisir Batam berfungsi sebagai…',
                'opsi' => ['Penghasil polusi', 'Penahan abrasi & habitat biota', 'Pengganti beton', 'Sumber gas beracun'],
                'jawaban' => 1,
                'penjelasan' => 'Mangrove melindungi pesisir dari abrasi dan menjadi habitat biota laut.',
            ],
            [
                'pertanyaan' => 'Rumput gajah mini sering dipakai di taman karena…',
                'opsi' => ['Tahan injakan & mudah dirawat', 'Hanya hidup di musim dingin', 'Tidak perlu dipotong', 'Berwarna ungu'],
                'jawaban' => 0,
                'penjelasan' => 'Rumput gajah mini tahan injakan sehingga cocok untuk area taman yang sering dilewati.',
            ],
            [
                'pertanyaan' => 'Program penghijauan Kota Batam dilaksanakan oleh…',
                'opsi' => ['Disperakimtan', 'Dinas Perhubungan', 'Dinas Pariwisata', 'Dinas Kesehatan'],
                'jawaban' => 0,
                'penjelasan' => 'Disperakimtan (Dinas Perumahan, Kawasan Permukiman dan Pertamanan) mengelola penghijauan & RTH.',
            ],
            [
                'pertanyaan' => 'Kompos dapat dibuat dari…',
                'opsi' => ['Plastik bekas', 'Sisa dapur organik', 'Batu koral', 'Cat tembok'],
                'jawaban' => 1,
                'penjelasan' => 'Sisa dapur organik seperti daun dan kulit buah bisa dijadikan kompos pupuk alami.',
            ],
        ];
    }
}
