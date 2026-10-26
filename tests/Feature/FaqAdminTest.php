<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\FaqSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_faq_section(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('faq-beranda', false)
            ->assertSee(FaqSetting::current()->section_title);
    }

    public function test_admin_can_manage_faq(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->put(route('admin.faq.settings.update'), [
                'section_badge' => 'FAQ',
                'section_title' => 'Tanya Jawab',
            ])
            ->assertRedirect();

        $this->assertSame('Tanya Jawab', FaqSetting::current()->fresh()->section_title);

        $this->actingAs($admin)
            ->post(route('admin.faq.store'), [
                'question' => 'Pertanyaan uji?',
                'answer' => 'Jawaban uji.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.faq.index'));

        $faq = Faq::query()->where('question', 'Pertanyaan uji?')->first();
        $this->assertNotNull($faq);

        $this->get(route('home'))
            ->assertSee('Pertanyaan uji?')
            ->assertSee('Jawaban uji.');
    }

    public function test_operator_cannot_access_faq_admin(): void
    {
        $operator = User::factory()->operatorTeams([], allWilayah: true)->create();

        $this->actingAs($operator)
            ->get(route('admin.faq.index'))
            ->assertForbidden();
    }
}
