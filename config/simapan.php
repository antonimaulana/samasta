<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WebAR — Profil RTH via Scan QR
    |--------------------------------------------------------------------------
    */

    'ar' => [
        /** GLB/GLTF model shown in AR mode (override via SIMAPAN_AR_MODEL_URL). */
        'model_url' => env(
            'SIMAPAN_AR_MODEL_URL',
            'https://modelviewer.dev/shared-assets/models/Astronaut.glb'
        ),

        /** Profil dianggap segar jika diverifikasi dalam N hari terakhir. */
        'data_fresh_days' => (int) env('SIMAPAN_AR_DATA_FRESH_DAYS', 90),

        /** Pemeliharaan dianggap "Terawat" jika ada catatan dalam N hari terakhir. */
        'maintenance_fresh_days' => (int) env('SIMAPAN_AR_MAINTENANCE_FRESH_DAYS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Laporan RTH — Rekap tahunan di halaman Laporan Taman
    |--------------------------------------------------------------------------
    */

    'rth_laporan' => [
        /** Luasan RTH publik sesuai RTRW Kota Batam (m²). */
        'luasan_rth_publik_rtrw_m2' => (int) env('SIMAPAN_RTH_RTRW_LUASAN_M2', 52_990_000),

        /** Penyesuaian luasan tidak terpelihara: E = A − nilai ini. */
        'luasan_penyesuaian_tidak_terpelihara_m2' => (int) env('SIMAPAN_RTH_PENYESUAIAN_TIDAK_TERPELIHARA_M2', 556_600),

        /** Kolom tahun pada tabel rekap (perhitungan kumulatif tahun pemutakhiran ke bawah). */
        'tahun' => [2025, 2026],
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Lapangan — akses petugas tanpa akun login
    |--------------------------------------------------------------------------
    |
    | Isi SIMAPAN_LAPANGAN_PIN untuk mengaktifkan input lapangan tanpa login.
    | (Nama env SIMAPAN_* dipertahankan untuk kompatibilitas deployment.)
    | Petugas cukup memasukkan PIN sekali per sesi perangkat.
    |
    */

    'lapangan' => [
        'pin' => env('SIMAPAN_LAPANGAN_PIN'),
        'session_key' => 'lapangan_guest_unlocked_at',
        'session_ttl_minutes' => (int) env('SIMAPAN_LAPANGAN_SESSION_TTL', 480),
    ],

];
