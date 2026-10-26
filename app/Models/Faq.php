<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'is_active',
        'urutan',
        'question',
        'answer',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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
