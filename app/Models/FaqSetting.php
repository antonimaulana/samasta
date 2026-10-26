<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqSetting extends Model
{
    protected $fillable = [
        'section_badge',
        'section_title',
        'section_subtitle',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'section_badge' => 'FAQ',
            'section_title' => 'Pertanyaan yang Sering Diajukan',
            'section_subtitle' => null,
        ]);
    }
}
