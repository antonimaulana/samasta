<?php

namespace Tests\Unit;

use App\Models\Taman;
use App\Support\TamanGalleryImageNormalizer;
use Tests\TestCase;

class TamanGalleryAspectRatioTest extends TestCase
{
    public function test_cover_crop_rect_for_landscape_wider_than_sixteen_by_nine(): void
    {
        [$x, $y, $w, $h] = TamanGalleryImageNormalizer::coverCropRect(2000, 1000);

        $this->assertSame(0, $y);
        $this->assertSame(1000, $h);
        $this->assertEqualsWithDelta(16 / 9, $w / $h, 0.01);
    }

    public function test_cover_crop_rect_for_portrait_and_four_by_three(): void
    {
        [$x, $y, $w, $h] = TamanGalleryImageNormalizer::coverCropRect(1080, 1920);

        $this->assertSame(0, $x);
        $this->assertEqualsWithDelta(16 / 9, $w / $h, 0.01);

        [$x2, $y2, $w2, $h2] = TamanGalleryImageNormalizer::coverCropRect(1200, 900);
        $this->assertEqualsWithDelta(16 / 9, $w2 / $h2, 0.01);
    }

    public function test_legacy_aspect_helper_still_detects_exact_sixteen_by_nine(): void
    {
        $this->assertTrue(Taman::isValidGalleryAspectRatio(1920, 1080));
    }
}
