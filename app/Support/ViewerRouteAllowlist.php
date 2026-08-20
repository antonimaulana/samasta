<?php

namespace App\Support;

class ViewerRouteAllowlist
{
    /**
     * Route admin yang boleh diakses role viewer (read-only, drill-down dari dashboard).
     *
     * @return list<string>
     */
    public static function routes(): array
    {
        return [
            'admin.dashboard',
            'admin.dashboard.export-pdf',
            'admin.jadwal-layanan.index',
            'admin.pemangkasans.index',
            'admin.pemangkasans.show',
            'admin.aduan-masyarakats.index',
            'admin.aduan-masyarakats.show',
            'admin.survey-kepuasan.index',
            'admin.operasional-pertamanan-laporan.index',
            'admin.operasional-pertamanan-laporan.export-pdf',
            'admin.tamans.index',
            'admin.tamans.show',
            'admin.tamans.export-pdf',
            'admin.bibits.index',
            'admin.bibits.show',
            'admin.bibit-laporan.index',
            'admin.bibit-laporan.export-pdf',
            'admin.pemeliharaan-tamans.index',
            'admin.pemeliharaan-tamans.export-pdf',
            'admin.pemeliharaan-tamans.export-pdf-operasional',
            'admin.alat-sarana-operasionals.index',
        ];
    }

    public static function allows(?string $routeName): bool
    {
        return filled($routeName) && in_array($routeName, self::routes(), true);
    }
}
