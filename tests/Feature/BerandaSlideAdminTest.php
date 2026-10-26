<?php

namespace Tests\Feature;

use App\Models\BerandaSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BerandaSlideAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_slides_from_database(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Informasi Terkini')
            ->assertSee('Jumlah Luas Ruang Terbuka Hijau Kota Batam');
    }

    public function test_admin_can_update_beranda_slide(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $slide = BerandaSlide::query()->where('slug', 'rth-kota')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.beranda-slides.update', $slide), [
                'jenis' => BerandaSlide::JENIS_STANDARD,
                'title_attr' => 'RTH Test',
                'route_name' => 'rth.index',
                'heading' => 'Judul RTH Diubah',
                'description' => 'Deskripsi uji.',
                'cta_text' => 'Lihat selengkapnya',
                'badge' => 'Kategori RTH',
                'visual_mode' => BerandaSlide::VISUAL_LIVE,
                'left_gradient' => 'from-green-400 to-emerald-500',
                'hero_mode' => 'single',
                'hero_live_preset' => 'rth',
                'show_stats' => '1',
                'tags_text' => "Tag A\nTag B",
            ])
            ->assertRedirect(route('admin.beranda-slides.index'));

        $slide->refresh();
        $this->assertSame('Judul RTH Diubah', $slide->heading);
    }

    public function test_admin_can_create_and_delete_beranda_slide(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.beranda-slides.store'), [
                'jenis' => BerandaSlide::JENIS_STANDARD,
                'heading' => 'Slide Baru Uji',
                'description' => 'Deskripsi slide baru.',
                'cta_text' => 'Lihat selengkapnya',
                'badge' => 'Info',
                'visual_mode' => BerandaSlide::VISUAL_GRADIENT,
                'left_gradient' => 'from-green-400 to-emerald-500',
                'hero_mode' => 'single',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.beranda-slides.index'));

        $slide = BerandaSlide::query()->where('heading', 'Slide Baru Uji')->first();
        $this->assertNotNull($slide);
        $this->assertSame('slide-baru-uji', $slide->slug);

        $this->actingAs($admin)
            ->delete(route('admin.beranda-slides.destroy', $slide))
            ->assertRedirect(route('admin.beranda-slides.index'));

        $this->assertDatabaseMissing('beranda_slides', ['id' => $slide->id]);
    }

    public function test_show_route_redirects_to_edit(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $slide = BerandaSlide::query()->where('slug', 'rth-kota')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.beranda-slides.show', $slide))
            ->assertRedirect(route('admin.beranda-slides.edit', $slide));
    }

    public function test_operator_cannot_access_beranda_slides(): void
    {
        $operator = User::factory()->operatorTeams([], allWilayah: true)->create();

        $this->actingAs($operator)
            ->get(route('admin.beranda-slides.index'))
            ->assertForbidden();
    }
}
