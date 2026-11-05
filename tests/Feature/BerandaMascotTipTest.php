<?php

namespace Tests\Feature;

use App\Models\BerandaMascotSetting;
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
            ->assertSee('"section_key":"hero"', false)
            ->assertSee('js/tama-tips.js', false);
    }

    public function test_home_hides_tama_when_popups_globally_disabled(): void
    {
        BerandaMascotSetting::current()->update(['popups_enabled' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="tama-tip-root"', false);
    }

    public function test_admin_can_toggle_global_popup_setting(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->put(route('admin.beranda-mascot-tips.settings.update'), [
                'popups_enabled' => '0',
            ])
            ->assertRedirect();

        $this->assertFalse(BerandaMascotSetting::current()->fresh()->popups_enabled);
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
