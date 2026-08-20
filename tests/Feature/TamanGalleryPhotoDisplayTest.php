<?php

namespace Tests\Feature;

use App\Models\Taman;
use App\Models\TamanImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanGalleryPhotoDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_taman_list_shows_gallery_photo_without_legacy_foto_column(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Galeri Test',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat galeri test',
            'deskripsi' => 'Deskripsi taman galeri test.',
        ]);

        TamanImage::create([
            'taman_id' => $taman->id,
            'path_foto' => 'tamans/sample.jpg',
        ]);

        $this->get(route('tamans.index'))
            ->assertOk()
            ->assertSee('storage/tamans/sample.jpg', false);
    }
}
