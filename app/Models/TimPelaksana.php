<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class TimPelaksana extends Model
{
    protected $fillable = [
        'nama',
        'nama_pengawas',
        'memiliki_wilayah_kerja',
        'aktif',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'memiliki_wilayah_kerja' => 'boolean',
            'aktif' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function kelurahans(): BelongsToMany
    {
        return $this->belongsToMany(Kelurahan::class, 'kelurahan_tim_pelaksana')
            ->withTimestamps();
    }

    /**
     * Set wilayah kerja; each kelurahan may only belong to one tim.
     *
     * @param  list<int>  $kelurahanIds
     */
    public function assignWilayahKerja(array $kelurahanIds): void
    {
        if ($kelurahanIds !== []) {
            DB::table('kelurahan_tim_pelaksana')
                ->whereIn('kelurahan_id', $kelurahanIds)
                ->where('tim_pelaksana_id', '!=', $this->id)
                ->delete();
        }

        $this->kelurahans()->sync($kelurahanIds);
    }

    /**
     * @return list<string>
     */
    public static function activeNames(): array
    {
        return static::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->pluck('nama')
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function legacyNames(): array
    {
        return [
            'Tim Wilayah 1',
            'Tim Wilayah 2',
            'Tim Wilayah 3',
            'Tim Wilayah 4',
            'Tim Nursery',
            'Tim Armada',
        ];
    }
}
