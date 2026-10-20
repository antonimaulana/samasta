<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerandaSlide extends Model
{
    public const JENIS_STANDARD = 'standard';

    public const JENIS_SIMPONI = 'simponi';

    public const JENIS_PERDA = 'perda';

    protected $fillable = [
        'slug',
        'jenis',
        'is_active',
        'urutan',
        'title_attr',
        'route_name',
        'href',
        'left_gradient',
        'badge',
        'hero_mode',
        'hero_live_preset',
        'hero_value',
        'hero_label',
        'hero_dual_left_value',
        'hero_dual_left_label',
        'hero_dual_right_value',
        'hero_dual_right_label',
        'show_stats',
        'stat_left_value',
        'stat_left_label',
        'stat_right_value',
        'stat_right_label',
        'heading',
        'description',
        'tags',
        'tag_classes',
        'card_ring',
        'card_shadow',
        'cta_text',
        'cta_color',
        'heading_hover',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_stats' => 'boolean',
            'tags' => 'array',
            'config' => 'array',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function activeOrdered()
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
    }
}
