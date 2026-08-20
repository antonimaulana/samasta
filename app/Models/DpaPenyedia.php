<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DpaPenyedia extends Model
{
    protected $fillable = [
        'nama',
        'pic',
        'jabatan',
        'npwp',
        'no_rekening',
        'company_profile',
        'company_profile_file',
    ];

    public function paketPekerjaans(): HasMany
    {
        return $this->hasMany(DpaPaketPekerjaan::class, 'dpa_penyedia_id');
    }
}
