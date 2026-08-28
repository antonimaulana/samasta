<?php

namespace Tests\Unit;

use App\Models\Taman;
use PHPUnit\Framework\TestCase;

class TamanGalleryAspectRatioTest extends TestCase
{
    public function test_accepts_landscape_sixteen_by_nine_within_tolerance(): void
    {
        $this->assertTrue(Taman::isValidGalleryAspectRatio(1920, 1080));
        $this->assertTrue(Taman::isValidGalleryAspectRatio(1280, 720));
    }

    public function test_rejects_portrait_and_non_sixteen_by_nine_ratios(): void
    {
        $this->assertFalse(Taman::isValidGalleryAspectRatio(1080, 1920));
        $this->assertFalse(Taman::isValidGalleryAspectRatio(1200, 800));
    }
}
