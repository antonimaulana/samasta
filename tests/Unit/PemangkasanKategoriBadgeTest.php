<?php

namespace Tests\Unit;

use App\Models\Pemangkasan;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PemangkasanKategoriBadgeTest extends TestCase
{
    #[DataProvider('kategoriBadgeProvider')]
    public function test_kategori_badge_class_is_distinct_per_category(string $kategori, string $expectedFragment): void
    {
        $class = Pemangkasan::kategoriBadgeClass($kategori);

        $this->assertStringContainsString($expectedFragment, $class);
    }

    public static function kategoriBadgeProvider(): array
    {
        return [
            'Instruksi' => ['Instruksi', 'indigo'],
            'Laporan Masyarakat' => ['Laporan Masyarakat', 'rose'],
            'Survei Lapangan' => ['Survei Lapangan', 'cyan'],
        ];
    }

    public function test_unknown_kategori_uses_neutral_badge(): void
    {
        $this->assertSame('bg-gray-100 text-gray-700 ring-1 ring-gray-200', Pemangkasan::kategoriBadgeClass('Lainnya'));
    }
}
