<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RthKategori extends Model
{
    protected $fillable = [
        'nama',
        'luas',
        'lokasi',
        'icon',
        'ringkas',
        'urutan',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'luas' => 'integer',
            'lokasi' => 'integer',
            'urutan' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return array{nama: string, luas: int, lokasi: int, icon: string, ringkas: string}
     */
    public function toSummaryArray(): array
    {
        return [
            'nama' => $this->nama,
            'luas' => $this->luas,
            'lokasi' => $this->lokasi,
            'icon' => $this->icon,
            'ringkas' => $this->ringkas ?? '',
        ];
    }
}
