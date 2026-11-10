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

    public function test_round_total_sums_decimals_then_rounds_half_up(): void
    {
        $this->assertSame(52_468, TamanLuasan::roundTotal(26233.81 + 26233.81));
        $this->assertSame(1_690_920, TamanLuasan::roundTotal(1_690_919.5));
        $this->assertSame(100, TamanLuasan::roundTotal(99.49));
        $this->assertSame(101, TamanLuasan::roundTotal(100.5));
    }

    public function test_format_total_never_shows_decimals(): void
    {
        $this->assertSame('52.468', TamanLuasan::formatTotal(26233.81 + 26233.81));
        $this->assertSame('1.690.920', TamanLuasan::formatTotal(1_690_919.5));
    }

    public function test_quantize_jalur_hijau_rounds_to_two_decimals_for_storage(): void
    {
        $this->assertSame(26233.81, TamanLuasan::quantizeStorage(26233.814, 'Jalur Hijau Jalan'));
        $this->assertSame(100.13, TamanLuasan::quantizeStorage(100.126, 'Jalur Hijau Jalan'));
    }

    public function test_sum_of_formatted_jalur_hijau_matches_quantized_sum(): void
    {
        $raw = [100.126, 50.004, 25.995];
        $sum = 0.0;

        foreach ($raw as $value) {
            $q = TamanLuasan::quantizeStorage($value, 'Jalur Hijau Jalan');
            $sum += $q;
            $this->assertSame(
                TamanLuasan::format($q, 'Jalur Hijau Jalan'),
                TamanLuasan::format($value, 'Jalur Hijau Jalan')
            );
        }

        $this->assertEqualsWithDelta(176.13, $sum, 0.0001);
    }

    public function test_format_aggregate_decimal_for_baseline_rekap(): void
    {
        $this->assertSame('26.272,49', TamanLuasan::formatAggregateDecimal(26272.49));
        $this->assertSame('3.142.734,98', TamanLuasan::formatAggregateDecimal(3_142_734.98));
    }
}
