<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pejabat extends Model
{
    protected $fillable = [
        'nama',
        'jabatan',
        'image_path',
        'accent_bg',
        'accent_ring',
        'photo_class',
        'photo_frame_class',
        'urutan',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return array{name: string, jabatan: string, image: string, accent_bg: string, accent_ring: string, photo_class?: string, photo_frame_class?: string}
     */
    public function toCardArray(): array
    {
        $data = [
            'name' => $this->nama,
            'jabatan' => $this->jabatan,
            'image' => $this->image_path ?? '',
            'accent_bg' => $this->accent_bg,
            'accent_ring' => $this->accent_ring,
        ];

        if (filled($this->photo_class)) {
            $data['photo_class'] = $this->photo_class;
        }

        if (filled($this->photo_frame_class)) {
            $data['photo_frame_class'] = $this->photo_frame_class;
        }

        return $data;
    }
}
