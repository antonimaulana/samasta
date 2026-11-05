<?php

namespace Tests\Feature;

use App\Models\BerandaMascotTip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BerandaMascotTipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BerandaMascotTip::seedDefaults();
    }

    public function test_home_includes_tama_tips_payload(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="tama-tip-root"', false)
            ->assertSee('Hai, saya Tama!', false)
            ->assertSee('id="home-hero"', false)
            ->assertSee('"section_key":"hero"', false);
    }

    public function test_admin_can_update_mascot_tip(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $tip = BerandaMascotTip::query()->where('section_key', BerandaMascotTip::SECTION_HERO)->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.beranda-mascot-tips.update', $tip), [
                'title' => 'Halo dari Tama',
                'body' => 'Teks uji persona Tama.',
                'image_asset' => 'images/mascot/tama/hero.png',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.beranda-mascot-tips.index'));

        $this->assertDatabaseHas('beranda_mascot_tips', [
            'id' => $tip->id,
            'title' => 'Halo dari Tama',
        ]);
    }
}
