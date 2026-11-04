<?php

namespace Tests\Unit;

use App\Support\Evaluasi\MasukanMasyarakatReportBuilder;
use PHPUnit\Framework\TestCase;

class MasukanMasyarakatSkorTest extends TestCase
{
    public function test_skor_is_average_of_aduan_response_and_survey_percent(): void
    {
        $this->assertSame(80, MasukanMasyarakatReportBuilder::persenAduanStatusDariBaru(10, 8));
        $this->assertSame(80, MasukanMasyarakatReportBuilder::persenSkorSurvey(4.0));
        $this->assertSame(80, MasukanMasyarakatReportBuilder::skorEvaluasi(80, 80, 10, 5));
    }

    public function test_skor_uses_zero_for_missing_survey_or_aduan_component(): void
    {
        $this->assertSame(40, MasukanMasyarakatReportBuilder::skorEvaluasi(80, 0, 5, 0));
        $this->assertSame(40, MasukanMasyarakatReportBuilder::skorEvaluasi(0, 80, 0, 3));
    }

    public function test_skor_null_when_no_aduan_and_no_survey(): void
    {
        $this->assertNull(MasukanMasyarakatReportBuilder::skorEvaluasi(0, 0, 0, 0));
    }
}
