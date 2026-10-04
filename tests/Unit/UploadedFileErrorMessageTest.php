<?php

namespace Tests\Unit;

use App\Support\UploadedFileErrorMessage;
use PHPUnit\Framework\TestCase;

class UploadedFileErrorMessageTest extends TestCase
{
    public function test_ini_size_to_bytes(): void
    {
        $this->assertSame(20 * 1024 * 1024, UploadedFileErrorMessage::iniSizeToBytes('20M'));
        $this->assertSame(2 * 1024 * 1024, UploadedFileErrorMessage::iniSizeToBytes('2M'));
    }
}
