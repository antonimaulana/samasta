<?php

namespace App\Models;

use App\Support\DpaMonitoring;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DpaDocumentTemplate extends Model
{
    protected $fillable = [
        'tahap',
        'kode',
        'nama',
        'template_path',
    ];

    public function dokumenLabel(): string
    {
        return DpaMonitoring::dokumenLabel($this->kode);
    }
}
