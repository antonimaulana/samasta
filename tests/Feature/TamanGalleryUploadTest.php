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

    public function test_admin_can_upload_gallery_photo_up_to_ten_megabytes(): void
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
            'nama_taman' => 'Taman Uji Foto Besar',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 1000,
            'alamat' => 'Jl. Uji',
            'deskripsi' => 'Deskripsi uji upload foto hingga 10 MB.',
            'fotos' => [
                UploadedFile::fake()->image('besar.jpg', 1200, 800)->size(9000),
            ],
        ]);

        $response->assertRedirect(route('admin.tamans.index'));
        $response->assertSessionDoesntHaveErrors('fotos.0');

        $this->assertTrue(
            Taman::query()->where('nama_taman', 'Taman Uji Foto Besar')->exists()
        );
    }

    public function test_admin_cannot_upload_gallery_photo_over_ten_megabytes(): void
    {
        Storage::fake('public');
        $this->seed(WilayahBatamSeeder::class);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahan = Kelurahan::query()->first();
        $this->assertNotNull($kelurahan);

        $response = $this->actingAs($admin)->post(route('admin.tamans.store'), [
            'nama_taman' => 'Taman Uji Foto Terlalu Besar',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 1000,
            'alamat' => 'Jl. Uji',
            'deskripsi' => 'Deskripsi uji batas ukuran.',
            'fasilitas_items' => [
                ['nama' => 'Jogging Track', 'kondisi' => 'Baik'],
            ],
            'fotos' => [
                UploadedFile::fake()->image('terlalu-besar.jpg', 800, 600)->size(Taman::GALLERY_MAX_UPLOAD_KILOBYTES + 512),
            ],
        ]);

        $response->assertSessionHasErrors('fotos.0');
        $this->assertFalse(
            Taman::query()->where('nama_taman', 'Taman Uji Foto Terlalu Besar')->exists()
        );
    }

    public function test_admin_can_upload_multiple_gallery_photos_at_once(): void
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
            'nama_taman' => 'Taman Uji Multi Foto',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 1000,
            'alamat' => 'Jl. Uji',
            'deskripsi' => 'Deskripsi uji multi upload.',
            'fasilitas_items' => [
                ['nama' => 'Jogging Track', 'kondisi' => 'Baik'],
            ],
            'fotos' => [
                UploadedFile::fake()->image('a.jpg', 1600, 900),
                UploadedFile::fake()->image('b.jpg', 1200, 800),
                UploadedFile::fake()->image('c.jpg', 900, 1600),
            ],
        ]);

        $response->assertRedirect(route('admin.tamans.index'));

        $taman = Taman::query()->where('nama_taman', 'Taman Uji Multi Foto')->first();
        $this->assertNotNull($taman);
        $this->assertCount(3, $taman->images);
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
