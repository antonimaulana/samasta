<?php

namespace Tests\Feature;

use Tests\TestCase;

class PeraturanPublicPageTest extends TestCase
{
    public function test_peraturan_page_is_accessible_and_shows_key_content(): void
    {
        $response = $this->get(route('peraturan.index'));

        $response->assertOk()
            ->assertSee('Peraturan Taman &amp; Ruang Hijau', false)
            ->assertSee('Jangan rusak tanaman', false)
            ->assertSee('Buat Aduan Masyarakat', false);
    }
}
