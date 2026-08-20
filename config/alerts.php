<?php

return [
    'bibit' => [
        'minimum_stock' => (int) env('ALERT_BIBIT_MIN_STOCK', 10),
    ],

    'aduan' => [
        'unreviewed_days' => (int) env('ALERT_ADUAN_UNREVIEWED_DAYS', 3),
        'unreviewed_status' => 'Baru',
    ],

    'layanan' => [
        'diproses_days' => (int) env('ALERT_LAYANAN_DIPROSES_DAYS', 7),
        'stuck_status' => 'Diproses',
    ],

    'digest' => [
        'enabled' => (bool) env('OPERATIONAL_DIGEST_ENABLED', false),
        'recipients' => env('OPERATIONAL_DIGEST_RECIPIENTS', ''),
    ],
];
