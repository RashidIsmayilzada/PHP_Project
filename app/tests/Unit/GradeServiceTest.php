<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Grade;
use App\Services\GradeService;
use App\Services\Config\GradeConfigPolicy;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Constants\GradeConfig;

class GradeServiceTest extends TestCase
{
    private $gradeService;
    private $gradeRepo;
    private $courseRepo;

    protected function setUp(): void
    {
        $this->gradeRepo = $this->createMock(GradeRepositoryInterface::class);
        $this->courseRepo = $this->createMock(CourseRepositoryInterface::class);

        $this->gradeService = new GradeService(
            $this->gradeRepo,
            $this->courseRepo,
            new GradeConfigPolicy()
        );
    }

    public function testPercentageToLetterGrade(): void
    {
        $this->assertEquals('A', $this->gradeService->percentageToLetterGrade(95.0));
        $this->assertEquals('A', $this->gradeService->percentageToLetterGrade(90.0));
        $this->assertEquals('B', $this->gradeService->percentageToLetterGrade(85.0));
        $this->assertEquals('B', $this->gradeService->percentageToLetterGrade(80.0));
        $this->assertEquals('C', $this->gradeService->percentageToLetterGrade(75.0));
        $this->assertEquals('C', $this->gradeService->percentageToLetterGrade(70.0));
        $this->assertEquals('D', $this->gradeService->percentageToLetterGrade(65.0));
        $this->assertEquals('D', $this->gradeService->percentageToLetterGrade(60.0));
        $this->assertEquals('F', $this->gradeService->percentageToLetterGrade(55.0));
        $this->assertEquals('F', $this->gradeService->percentageToLetterGrade(0.0));
    }

    public function testPercentageToGPA(): void
    {
        $this->assertEquals(GradeConfig::GPA_A, $this->gradeService->percentageToGPA(90.0));
        $this->assertEquals(GradeConfig::GPA_B, $this->gradeService->percentageToGPA(80.0));
        $this->assertEquals(GradeConfig::GPA_C, $this->gradeService->percentageToGPA(70.0));
        $this->assertEquals(GradeConfig::GPA_D, $this->gradeService->percentageToGPA(60.0));
        $this->assertEquals(GradeConfig::GPA_F, $this->gradeService->percentageToGPA(59.9));
    }

    public function testUpdateGradeUpdatesPointsAndFeedback(): void
    {
        $grade = new Grade(10, 20, 75.0, 'Initial feedback', 5);

        $this->gradeRepo
            ->expects($this->once())
            ->method('update')
            ->with($this->callback(function (Grade $updatedGrade): bool {
                $this->assertSame(88.5, $updatedGrade->getPointsEarned());
                $this->assertSame('Revised feedback', $updatedGrade->getFeedback());

                return true;
            }))
            ->willReturn(true);

        $result = $this->gradeService->updateGrade($grade, [
            'points_earned' => 88.5,
            'feedback' => 'Revised feedback',
        ]);

        $this->assertTrue($result);
    }
}
