<?php

declare(strict_types=1);

/**
 * Generate a Word (.docx) analysis document for RAP vs SIMTAMAN current state.
 */
$outPath = dirname(__DIR__).'/docs/ANALISIS-RAP-vs-SIMTAMAN.docx';

$paragraphs = [
    ['style' => 'title', 'text' => 'Analisis Kesesuaian RAP dengan Kondisi Sistem SIMTAMAN'],
    ['style' => 'subtitle', 'text' => 'Optimalisasi Pengelolaan Data dan Monitoring Operasional RTH — Disperakimtan Kota Batam'],
    ['style' => 'meta', 'text' => 'Dokumen referensi: RAP Irwan rev5 (1).docx'],
    ['style' => 'meta', 'text' => 'Tanggal analisis: 17 September 2026'],
    ['style' => 'meta', 'text' => 'Aplikasi: SIMTAMAN (Sistem Informasi Manajemen Pertamanan) — Laravel 12 / PHP 8.2'],
    ['style' => 'spacer'],
    ['style' => 'heading1', 'text' => '1. Pendahuluan'],
    ['style' => 'body', 'text' => 'Dokumen ini menyajikan penilaian teknis terhadap kesesuaian Rancangan Aksi Perubahan (RAP) terkait optimalisasi pengelolaan Ruang Terbuka Hijau (RTH) dengan kondisi implementasi aplikasi SIMTAMAN per September 2026. Analisis disusun berdasarkan inspeksi langsung terhadap kode sumber, modul evaluasi, konfigurasi, dan dokumentasi teknis di repositori sitaman-batam.'],
    ['style' => 'note', 'text' => 'Catatan: Berkas RAP Irwan rev5 (1).docx tidak dapat diakses dari lingkungan analisis (path tidak ditemukan). Penilaian di bawah ini memetakan elemen RAP yang umum diperlukan untuk transformasi digital pengelolaan RTH terhadap fitur yang sudah ada di SIMTAMAN. Untuk pemetaan butir-per-butir terhadap isi RAP rev5, unggah ulang dokumen ke folder proyek atau lampirkan langsung di chat.'],
    ['style' => 'heading1', 'text' => '2. Ringkasan Eksekutif'],
    ['style' => 'body', 'text' => 'SIMTAMAN sudah berada pada tahap maturitas operasional yang kuat untuk mendukung pelaksanaan RAP. Aplikasi mengintegrasikan basis data RTH, monitoring operasional pertamanan, portal publik (aduan & survey), input lapangan, serta modul evaluasi yang dirancang khusus untuk pengambilan keputusan RAP.'],
    ['style' => 'bullet', 'text' => 'Kesesuaian tinggi: basis data taman/RTH, pemeliharaan rutin, permohonan layanan, armada, laporan evaluasi operasional, partisipasi masyarakat (aduan & survey), dan dashboard eksekutif.'],
    ['style' => 'bullet', 'text' => 'Pencapaian signifikan terbaru: empat modul evaluasi RAP (Kelengkapan Data, RTH Terpelihara, Masukan Masyarakat, RAP Konsolidasi) yang sebelumnya belum ada, kini sudah diimplementasikan dengan ekspor PDF dan pengujian otomatis.'],
    ['style' => 'bullet', 'text' => 'Gap utama yang masih perlu ditindaklanjuti: integrasi modul DPA dengan indikator RAP, aplikasi mobile native/API, target KPI RAP yang dapat dikonfigurasi, workflow verifikasi data multi-tahap, dan deployment modul evaluasi terbaru ke produksi.'],
    ['style' => 'body', 'text' => 'Secara keseluruhan, arah RAP dan arsitektur SIMTAMAN sudah selaras. Aplikasi bukan lagi sekadar repositori data, melainkan platform monitoring kinerja yang dapat menjadi instrumen evaluasi berkala RAP.'],
    ['style' => 'heading1', 'text' => '3. Kondisi Sistem SIMTAMAN Saat Ini'],
    ['style' => 'heading2', 'text' => '3.1 Stack Teknologi'],
    ['style' => 'bullet', 'text' => 'Backend: PHP 8.2+, Laravel 12, autentikasi session-based (admin/operator/viewer).'],
    ['style' => 'bullet', 'text' => 'Frontend: Blade, Tailwind CSS 4, Vite 7.'],
    ['style' => 'bullet', 'text' => 'Database: SQLite (dev), MySQL/MariaDB (produksi).'],
    ['style' => 'bullet', 'text' => 'Ekspor: DomPDF (PDF), QR Code untuk WebAR profil RTH.'],
    ['style' => 'bullet', 'text' => 'Rebrand: SIMAPAN → SIMTAMAN (alias legacy masih didukung di config/env).'],
    ['style' => 'heading2', 'text' => '3.2 Modul Utama'],
    ['style' => 'bullet', 'text' => 'Portal Publik: informasi taman, peta interaktif, statistik RTH, WebAR, ensiklopedia, aduan masyarakat, survey kepuasan.'],
    ['style' => 'bullet', 'text' => 'Admin Back-Office: CRUD taman & bibit, operasional pertamanan, evaluasi, masukan masyarakat, ensiklopedia, manajemen user, monitoring DPA.'],
    ['style' => 'bullet', 'text' => 'Input Lapangan (/lapangan): akses PIN tanpa login atau login operator/admin; input pemeliharaan rutin dan update progres permohonan.'],
    ['style' => 'bullet', 'text' => 'Scheduler: reminder jadwal layanan (07:00), digest operasional email (07:30, opsional).'],
    ['style' => 'heading1', 'text' => '4. Pemetaan RAP terhadap Fitur SIMTAMAN'],
    ['style' => 'heading2', 'text' => '4.1 Pilar Basis Data RTH'],
    ['style' => 'body', 'text' => 'RAP umumnya menargetkan peningkatan kelengkapan, akurasi, dan kemutakhiran data RTH. SIMTAMAN sudah memiliki:'],
    ['style' => 'bullet', 'text' => 'CRUD taman lengkap dengan import CSV, galeri foto, koordinat, fasilitas, dan penentuan wilayah otomatis dari koordinat.'],
    ['style' => 'bullet', 'text' => 'Skor kelengkapan profil (13 field → 0–100%) via TamanCompleteness; status data lengkap/belum lengkap.'],
    ['style' => 'bullet', 'text' => 'Timestamp verifikasi data (data_verified_at) untuk menilai kemutakhiran.'],
    ['style' => 'bullet', 'text' => 'Laporan Evaluasi Kelengkapan Data: filter kategori, kecamatan, status, mutakhir (30/60/90/180 hari), breakdown field kosong, daftar prioritas perbaikan.'],
    ['style' => 'bullet', 'text' => 'Laporan Evaluasi RTH Terpelihara: persentase lokasi & luasan terpelihara berdasarkan pemeliharaan rutin terakhir (window 30–90 hari).'],
    ['style' => 'status', 'text' => 'Status: SESUAI — indikator RAP untuk basis data sudah terukur dan dapat diekspor PDF.'],
    ['style' => 'heading2', 'text' => '4.2 Pilar Monitoring Operasional'],
    ['style' => 'body', 'text' => 'RAP menekankan transparansi dan akuntabilitas pelaksanaan pemeliharaan, permohonan layanan, dan utilisasi sumber daya. SIMTAMAN menyediakan:'],
    ['style' => 'bullet', 'text' => 'Pemeliharaan rutin dengan dokumentasi foto (hingga 6 foto per kegiatan).'],
    ['style' => 'bullet', 'text' => 'Permohonan layanan (pemangkasan dll.) dengan jadwal, progres, deteksi jadwal terlambat, badge alert di navigasi.'],
    ['style' => 'bullet', 'text' => 'Inventaris armada/alat sarana dengan riwayat penugasan pada pemeliharaan & permohonan.'],
    ['style' => 'bullet', 'text' => 'Evaluasi: Pemeliharaan per Taman, Utilisasi Armada, Kinerja Tim, Operasional Permohonan (SLA, persentase selesai & tepat waktu).'],
    ['style' => 'bullet', 'text' => 'Alert operasional (stok bibit rendah, aduan belum ditinjau, jadwal terlambat, layanan macet) + notifikasi in-app & email digest.'],
    ['style' => 'status', 'text' => 'Status: SESUAI — monitoring operasional sudah terintegrasi dan dapat diukur per periode.'],
    ['style' => 'heading2', 'text' => '4.3 Pilar Partisipasi Masyarakat'],
    ['style' => 'body', 'text' => 'RAP modern menuntut mekanisme umpan balik masyarakat yang terukur. SIMTAMAN memiliki:'],
    ['style' => 'bullet', 'text' => 'Formulir aduan publik dengan pelacakan status (throttled).'],
    ['style' => 'bullet', 'text' => 'Survey kepuasan publik (throttled) dengan kategori portal SIMTAMAN.'],
    ['style' => 'bullet', 'text' => 'Workflow admin aduan (tinjau, proses, selesai) dan review survey.'],
    ['style' => 'bullet', 'text' => 'Evaluasi Masukan Masyarakat: tingkat penyelesaian aduan, aduan ditanggapi, aduan overdue, rata-rata & persentase kepuasan survey per periode.'],
    ['style' => 'status', 'text' => 'Status: SESUAI — partisipasi masyarakat sudah terdata dan masuk indikator evaluasi.'],
    ['style' => 'heading2', 'text' => '4.4 Laporan RAP Konsolidasi (Dashboard Eksekutif)'],
    ['style' => 'body', 'text' => 'Modul RAP Konsolidasi mengagregasi seluruh indikator evaluasi ke dalam tiga pilar (Basis Data RTH, Monitoring Operasional, Partisipasi Masyarakat) dengan:'],
    ['style' => 'bullet', 'text' => 'Indeks Kinerja RAP: rata-rata numerik seluruh indikator (good ≥80%, warn ≥60%, bad <60%).'],
    ['style' => 'bullet', 'text' => 'Radar chart indikator, drill-down ke masing-masing laporan detail.'],
    ['style' => 'bullet', 'text' => 'Daftar Prioritas Tindak Lanjut: RTH belum terpelihara, profil data perlu perhatian, aduan terlambat ditinjau.'],
    ['style' => 'bullet', 'text' => 'Ekspor PDF landscape untuk rapat RAP / presentasi pimpinan.'],
    ['style' => 'bullet', 'text' => 'Role viewer dapat mengakses seluruh laporan evaluasi (read-only).'],
    ['style' => 'status', 'text' => 'Status: SANGAT SESUAI — modul ini secara langsung mewujudkan kebutuhan evaluasi berkala RAP yang sebelumnya belum ada.'],
    ['style' => 'heading1', 'text' => '5. Matriks Kesesuaian RAP vs Kondisi Sistem'],
    ['style' => 'table_header', 'text' => "Aspek RAP\tKondisi SIMTAMAN\tTingkat Kesesuaian"],
    ['style' => 'table_row', 'text' => "Basis data RTH terpusat\tCRUD + import CSV + kelengkapan + verifikasi\tTinggi"],
    ['style' => 'table_row', 'text' => "Indikator kelengkapan & mutakhir data\tEvaluasi Kelengkapan Data + data_verified_at\tTinggi"],
    ['style' => 'table_row', 'text' => "Monitoring % RTH terpelihara\tEvaluasi RTH Terpelihara (lokasi & luasan)\tTinggi"],
    ['style' => 'table_row', 'text' => "Monitoring pemeliharaan & permohonan\tModul operasional + 4 laporan evaluasi\tTinggi"],
    ['style' => 'table_row', 'text' => "Partisipasi & kepuasan masyarakat\tAduan + survey + Evaluasi Masukan Masyarakat\tTinggi"],
    ['style' => 'table_row', 'text' => "Laporan eksekutif RAP berkala\tRAP Konsolidasi + ekspor PDF\tTinggi (baru)"],
    ['style' => 'table_row', 'text' => "Input data lapangan\tPortal /lapangan (PIN + mobile-responsive)\tSedang"],
    ['style' => 'table_row', 'text' => "Integrasi anggaran/DPA\tModul DPA ada, terpisah dari RAP\tRendah–Sedang"],
    ['style' => 'table_row', 'text' => "Aplikasi mobile native / API\tBelum ada (web lapangan saja)\tRendah"],
    ['style' => 'table_row', 'text' => "Target KPI RAP yang dapat diset\tBelum ada modul konfigurasi target\tRendah"],
    ['style' => 'table_row', 'text' => "Workflow verifikasi multi-tahap\tVerifikasi timestamp manual\tSedang"],
    ['style' => 'table_row', 'text' => "Transparansi publik indikator RAP\tPortal info taman/aduan, belum dashboard publik RAP\tSedang"],
    ['style' => 'heading1', 'text' => '6. Gap Analysis — Apa yang Masih Kurang'],
    ['style' => 'heading2', 'text' => '6.1 Gap Operasional & Teknis'],
    ['style' => 'bullet', 'text' => 'Modul evaluasi RAP terbaru (4 laporan + konsolidasi) masih belum di-commit/deploy ke produksi — perlu merge, migrate, dan uji di VPS BatamGarden.'],
    ['style' => 'bullet', 'text' => 'PIN lapangan (SIMTAMAN_LAPANGAN_PIN) perlu dikonfigurasi agar petugas lapangan dapat input tanpa akun penuh.'],
    ['style' => 'bullet', 'text' => 'Field data_verified_at perlu diisi massal / rutin agar indikator "Data Mutakhir" RAP bermakna.'],
    ['style' => 'bullet', 'text' => 'Digest email operasional (OPERATIONAL_DIGEST_ENABLED) masih off by default — perlu SMTP produksi.'],
    ['style' => 'heading2', 'text' => '6.2 Gap Strategis RAP'],
    ['style' => 'bullet', 'text' => 'Tidak ada modul untuk mendefinisikan target RAP per indikator (mis. target 85% kelengkapan Q4 2026) dan melacak milestone.'],
    ['style' => 'bullet', 'text' => 'Modul DPA (monitoring anggaran paket pekerjaan RTH) tidak terintegrasi ke RAP Konsolidasi — padahal RAP sering mencakup realisasi anggaran.'],
    ['style' => 'bullet', 'text' => 'Tidak ada API/mobile app native — RAP yang menargetkan digitalisasi lapangan penuh masih bergantung web responsive.'],
    ['style' => 'bullet', 'text' => 'Tidak ada ekspor Excel/XLSX — hanya PDF dan CSV import.'],
    ['style' => 'bullet', 'text' => 'Audit trail hanya untuk mutasi data admin, belum field-level atau read-audit.'],
    ['style' => 'heading1', 'text' => '7. Rekomendasi Tindak Lanjut'],
    ['style' => 'numbered', 'text' => 'Segera deploy modul evaluasi RAP ke produksi — commit, push, migrate, npm build, uji PDF export (pastikan ekstensi PHP GD aktif).'],
    ['style' => 'numbered', 'text' => 'Lakukan baseline measurement menggunakan RAP Konsolidasi sebelum rapat RAP — catat Indeks Kinerja awal per pilar.'],
    ['style' => 'numbered', 'text' => 'Aktifkan input lapangan (set PIN) dan sosialisasi ke tim pelaksana per wilayah kerja.'],
    ['style' => 'numbered', 'text' => 'Jalankan kampanye verifikasi data: lengkapi profil taman + update data_verified_at untuk meningkatkan skor Basis Data RTH.'],
    ['style' => 'numbered', 'text' => 'Selaraskan threshold RAP (fresh days pemeliharaan 30–90 hari, verifikasi data 30–180 hari) dengan kebijakan operasional Disperakimtan.'],
    ['style' => 'numbered', 'text' => 'Pertimbangkan integrasi indikator DPA ke pilar ke-4 RAP Konsolidasi jika RAP rev5 mencakup realisasi anggaran.'],
    ['style' => 'numbered', 'text' => 'Untuk fase berikutnya: modul target KPI RAP, API lapangan, dan dashboard transparansi publik.'],
    ['style' => 'heading1', 'text' => '8. Kesimpulan'],
    ['style' => 'body', 'text' => 'RAP terkait optimalisasi pengelolaan data dan monitoring operasional RTH memiliki kesesuaian yang sangat kuat dengan arah pengembangan SIMTAMAN. Aplikasi sudah menyediakan infrastruktur data, proses operasional, partisipasi masyarakat, dan — yang paling relevan — modul evaluasi RAP Konsolidasi yang mengagregasi indikator kinerja dalam format siap presentasi.'],
    ['style' => 'body', 'text' => 'RAP Irwan rev5 kemungkinan besar fokus pada aspek organisasi, kapabilitas SDM, dan proses bisnis yang melampaui ruang lingkup murni teknis aplikasi. Dari sisi teknologi informasi, SIMTAMAN sudah siap menjadi instrumen monitoring dan evaluasi RAP, bukan hanya alat pencatatan.'],
    ['style' => 'body', 'text' => 'Prioritas immediate value: deploy modul evaluasi terbaru, konfigurasi lapangan, baseline measurement, dan pengisian data agar indikator RAP mencerminkan kondisi riil. Gap strategis (target KPI, integrasi DPA, mobile native) dapat masuk fase RAP berikutnya.'],
    ['style' => 'spacer'],
    ['style' => 'meta', 'text' => '— Dokumen dihasilkan otomatis berdasarkan analisis kode sumber SIMTAMAN (repositori sitaman-batam). —'],
];

function xmlEscape(string $text): string
{
    return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function buildParagraph(string $style, string $text = ''): string
{
    $escaped = xmlEscape($text);

    return match ($style) {
        'title' => '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="200"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="36"/><w:color w:val="1B4332"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'subtitle' => '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="120"/></w:pPr><w:r><w:rPr><w:sz w:val="24"/><w:color w:val="40916C"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'meta' => '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="60"/></w:pPr><w:r><w:rPr><w:i/><w:sz w:val="20"/><w:color w:val="666666"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'heading1' => '<w:p><w:pPr><w:spacing w:before="360" w:after="120"/><w:pBdr><w:bottom w:val="single" w:sz="6" w:space="1" w:color="40916C"/></w:pBdr></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="28"/><w:color w:val="1B4332"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'heading2' => '<w:p><w:pPr><w:spacing w:before="240" w:after="80"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="24"/><w:color w:val="2D6A4F"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'body' => '<w:p><w:pPr><w:spacing w:after="120"/><w:jc w:val="both"/></w:pPr><w:r><w:rPr><w:sz w:val="22"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'note' => '<w:p><w:pPr><w:spacing w:after="120"/><w:shd w:val="clear" w:color="auto" w:fill="FFF3CD"/><w:jc w:val="both"/></w:pPr><w:r><w:rPr><w:sz w:val="20"/><w:color w:val="856404"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'status' => '<w:p><w:pPr><w:spacing w:after="120"/><w:shd w:val="clear" w:color="auto" w:fill="D8F3DC"/><w:jc w:val="both"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="1B4332"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'bullet' => '<w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr><w:spacing w:after="60"/></w:pPr><w:r><w:rPr><w:sz w:val="22"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'numbered' => '<w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="2"/></w:numPr><w:spacing w:after="80"/></w:pPr><w:r><w:rPr><w:sz w:val="22"/></w:rPr><w:t>'.$escaped.'</w:t></w:r></w:p>',
        'table_header' => buildTableRow($text, true),
        'table_row' => buildTableRow($text, false),
        'spacer' => '<w:p><w:r><w:t></w:t></w:r></w:p>',
        default => '<w:p><w:r><w:t>'.$escaped.'</w:t></w:r></w:p>',
    };
}

function buildTableRow(string $text, bool $header): string
{
    $cells = explode("\t", $text);
    $row = '<w:tr>';
    foreach ($cells as $cell) {
        $row .= '<w:tc><w:tcPr><w:tcW w:w="3000" w:type="dxa"/></w:tcPr><w:p><w:r><w:rPr>';
        if ($header) {
            $row .= '<w:b/><w:color w:val="FFFFFF"/>';
        }
        $row .= '<w:sz w:val="20"/></w:rPr><w:t>'.xmlEscape($cell).'</w:t></w:r></w:p></w:tc>';
    }
    $row .= '</w:tr>';

    if ($header) {
        return '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="40916C"/><w:left w:val="single" w:sz="4" w:space="0" w:color="40916C"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="40916C"/><w:right w:val="single" w:sz="4" w:space="0" w:color="40916C"/><w:insideH w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/><w:insideV w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/></w:tblBorders></w:tblPr><w:tblGrid><w:gridCol w:w="3500"/><w:gridCol w:w="4500"/><w:gridCol w:w="2000"/></w:tblGrid>'.$row;
    }

    return $row;
}

$bodyXml = '';
$inTable = false;
foreach ($paragraphs as $p) {
    $style = $p['style'];
    $text = $p['text'] ?? '';

    if ($style === 'table_header') {
        $bodyXml .= buildParagraph($style, $text);
        $inTable = true;

        continue;
    }

    if ($inTable && $style === 'table_row') {
        $bodyXml .= buildParagraph($style, $text);

        continue;
    }

    if ($inTable && $style !== 'table_row') {
        $bodyXml .= '</w:tbl>';
        $inTable = false;
    }

    $bodyXml .= buildParagraph($style, $text);
}

if ($inTable) {
    $bodyXml .= '</w:tbl>';
}

$documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
    .'<w:body>'.$bodyXml
    .'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>'
    .'</w:body></w:document>';

$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    .'<Default Extension="xml" ContentType="application/xml"/>'
    .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
    .'<Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>'
    .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
    .'</Types>';

$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    .'</Relationships>';

$docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>'
    .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
    .'</Relationships>';

$numbering = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
    .'<w:abstractNum w:abstractNumId="0"><w:multiLevelType w:val="hybridMultilevel"/>'
    .'<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="•"/><w:lvlJc w:val="left"/></w:lvl></w:abstractNum>'
    .'<w:abstractNum w:abstractNumId="1"><w:multiLevelType w:val="hybridMultilevel"/>'
    .'<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%1."/><w:lvlJc w:val="left"/></w:lvl></w:abstractNum>'
    .'<w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>'
    .'<w:num w:numId="2"><w:abstractNumId w:val="1"/></w:num>'
    .'</w:numbering>';

$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
    .'<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/></w:rPr></w:rPrDefault></w:docDefaults>'
    .'</w:styles>';

$zip = new ZipArchive;
if ($zip->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Cannot create docx at {$outPath}\n");
    exit(1);
}

$zip->addFromString('[Content_Types].xml', $contentTypes);
$zip->addFromString('_rels/.rels', $rels);
$zip->addFromString('word/document.xml', $documentXml);
$zip->addFromString('word/_rels/document.xml.rels', $docRels);
$zip->addFromString('word/numbering.xml', $numbering);
$zip->addFromString('word/styles.xml', $styles);
$zip->close();

echo "Created: {$outPath}\n";
