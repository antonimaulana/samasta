<?php

namespace Tests\Unit;

use App\Support\TamanLuasan;
use PHPUnit\Framework\TestCase;

class TamanLuasanTest extends TestCase
{
    public function test_format_shows_decimals_when_needed(): void
    {
        $this->assertSame('5.000', TamanLuasan::format(5000));
        $this->assertSame('1.234,75', TamanLuasan::format(1234.75));
    }

    public function test_normalize_request_accepts_indonesian_formats(): void
    {
        $this->assertSame('5000', TamanLuasan::normalizeRequest('5.000'));
        $this->assertSame('1234.75', TamanLuasan::normalizeRequest('1234,75'));
        $this->assertSame('99.5', TamanLuasan::normalizeRequest('99,5'));
    }

    public function test_format_jalur_hijau_uses_two_decimal_places(): void
    {
        $this->assertSame('26.233,81', TamanLuasan::format(26233.81, 'Jalur Hijau Jalan'));
        $this->assertSame('1.890.919,50', TamanLuasan::format(1890919.5, 'Jalur Hijau Jalan'));
    }
}
