<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlatSaranaOperasional extends Model
{
    public const JENIS = [
        'Alat Manual',
        'Alat Mesin',
        'Alat Kebersihan',
        'Dump Truck',
        'Truck',
        'Crane',
        'Kendaraan Operasional',
        'Lainnya',
    ];

    public const KONDISI = [
        'Baik',
        'Rusak Ringan',
        'Rusak Berat',
        'Hilang',
    ];

    public const ARMADA_JENIS = [
        'Dump Truck',
        'Truck',
        'Crane',
        'Kendaraan Operasional',
    ];

    protected $fillable = [
        'nama',
        'jenis',
        'no_plat',
        'sopir',
        'jumlah',
        'peruntukan',
        'kondisi',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    /**
     * @return list<string>
     */
    public static function timOptions(): array
    {
        if (TimPelaksana::query()->exists()) {
            return TimPelaksana::activeNames();
        }

        return PemeliharaanTaman::timNames();
    }

    public static function isArmadaJenis(string $jenis): bool
    {
        return in_array($jenis, self::ARMADA_JENIS, true);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeArmadaInventory($query)
    {
        return $query
            ->where('peruntukan', PemeliharaanTaman::TIM_ARMADA)
            ->whereIn('jenis', self::ARMADA_JENIS);
    }
}
