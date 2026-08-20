<?php

namespace App\Support;

class Ensiklopedia
{
    /**
     * @return list<array{slug: string, nama: string, icon: string, deskripsi: string, artikel: list<array{slug: string, judul: string, icon: string, ringkas: string, konten: string}>}>
     */
    public static function kategori(): array
    {
        return [
            [
                'slug' => 'program-pemerintah',
                'nama' => 'Program Pemerintah',
                'icon' => '🏛️',
                'deskripsi' => 'Program dan kebijakan Disperakimtan Kota Batam dalam penghijauan, pengelolaan RTH, dan layanan pertamanan.',
                'artikel' => [
                    [
                        'slug' => 'penghijauan-kota-batam',
                        'judul' => 'Program Penghijauan Kota Batam',
                        'icon' => '🌱',
                        'ringkas' => 'Upaya sistematis meningkatkan tutupan vegetasi dan kualitas udara di seluruh wilayah Kota Batam.',
                        'konten' => "Program Penghijauan Kota Batam merupakan komitmen Pemerintah Kota Batam melalui Dinas Perumahan, Permukiman, dan Pertamanan (Disperakimtan) untuk meningkatkan tutupan vegetasi perkotaan.\n\nProgram ini mencakup penanaman pohon di ruas jalan, taman kota, taman lingkungan, dan kawasan RTH. Target utamanya adalah menciptakan lingkungan yang lebih sejuk, mengurangi polusi udara, serta memberikan ruang rekreasi bagi masyarakat.\n\nKegiatan penghijauan dilakukan secara berkala dengan melibatkan masyarakat, sekolah, dan komunitas lingkungan sebagai mitra strategis.",
                    ],
                    [
                        'slug' => 'pengelolaan-rth',
                        'judul' => 'Pengelolaan Ruang Terbuka Hijau (RTH)',
                        'icon' => '🏞️',
                        'ringkas' => 'Pemeliharaan dan pengembangan RTH taman kota, taman lingkungan, jalur hijau, Kebun Raya, dan TPU.',
                        'konten' => "Pengelolaan RTH Kota Batam diatur berdasarkan Peraturan Daerah dan standar teknis pertamanan. Disperakimtan mengelola lebih dari 2,5 juta meter persegi RTH yang terpelihara.\n\nKategori RTH meliputi taman kota, taman lingkungan, jalur hijau jalan, Kebun Raya Batam, dan Taman Pemakaman Umum (TPU). Setiap kategori memiliki karakteristik dan fungsi yang berbeda.\n\nPemeliharaan rutin meliputi penyiraman, pemupukan, pemangkasan, pengendalian hama, serta perbaikan fasilitas pendukung seperti lampu taman, bangku, dan jalur pejalan kaki.",
                    ],
                    [
                        'slug' => 'layanan-pertamanan',
                        'judul' => 'Layanan Pertamanan Masyarakat',
                        'icon' => '🪓',
                        'ringkas' => 'Layanan pemangkasan pohon dan penanganan pohon tumbang untuk keamanan dan estetika lingkungan.',
                        'konten' => "Disperakimtan Kota Batam menyediakan layanan pertamanan bagi masyarakat, termasuk pemangkasan pohon dan penanganan pohon tumbang.\n\nLayanan pemangkasan pohon dilakukan untuk pohon-pohon yang sudah terlalu tinggi, mengganggu utilitas (kabel listrik, jalan), atau berpotensi bahaya. Penanganan pohon tumbang ditangani segera setelah laporan diterima untuk mencegah kerusakan properti dan kecelakaan.\n\nMasyarakat dapat mengajukan permohonan layanan melalui kanal resmi Disperakimtan dengan melampirkan lokasi dan kondisi pohon yang perlu ditangani.",
                    ],
                    [
                        'slug' => 'penanaman-bibit',
                        'judul' => 'Program Penyediaan & Penanaman Bibit',
                        'icon' => '🌿',
                        'ringkas' => 'Distribusi bibit tanaman produktif dan penghijauan untuk kegiatan gotong royong masyarakat.',
                        'konten' => "Program bibit tanaman merupakan bagian dari strategi penghijauan jangka panjang Kota Batam. Disperakimtan mengelola nursery bibit yang menyediakan berbagai jenis tanaman produktif dan penghijau.\n\nBibit didistribusikan untuk kegiatan penanaman bersama masyarakat, sekolah, dan instansi pemerintah. Jenis bibit disesuaikan dengan karakteristik lahan dan fungsi penanaman.\n\nProgram ini juga mendukung gerakan penghijauan lingkungan permukiman dan pencapaian target RTH sesuai rencana tata ruang wilayah.",
                    ],
                ],
            ],
            [
                'slug' => 'tanaman-flora',
                'nama' => 'Tanaman & Flora',
                'icon' => '🌳',
                'deskripsi' => 'Informasi jenis-jenis tanaman yang ditanam di taman dan RTH Kota Batam, serta tips perawatannya.',
                'artikel' => [
                    [
                        'slug' => 'pohon-penghasil-oksigen',
                        'judul' => 'Pohon Penghasil Oksigen untuk Perkotaan',
                        'icon' => '💨',
                        'ringkas' => 'Jenis pohon yang efektif menyerap CO₂ dan menghasilkan oksigen di lingkungan urban.',
                        'konten' => "Pemilihan jenis pohon yang tepat sangat penting untuk efektivitas penghijauan perkotaan. Pohon dengan kanopi lebar dan daun lebat mampu menyerap karbon dioksida dan melepaskan oksigen dalam jumlah signifikan.\n\nBeberapa pohon yang umum ditanam di Kota Batam antara lain pohon angsana (Pterocarpus indicus), pohon trembesi (Samanea saman), pohon flamboyan (Delonix regia), dan pohon ketapang (Terminalia catappa).\n\nSelain fungsi ekologis, pohon-pohon ini juga memberikan naungan yang nyaman dan mempercantik estetika taman serta jalur hijau.",
                    ],
                    [
                        'slug' => 'tanaman-hias-taman',
                        'judul' => 'Tanaman Hias di Taman Kota',
                        'icon' => '🌺',
                        'ringkas' => 'Tanaman bunga dan semak hias yang memperindah taman dan RTH di Kota Batam.',
                        'konten' => "Tanaman hias berperan penting dalam menciptakan taman yang indah dan menarik. Disperakimtan menggunakan kombinasi tanaman bunga musiman dan tanaman permanen untuk menjaga tampilan taman sepanjang tahun.\n\nTanaman yang sering digunakan meliputi bougainvillea, ixora, mawar taman, dan various palm. Penataan tanaman hias mengikuti prinsip desain lansekap dengan memperhatikan warna, tekstur, dan ketinggian.\n\nPerawatan tanaman hias meliputi pemangkasan rutin, penggantian tanaman musiman, dan pengendalian hama agar taman tetap rapi dan sehat.",
                    ],
                    [
                        'slug' => 'tanaman-mangrove',
                        'judul' => 'Mangrove & Vegetasi Pesisir',
                        'icon' => '🌊',
                        'ringkas' => 'Peran mangrove dalam melindungi pesisir dan menjaga keseimbangan ekosistem perairan.',
                        'konten' => "Sebagai kota kepulauan, Batam memiliki kawasan pesisir yang membutuhkan perlindungan vegetasi mangrove. Mangrove berfungsi sebagai penahan abrasi, habitat biota laut, dan penyerap karbon biru (blue carbon).\n\nJenis mangrove yang umum di perairan Batam meliputi bakau (Rhizophora), api-api (Avicennia), dan pedada (Sonneratia). Konservasi dan penanaman mangrove dilakukan bekerja sama dengan berbagai pihak.\n\nProgram edukasi mangrove juga diselenggarakan untuk meningkatkan kesadaran masyarakat tentang pentingnya ekosistem pesisir.",
                    ],
                    [
                        'slug' => 'rumput-taman',
                        'judul' => 'Jenis Rumput & Penutup Lahan',
                        'icon' => '🌾',
                        'ringkas' => 'Pemilihan rumput taman dan tanaman penutup lahan untuk area RTH yang rapi dan hijau.',
                        'konten' => "Rumput taman merupakan elemen dasar dalam lansekap taman modern. Pemilihan jenis rumput disesuaikan dengan intensitas penggunaan, cahaya matahari, dan ketersediaan air.\n\nRumput gajah mini (Axonopus compressus) dan rumput Manila (Zoysia matrella) sering digunakan di taman Kota Batam karena tahan injakan dan mudah dipelihara.\n\nPerawatan rumput taman meliputi pemotongan rutin, penyiraman, pemupukan, dan aerasi tanah untuk menjaga kepadatan dan warna hijau yang merata.",
                    ],
                ],
            ],
            [
                'slug' => 'edukasi-lainnya',
                'nama' => 'Edukasi & Lainnya',
                'icon' => '📚',
                'deskripsi' => 'Artikel edukatif tentang manfaat RTH, tips berkebun, dan isu-isu lingkungan terkait pertamanan.',
                'artikel' => [
                    [
                        'slug' => 'manfaat-rth',
                        'judul' => 'Manfaat Ruang Terbuka Hijau',
                        'icon' => '✨',
                        'ringkas' => 'Mengapa RTH penting bagi kesehatan, iklim, dan kualitas hidup masyarakat perkotaan.',
                        'konten' => "Ruang Terbuka Hijau (RTH) memberikan manfaat multidimensional bagi masyarakat dan lingkungan. Secara ekologis, RTH menyerap polusi udara, mengurangi efek pulau panas (urban heat island), dan menjadi habitat bagi berbagai spesies.\n\nSecara sosial, taman dan RTH menjadi ruang interaksi masyarakat, area rekreasi keluarga, dan tempat aktivitas olahraga. RTH juga meningkatkan nilai estetika dan daya tarik kota.\n\nMenurut standar perencanaan, proporsi RTH ideal untuk kota adalah minimal 20% dari luas wilayah. Kota Batam terus berupaya meningkatkan kualitas dan kuantitas RTH untuk kesejahteraan warganya.",
                    ],
                    [
                        'slug' => 'tips-berkebun-rumah',
                        'judul' => 'Tips Berkebun di Rumah',
                        'icon' => '🏡',
                        'ringkas' => 'Panduan sederhana memulai kebun kecil di halaman rumah atau pot untuk pemula.',
                        'konten' => "Berkebun di rumah adalah cara praktis berkontribusi terhadap penghijauan lingkungan. Mulailah dengan menentukan lokasi yang mendapat cahaya matahari minimal 4–6 jam per hari.\n\nPilih tanaman yang sesuai dengan kondisi lahan: tanaman sayuran untuk area cerah, tanaman hias daun untuk area teduh. Gunakan media tanam berkualitas dan pastikan drainase pot atau bedengan baik.\n\nSiram tanaman secara teratur — pagi atau sore hari adalah waktu terbaik. Pupuk organik kompos dapat dibuat sendiri dari sisa dapur untuk menyuburkan tanaman secara alami.",
                    ],
                    [
                        'slug' => 'urban-heat-island',
                        'judul' => 'Mengenal Efek Pulau Panas Perkotaan',
                        'icon' => '🌡️',
                        'ringkas' => 'Bagaimana penghijauan dapat mengurangi suhu udara di kawasan permukiman padat.',
                        'konten' => "Efek pulau panas (urban heat island) terjadi ketika suhu udara di kawasan perkotaan lebih tinggi dibandingkan area sekitarnya. Penyebab utamanya adalah permukaan beton dan aspal yang menyerap panas, serta kurangnya vegetasi.\n\nPenghijauan melalui penanaman pohon dan pengembangan RTH terbukti efektif menurunkan suhu mikro lingkungan. Naungan pohon dapat mengurangi suhu permukaan hingga 10–20°C.\n\nStrategi mitigasi pulau panas di Kota Batam meliputi penanaman pohon di jalur hijau jalan, pengembangan taman lingkungan, dan mendorong penghijauan pada bangunan gedung.",
                    ],
                    [
                        'slug' => 'partisipasi-masyarakat',
                        'judul' => 'Partisipasi Masyarakat dalam Penghijauan',
                        'icon' => '🤝',
                        'ringkas' => 'Peran aktif warga dalam menjaga dan mengembangkan ruang hijau di lingkungan sekitar.',
                        'konten' => "Keberhasilan program penghijauan tidak hanya bergantung pada pemerintah, tetapi juga partisipasi aktif masyarakat. Warga dapat terlibat melalui kegiatan penanaman bersama, adopsi pohon, dan pemeliharaan taman lingkungan.\n\nKomunitas RT/RW dapat mengorganisir kegiatan gotong royong membersihkan dan merawat RTH di lingkungan mereka. Sekolah juga berperan penting melalui program edukasi lingkungan dan kebun sekolah.\n\nMelaporkan kondisi pohon berbahaya, pohon tumbang, atau kerusakan fasilitas taman kepada Disperakimtan juga merupakan bentuk partisipasi yang berharga.",
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{slug: string, judul: string, icon: string, ringkas: string, konten: string, kategori: array{slug: string, nama: string, icon: string}}|null
     */
    public static function findArtikel(string $slug): ?array
    {
        foreach (self::kategori() as $kategori) {
            foreach ($kategori['artikel'] as $artikel) {
                if ($artikel['slug'] === $slug) {
                    return [
                        ...$artikel,
                        'kategori' => [
                            'slug' => $kategori['slug'],
                            'nama' => $kategori['nama'],
                            'icon' => $kategori['icon'],
                        ],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @return list<array{slug: string, judul: string, icon: string, ringkas: string, kategori_nama: string, kategori_slug: string}>
     */
    public static function semuaArtikel(): array
    {
        $artikel = [];

        foreach (self::kategori() as $kategori) {
            foreach ($kategori['artikel'] as $item) {
                $artikel[] = [
                    'slug' => $item['slug'],
                    'judul' => $item['judul'],
                    'icon' => $item['icon'],
                    'ringkas' => $item['ringkas'],
                    'kategori_nama' => $kategori['nama'],
                    'kategori_slug' => $kategori['slug'],
                ];
            }
        }

        return $artikel;
    }
}
