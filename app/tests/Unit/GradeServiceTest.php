<?php

namespace App\Tests\Unit;

use App\Services\GradeService;
use PHPUnit\Framework\TestCase;

class GradeServiceTest extends TestCase
{
    private GradeService $gradeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gradeService = new GradeService();
    }

    // Test percentage to letter grade conversion
    public function testPercentageToLetterGrade(): void
    {
        // Test A grade (90-100)
        $this->assertEquals('A', $this->gradeService->percentageToLetterGrade(100));
        $this->assertEquals('A', $this->gradeService->percentageToLetterGrade(95));
        $this->assertEquals('A', $this->gradeService->percentageToLetterGrade(90));

        // Test B grade (80-89)
        $this->assertEquals('B', $this->gradeService->percentageToLetterGrade(89));
        $this->assertEquals('B', $this->gradeService->percentageToLetterGrade(85));
        $this->assertEquals('B', $this->gradeService->percentageToLetterGrade(80));

        // Test C grade (70-79)
        $this->assertEquals('C', $this->gradeService->percentageToLetterGrade(79));
        $this->assertEquals('C', $this->gradeService->percentageToLetterGrade(75));
        $this->assertEquals('C', $this->gradeService->percentageToLetterGrade(70));

        // Test D grade (60-69)
        $this->assertEquals('D', $this->gradeService->percentageToLetterGrade(69));
        $this->assertEquals('D', $this->gradeService->percentageToLetterGrade(65));
        $this->assertEquals('D', $this->gradeService->percentageToLetterGrade(60));

        // Test F grade (<60)
        $this->assertEquals('F', $this->gradeService->percentageToLetterGrade(59));
        $this->assertEquals('F', $this->gradeService->percentageToLetterGrade(50));
        $this->assertEquals('F', $this->gradeService->percentageToLetterGrade(0));
    }

    // Test edge cases for letter grade conversion
    public function testPercentageToLetterGradeEdgeCases(): void
    {
        // Test boundary values
        $this->assertEquals('A', $this->gradeService->percentageToLetterGrade(90.0));
        $this->assertEquals('B', $this->gradeService->percentageToLetterGrade(89.99));
        $this->assertEquals('B', $this->gradeService->percentageToLetterGrade(80.0));
        $this->assertEquals('C', $this->gradeService->percentageToLetterGrade(79.99));
        $this->assertEquals('C', $this->gradeService->percentageToLetterGrade(70.0));
        $this->assertEquals('D', $this->gradeService->percentageToLetterGrade(69.99));
        $this->assertEquals('D', $this->gradeService->percentageToLetterGrade(60.0));
        $this->assertEquals('F', $this->gradeService->percentageToLetterGrade(59.99));
    }

    // Test percentage to GPA conversion
    public function testPercentageToGPA(): void
    {
        // Test A grade (90-100) = 4.0
        $this->assertEquals(4.0, $this->gradeService->percentageToGPA(100));
        $this->assertEquals(4.0, $this->gradeService->percentageToGPA(95));
        $this->assertEquals(4.0, $this->gradeService->percentageToGPA(90));

        // Test B grade (80-89) = 3.0
        $this->assertEquals(3.0, $this->gradeService->percentageToGPA(89));
        $this->assertEquals(3.0, $this->gradeService->percentageToGPA(85));
        $this->assertEquals(3.0, $this->gradeService->percentageToGPA(80));

        // Test C grade (70-79) = 2.0
        $this->assertEquals(2.0, $this->gradeService->percentageToGPA(79));
        $this->assertEquals(2.0, $this->gradeService->percentageToGPA(75));
        $this->assertEquals(2.0, $this->gradeService->percentageToGPA(70));

        // Test D grade (60-69) = 1.0
        $this->assertEquals(1.0, $this->gradeService->percentageToGPA(69));
        $this->assertEquals(1.0, $this->gradeService->percentageToGPA(65));
        $this->assertEquals(1.0, $this->gradeService->percentageToGPA(60));

        // Test F grade (<60) = 0.0
        $this->assertEquals(0.0, $this->gradeService->percentageToGPA(59));
        $this->assertEquals(0.0, $this->gradeService->percentageToGPA(50));
        $this->assertEquals(0.0, $this->gradeService->percentageToGPA(0));
    }

    // Test edge cases for GPA conversion
    public function testPercentageToGPAEdgeCases(): void
    {
        // Test boundary values
        $this->assertEquals(4.0, $this->gradeService->percentageToGPA(90.0));
        $this->assertEquals(3.0, $this->gradeService->percentageToGPA(89.99));
        $this->assertEquals(3.0, $this->gradeService->percentageToGPA(80.0));
        $this->assertEquals(2.0, $this->gradeService->percentageToGPA(79.99));
        $this->assertEquals(2.0, $this->gradeService->percentageToGPA(70.0));
        $this->assertEquals(1.0, $this->gradeService->percentageToGPA(69.99));
        $this->assertEquals(1.0, $this->gradeService->percentageToGPA(60.0));
        $this->assertEquals(0.0, $this->gradeService->percentageToGPA(59.99));
    }

    // Test that GPA and letter grade conversion align
    public function testGPAAndLetterGradeAlignment(): void
    {
        $testCases = [
            100 => ['letter' => 'A', 'gpa' => 4.0],
            90 => ['letter' => 'A', 'gpa' => 4.0],
            89 => ['letter' => 'B', 'gpa' => 3.0],
            80 => ['letter' => 'B', 'gpa' => 3.0],
            79 => ['letter' => 'C', 'gpa' => 2.0],
            70 => ['letter' => 'C', 'gpa' => 2.0],
            69 => ['letter' => 'D', 'gpa' => 1.0],
            60 => ['letter' => 'D', 'gpa' => 1.0],
            59 => ['letter' => 'F', 'gpa' => 0.0],
            0 => ['letter' => 'F', 'gpa' => 0.0],
        ];

        foreach ($testCases as $percentage => $expected) {
            $this->assertEquals(
                $expected['letter'],
                $this->gradeService->percentageToLetterGrade($percentage),
                "Letter grade mismatch for $percentage%"
            );
            $this->assertEquals(
                $expected['gpa'],
                $this->gradeService->percentageToGPA($percentage),
                "GPA mismatch for $percentage%"
            );
        }
    }
}
