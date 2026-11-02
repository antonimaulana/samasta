<?php

namespace Tests\Feature;

use App\Models\Taman;
use App\Models\TamanImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TamanGalleryPhotoDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_taman_list_shows_gallery_photo_without_legacy_foto_column(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tamans/sample.jpg', 'x');

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

    public function test_public_and_admin_show_pages_use_shared_gallery_layout(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tamans/photo-a.jpg', 'a');
        Storage::disk('public')->put('tamans/photo-b.jpg', 'b');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $taman = Taman::create([
            'nama_taman' => 'Taman Galeri Konsisten',
            'kategori' => 'Taman Kota',
            'luasan' => 1500,
            'alamat' => 'Alamat galeri konsisten',
            'deskripsi' => 'Deskripsi taman galeri konsisten.',
        ]);

        foreach (['tamans/photo-a.jpg', 'tamans/photo-b.jpg'] as $path) {
            TamanImage::create([
                'taman_id' => $taman->id,
                'path_foto' => $path,
            ]);
        }

        $publicResponse = $this->get(route('tamans.show', $taman))->assertOk();
        $adminResponse = $this->actingAs($admin)
            ->get(route('admin.tamans.show', $taman))
            ->assertOk();

        $publicResponse
            ->assertSee('Galeri Foto', false)
            ->assertSee('2 foto', false)
            ->assertSee('aspect-[16/9]', false)
            ->assertSee('object-cover object-center', false)
            ->assertSee('taman-gallery-'.$taman->id.'-swiper', false)
            ->assertSee('storage/tamans/photo-a.jpg', false)
            ->assertSee('storage/tamans/photo-b.jpg', false);

        $adminResponse
            ->assertSee('Profil RTH / Taman', false)
            ->assertSee('Nama Taman', false)
            ->assertDontSee('Galeri Foto', false)
            ->assertSee('aspect-[16/9]', false)
            ->assertSee('object-cover object-center', false)
            ->assertSee('taman-gallery-'.$taman->id.'-swiper', false)
            ->assertSee('storage/tamans/photo-a.jpg', false)
            ->assertSee('storage/tamans/photo-b.jpg', false);
    }

    public function test_gallery_shows_empty_state_when_no_photos(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $taman = Taman::create([
            'nama_taman' => 'Taman Tanpa Foto',
            'kategori' => 'Taman Kota',
            'luasan' => 800,
            'alamat' => 'Alamat tanpa foto',
            'deskripsi' => 'Deskripsi tanpa foto.',
        ]);

        $this->get(route('tamans.show', $taman))
            ->assertOk()
            ->assertSee('Galeri Foto', false)
            ->assertDontSee('swiper', false);

        $this->actingAs($admin)
            ->get(route('admin.tamans.show', $taman))
            ->assertOk()
            ->assertSee('Tidak ada foto', false);
    }

    public function test_admin_can_reorder_gallery_images_and_cover_follows_first_position(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tamans/first.jpg', 'a');
        Storage::disk('public')->put('tamans/second.jpg', 'b');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $taman = Taman::create([
            'nama_taman' => 'Taman Urutan Foto',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat urutan',
            'deskripsi' => 'Deskripsi urutan galeri.',
        ]);

        $first = TamanImage::create([
            'taman_id' => $taman->id,
            'path_foto' => 'tamans/first.jpg',
            'urutan' => 10,
        ]);
        $second = TamanImage::create([
            'taman_id' => $taman->id,
            'path_foto' => 'tamans/second.jpg',
            'urutan' => 20,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.tamans.images.reorder', $taman), [
                'order' => [$second->id, $first->id],
            ])
            ->assertRedirect(route('admin.tamans.edit', $taman))
            ->assertSessionHas('success');

        $this->assertSame(10, $second->fresh()->urutan);
        $this->assertSame(20, $first->fresh()->urutan);

        $taman->load('images');
        $this->assertSame('tamans/second.jpg', $taman->images->first()->path_foto);
        $this->assertStringContainsString('storage/tamans/second.jpg', (string) $taman->foto_url);
    }
}
