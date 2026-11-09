<?php

namespace Tests\Unit;

use App\Models\Pemangkasan;
use App\Support\PermohonanPelaksanaProgress;
use PHPUnit\Framework\TestCase;

class PermohonanFotoDocumentationTest extends TestCase
{
    public function test_collects_foto_for_non_mini_garden(): void
    {
        $permohonan = new Pemangkasan([
            'jenis_layanan' => 'Pemangkasan Pohon',
            'pelaksana' => ['Tim Wilayah 1'],
        ]);

        $this->assertTrue(PermohonanPelaksanaProgress::shouldCollectFotoDocumentation($permohonan, 'Tim Wilayah 1'));
    }

    public function test_mini_garden_requires_foto_only_for_nursery_tim(): void
    {
        $permohonan = new Pemangkasan([
            'jenis_layanan' => 'Pemasangan Mini Garden',
            'pelaksana' => ['Tim Wilayah 1', 'Tim Nursery'],
        ]);

        $this->assertFalse(PermohonanPelaksanaProgress::shouldCollectFotoDocumentation($permohonan, 'Tim Wilayah 1'));
        $this->assertTrue(PermohonanPelaksanaProgress::shouldCollectFotoDocumentation($permohonan, 'Tim Nursery'));
        $this->assertTrue(PermohonanPelaksanaProgress::shouldCollectFotoDocumentation($permohonan, null));
    }
}
