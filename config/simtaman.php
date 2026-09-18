<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WebAR — Profil RTH via Scan QR
    |--------------------------------------------------------------------------
    */

    'ar' => [
        /** GLB/GLTF model shown in AR mode (override via SIMTAMAN_AR_MODEL_URL). */
        'model_url' => env(
            'SIMTAMAN_AR_MODEL_URL',
            env(
                'SIMAPAN_AR_MODEL_URL',
                'https://modelviewer.dev/shared-assets/models/Astronaut.glb'
            )
        ),

        /** Profil dianggap segar jika diverifikasi dalam N hari terakhir. */
        'data_fresh_days' => (int) env('SIMTAMAN_AR_DATA_FRESH_DAYS', env('SIMAPAN_AR_DATA_FRESH_DAYS', 90)),

        /** Pemeliharaan dianggap "Terawat" jika ada catatan dalam N hari terakhir. */
        'maintenance_fresh_days' => (int) env('SIMTAMAN_AR_MAINTENANCE_FRESH_DAYS', env('SIMAPAN_AR_MAINTENANCE_FRESH_DAYS', 60)),
    ],

    /*
    |--------------------------------------------------------------------------
    | Laporan RTH — Rekap tahunan di halaman Laporan Taman
    |--------------------------------------------------------------------------
    */

    'rth_laporan' => [
        /** Luasan RTH publik sesuai RTRW Kota Batam (m²). */
        'luasan_rth_publik_rtrw_m2' => (int) env('SIMTAMAN_RTH_RTRW_LUASAN_M2', env('SIMAPAN_RTH_RTRW_LUASAN_M2', 52_990_000)),

        /** Penyesuaian luasan tidak terpelihara: E = A − nilai ini. */
        'luasan_penyesuaian_tidak_terpelihara_m2' => (int) env('SIMTAMAN_RTH_PENYESUAIAN_TIDAK_TERPELIHARA_M2', env('SIMAPAN_RTH_PENYESUAIAN_TIDAK_TERPELIHARA_M2', 556_600)),

        /** Kolom tahun pada tabel rekap (perhitungan kumulatif tahun pemutakhiran ke bawah). */
        'tahun' => [2025, 2026],
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Lapangan — akses petugas tanpa akun login
    |--------------------------------------------------------------------------
    |
    | Isi SIMTAMAN_LAPANGAN_PIN untuk mengaktifkan input lapangan tanpa login.
    | Petugas cukup memasukkan PIN sekali per sesi perangkat.
    |
    */

    'lapangan' => [
        'pin' => env('SIMTAMAN_LAPANGAN_PIN', env('SIMAPAN_LAPANGAN_PIN')),
        'session_key' => 'lapangan_guest_unlocked_at',
        'session_ttl_minutes' => (int) env('SIMTAMAN_LAPANGAN_SESSION_TTL', env('SIMAPAN_LAPANGAN_SESSION_TTL', 480)),
    ],

];
