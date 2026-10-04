<?php

declare(strict_types=1);

/**
 * Lampiran KAK — flowchart ringkas SIMTAMAN (±1 halaman A4).
 *
 * Usage: php scripts/generate-lampiran-flowchart-kak-docx.php
 */
require __DIR__.'/docx-builder.php';

$outPath = dirname(__DIR__).'/docs/LAMPIRAN-KAK-FLOWCHART-SIMTAMAN-1-HALAMAN.docx';

$diagramLines = [
    '  [Masyarakat] ──────► [Portal Publik] ──────► [Database + Storage]',
    '                              ▲                        ▲',
    '  [Admin/Ops] ───────► [/admin + RBAC] ────────────────┤',
    '                              │                        │',
    '                              ├── Export/Import CSV ───┤',
    '                              │                        │',
    '  [Petugas] ──PIN──► [/lapangan] ──► Pemeliharaan & Progres layanan',
    '                              └────────────────────────┘',
    '                                         │',
    '                                         ▼',
    '                    [Evaluasi RAP + Dashboard + PDF] ──► Keputusan Disperakimtan',
];

$paragraphs = [
    ['style' => 'title', 'text' => 'LAMPIRAN KAK'],
    ['style' => 'subtitle', 'text' => 'Flowchart Ringkas Rancangan Sistem SIMTAMAN (1 Halaman)'],
    ['style' => 'meta', 'text' => 'Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam'],
    ['style' => 'meta', 'text' => 'Versi 1.0 · September 2026'],
    ['style' => 'spacer'],

    ['style' => 'heading2', 'text' => 'Diagram integrasi kanal & data'],
    ['style' => 'note', 'text' => 'Diagram interaktif (Mermaid): docs/LAMPIRAN-KAK-FLOWCHART-SIMTAMAN-1-HALAMAN.md · Detail: docs/FLOWCHART-RANCANGAN-SISTEM-SIMTAMAN.md'],
];

foreach ($diagramLines as $line) {
    $paragraphs[] = ['style' => 'diagram', 'text' => $line];
}

$paragraphs = array_merge($paragraphs, [
    ['style' => 'spacer'],
    ['style' => 'heading2', 'text' => 'Alur operasional utama'],
    ['style' => 'table_header', 'text' => "No\tProses\tInput\tKeluaran"],
    ['style' => 'table_row', 'text' => "1\tProfil taman\tForm admin / CSV\tStatus lengkap, portal & peta"],
    ['style' => 'table_row', 'text' => "2\tPemeliharaan rutin\tAdmin atau lapangan (foto, petugas)\tRecord operasional + evaluasi RTH"],
    ['style' => 'table_row', 'text' => "3\tPermohonan layanan\tJadwal admin → progres harian lapangan\tPersentase + PDF progres"],
    ['style' => 'table_row', 'text' => "4\tPartisipasi publik\tAduan & survey\tTindak lanjut + evaluasi masukan"],
    ['style' => 'table_row', 'text' => "5\tMonitoring\tAgregasi data\tDashboard, evaluasi RAP, laporan PDF"],

    ['style' => 'heading2', 'text' => 'Kanal & autentikasi'],
    ['style' => 'table_header', 'text' => "Kanal\tAutentikasi\tFungsi"],
    ['style' => 'table_row', 'text' => "Portal /\tTanpa login\tInformasi taman/RTH, WebAR, aduan, survey"],
    ['style' => 'table_row', 'text' => "/admin\tSession login (4 peran)\tKelola data, operasional, evaluasi"],
    ['style' => 'table_row', 'text' => "/lapangan\tPIN .env atau user Operator/Admin\tInput mobile operasional"],

    ['style' => 'spacer'],
    ['style' => 'meta', 'text' => 'Regenerasi: php scripts/generate-lampiran-flowchart-kak-docx.php'],
]);

writeDocx($outPath, $paragraphs);

echo "Written: {$outPath}\n";
