<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Constants\GradeConfig;

class GradeConfigTest extends TestCase
{
    public function testGradeThresholds(): void
    {
        $this->assertEquals(90.0, GradeConfig::GRADE_A_THRESHOLD);
        $this->assertEquals(80.0, GradeConfig::GRADE_B_THRESHOLD);
        $this->assertEquals(70.0, GradeConfig::GRADE_C_THRESHOLD);
        $this->assertEquals(60.0, GradeConfig::GRADE_D_THRESHOLD);
    }

    public function testGpaValues(): void
    {
        $this->assertEquals(4.0, GradeConfig::GPA_A);
        $this->assertEquals(3.0, GradeConfig::GPA_B);
        $this->assertEquals(2.0, GradeConfig::GPA_C);
        $this->assertEquals(1.0, GradeConfig::GPA_D);
        $this->assertEquals(0.0, GradeConfig::GPA_F);
    }
}
