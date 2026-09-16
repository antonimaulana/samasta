<?php

namespace Tests\Unit;

use App\Support\OperasionalPelaksanaanTime;
use PHPUnit\Framework\TestCase;

class OperasionalPelaksanaanTimeTest extends TestCase
{
    public function test_parse_handles_double_time_specification(): void
    {
        $parsed = OperasionalPelaksanaanTime::parse('2026-08-28 00:00:00 08:00:00');

        $this->assertSame('2026-08-28 00:00:00', $parsed->format('Y-m-d H:i:s'));
    }

    public function test_display_handles_double_time_specification(): void
    {
        $display = OperasionalPelaksanaanTime::display('2026-08-28 00:00:00 08:00:00');

        $this->assertSame('28/08/2026 00:00', $display);
    }
}
