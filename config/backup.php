<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lokasi & retensi backup SIMTAMAN
    |--------------------------------------------------------------------------
    |
    | Production (VPS): set BACKUP_PATH=/var/backups/samasta dan pastikan
    | direktori dimiliki user web (www-data) dengan chmod 750.
    |
    */

    'path' => env('BACKUP_PATH', storage_path('app/backups')),

    'keep_days' => (int) env('BACKUP_KEEP_DAYS', 30),

    'mysqldump_binary' => env('BACKUP_MYSQLDUMP_PATH', 'mysqldump'),

];
