<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnsiklopediaPortalSetting extends Model
{
    protected $fillable = [
        'hero_badge',
        'hero_title',
        'hero_description',
        'search_placeholder',
        'interaktif_title',
        'interaktif_subtitle',
        'quiz_button_label',
        'interaktif_config',
    ];

    protected function casts(): array
    {
        return [
            'interaktif_config' => 'array',
        ];
    }
}
