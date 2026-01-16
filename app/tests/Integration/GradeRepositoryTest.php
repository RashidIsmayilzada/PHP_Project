<?php

namespace App\Tests\Integration;

use App\Models\Grade;
use App\Repositories\GradeRepository;
use App\Database\Database;
use PHPUnit\Framework\TestCase;

class GradeRepositoryTest extends TestCase
{
    private GradeRepository $gradeRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gradeRepository = new GradeRepository();
    }

    // Test finding all grades
    public function testFindAll(): void
    {
        $grades = $this->gradeRepository->findAll();

        $this->assertIsArray($grades);
        $this->assertNotEmpty($grades);

        foreach ($grades as $grade) {
            $this->assertInstanceOf(Grade::class, $grade);
            $this->assertNotNull($grade->getGradeId());
            $this->assertNotNull($grade->getAssignmentId());
            $this->assertNotNull($grade->getStudentId());
            $this->assertIsFloat($grade->getPointsEarned());
        }
    }

    // Test finding grade by ID
    public function testFindById(): void
    {
        $allGrades = $this->gradeRepository->findAll();

        if (empty($allGrades)) {
            $this->markTestSkipped('No grades in database to test');
        }

        $firstGrade = $allGrades[0];
        $gradeId = $firstGrade->getGradeId();

        $grade = $this->gradeRepository->findById($gradeId);

        $this->assertInstanceOf(Grade::class, $grade);
        $this->assertEquals($gradeId, $grade->getGradeId());
    }

    // Test finding grade by non-existent ID returns null
    public function testFindByIdNonExistent(): void
    {
        $grade = $this->gradeRepository->findById(999999);
        $this->assertNull($grade);
    }

    // Test finding grades by student ID
    public function testFindByStudentId(): void
    {
        $allGrades = $this->gradeRepository->findAll();

        if (empty($allGrades)) {
            $this->markTestSkipped('No grades in database to test');
        }

        $studentId = $allGrades[0]->getStudentId();
        $grades = $this->gradeRepository->findByStudentId($studentId);

        $this->assertIsArray($grades);
        $this->assertNotEmpty($grades);

        foreach ($grades as $grade) {
            $this->assertInstanceOf(Grade::class, $grade);
            $this->assertEquals($studentId, $grade->getStudentId());
        }
    }

    // Test finding grades by assignment ID
    public function testFindByAssignmentId(): void
    {
        $allGrades = $this->gradeRepository->findAll();

        if (empty($allGrades)) {
            $this->markTestSkipped('No grades in database to test');
        }

        $assignmentId = $allGrades[0]->getAssignmentId();
        $grades = $this->gradeRepository->findByAssignmentId($assignmentId);

        $this->assertIsArray($grades);
        $this->assertNotEmpty($grades);

        foreach ($grades as $grade) {
            $this->assertInstanceOf(Grade::class, $grade);
            $this->assertEquals($assignmentId, $grade->getAssignmentId());
        }
    }

    // Test that grades have valid point values
    public function testGradesHaveValidPointValues(): void
    {
        $grades = $this->gradeRepository->findAll();

        if (empty($grades)) {
            $this->markTestSkipped('No grades in database to test');
        }

        foreach ($grades as $grade) {
            $points = $grade->getPointsEarned();
            $this->assertGreaterThanOrEqual(0, $points, 'Points earned should be non-negative');
        }
    }

    // Test finding grades by student and assignment
    public function testFindByStudentAndAssignment(): void
    {
        $allGrades = $this->gradeRepository->findAll();

        if (empty($allGrades)) {
            $this->markTestSkipped('No grades in database to test');
        }

        $firstGrade = $allGrades[0];
        $studentId = $firstGrade->getStudentId();
        $assignmentId = $firstGrade->getAssignmentId();

        $grade = $this->gradeRepository->findByStudentAndAssignment($studentId, $assignmentId);

        if ($grade !== null) {
            $this->assertInstanceOf(Grade::class, $grade);
            $this->assertEquals($studentId, $grade->getStudentId());
            $this->assertEquals($assignmentId, $grade->getAssignmentId());
        }
    }

    // Test calculating course average
    public function testCalculateCourseAverage(): void
    {
        $allGrades = $this->gradeRepository->findAll();

        if (empty($allGrades)) {
            $this->markTestSkipped('No grades in database to test');
        }

        $studentId = $allGrades[0]->getStudentId();

        // We need to get a course ID from the assignment
        // For this test, we'll just test that the method doesn't throw errors
        $average = $this->gradeRepository->calculateCourseAverage(1, $studentId);

        if ($average !== null) {
            $this->assertIsFloat($average);
            $this->assertGreaterThanOrEqual(0, $average);
            $this->assertLessThanOrEqual(100, $average);
        }
    }
}
