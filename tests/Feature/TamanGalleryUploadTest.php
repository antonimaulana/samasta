<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TamanGalleryUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_non_sixteen_by_nine_gallery_photo(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension required for gallery normalization.');
        }

        Storage::fake('public');
        $this->seed(WilayahBatamSeeder::class);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahan = Kelurahan::query()->first();
        $this->assertNotNull($kelurahan);

        $response = $this->actingAs($admin)->post(route('admin.tamans.store'), [
            'nama_taman' => 'Taman Uji Upload',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 1000,
            'alamat' => 'Jl. Uji',
            'deskripsi' => 'Deskripsi uji upload foto fleksibel.',
            'fasilitas_items' => [
                ['nama' => 'Jogging Track', 'kondisi' => 'Baik'],
            ],
            'fotos' => [
                UploadedFile::fake()->image('portrait.jpg', 900, 1600),
            ],
        ]);

        $response->assertRedirect(route('admin.tamans.index'));

        $taman = Taman::query()->where('nama_taman', 'Taman Uji Upload')->first();
        $this->assertNotNull($taman);
        $this->assertCount(1, $taman->images);

        $path = $taman->images->first()->path_foto;
        $this->assertStringEndsWith('.jpg', $path);

        $absolute = Storage::disk('public')->path($path);
        $size = @getimagesize($absolute);
        $this->assertIsArray($size);
        $this->assertSame(Taman::GALLERY_RECOMMENDED_WIDTH, $size[0]);
        $this->assertSame(Taman::GALLERY_RECOMMENDED_HEIGHT, $size[1]);
    }

    public function test_admin_can_upload_gallery_photo_larger_than_two_megabytes(): void
    {
        Storage::fake('public');
        $this->seed(WilayahBatamSeeder::class);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahan = Kelurahan::query()->first();
        $this->assertNotNull($kelurahan);

        $response = $this->actingAs($admin)->post(route('admin.tamans.store'), [
            'nama_taman' => 'Taman Uji Foto Besar',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 1000,
            'alamat' => 'Jl. Uji',
            'deskripsi' => 'Deskripsi uji upload foto tanpa batas 2 MB.',
            'fotos' => [
                UploadedFile::fake()->image('besar.jpg', 1200, 800)->size(3072),
            ],
        ]);

        $response->assertRedirect(route('admin.tamans.index'));
        $response->assertSessionDoesntHaveErrors('fotos.0');

        $this->assertTrue(
            Taman::query()->where('nama_taman', 'Taman Uji Foto Besar')->exists()
        );
    }

    public function test_deleting_gallery_image_from_edit_redirects_back_to_edit(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $taman = Taman::create([
            'nama_taman' => 'Taman Hapus Foto',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
        ]);

        $image = $taman->images()->create(['path_foto' => 'tamans/test.jpg']);
        Storage::disk('public')->put('tamans/test.jpg', 'fake');

        $this->actingAs($admin)
            ->delete(route('admin.tamans.images.destroy', [$taman, $image]))
            ->assertRedirect(route('admin.tamans.edit', $taman))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('taman_images', ['id' => $image->id]);
    }
}
