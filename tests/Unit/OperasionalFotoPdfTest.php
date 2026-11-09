<?php

namespace Tests\Unit;

use App\Support\OperasionalFotoPdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperasionalFotoPdfTest extends TestCase
{
    public function test_embedded_src_resizes_large_image(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required.');
        }

        Storage::fake('public');

        $image = imagecreatetruecolor(2000, 1500);
        ob_start();
        imagejpeg($image, null, 90);
        imagedestroy($image);
        $jpeg = ob_get_clean();

        Storage::disk('public')->put('pemeliharaan-taman/large.jpg', $jpeg);

        $src = OperasionalFotoPdf::embeddedSrc('pemeliharaan-taman/large.jpg', 115, 68);

        $this->assertNotNull($src);
        $this->assertStringStartsWith('data:image/jpeg;base64,', $src);
        $this->assertLessThan(strlen(base64_encode($jpeg)), strlen($src));
    }

    public function test_display_size_uses_string_not_disk_path(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('thumb.jpg', 400, 300);
        Storage::disk('public')->putFileAs('pemeliharaan-taman', $file, 'thumb.jpg');

        $size = OperasionalFotoPdf::displaySize('pemeliharaan-taman/thumb.jpg', 115, 68);

        $this->assertNotNull($size);
        $this->assertSame(91, $size['width']);
        $this->assertSame(68, $size['height']);
    }
}
