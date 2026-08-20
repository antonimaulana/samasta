<?php

namespace Tests\Feature;

use Tests\TestCase;

class MasukanPageTest extends TestCase
{
    public function test_masukan_page_shows_aduan_and_survey_choices(): void
    {
        $this->get(route('masukan.index'))
            ->assertOk()
            ->assertSee('Aduan Masyarakat')
            ->assertSee('Survey Kepuasan')
            ->assertSee(route('aduan.create'), false)
            ->assertSee(route('survey.create'), false);
    }
}
