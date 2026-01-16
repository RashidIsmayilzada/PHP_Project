<?php

namespace App\Tests\Unit;

use App\Models\Grade;
use PHPUnit\Framework\TestCase;

class GradeModelTest extends TestCase
{
    /**
     * Test Grade model construction with minimum required fields
     */
    public function testGradeConstructionWithMinimumFields(): void
    {
        $grade = new Grade(
            assignmentId: 1,
            studentId: 2,
            pointsEarned: 85.5
        );

        $this->assertNull($grade->getGradeId());
        $this->assertEquals(1, $grade->getAssignmentId());
        $this->assertEquals(2, $grade->getStudentId());
        $this->assertEquals(85.5, $grade->getPointsEarned());
        $this->assertNull($grade->getFeedback());
        $this->assertNull($grade->getGradedAt());
        $this->assertNull($grade->getUpdatedAt());
    }

    /**
     * Test Grade model construction with all fields
     */
    public function testGradeConstructionWithAllFields(): void
    {
        $grade = new Grade(
            assignmentId: 1,
            studentId: 2,
            pointsEarned: 95.0,
            feedback: 'Excellent work!',
            gradeId: 10,
            gradedAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-02 15:30:00'
        );

        $this->assertEquals(10, $grade->getGradeId());
        $this->assertEquals(1, $grade->getAssignmentId());
        $this->assertEquals(2, $grade->getStudentId());
        $this->assertEquals(95.0, $grade->getPointsEarned());
        $this->assertEquals('Excellent work!', $grade->getFeedback());
        $this->assertEquals('2025-01-01 10:00:00', $grade->getGradedAt());
        $this->assertEquals('2025-01-02 15:30:00', $grade->getUpdatedAt());
    }

    /**
     * Test setting grade ID
     */
    public function testSetGradeId(): void
    {
        $grade = new Grade(1, 2, 80.0);
        $this->assertNull($grade->getGradeId());

        $grade->setGradeId(5);
        $this->assertEquals(5, $grade->getGradeId());
    }

    /**
     * Test setting points earned
     */
    public function testSetPointsEarned(): void
    {
        $grade = new Grade(1, 2, 80.0);
        $this->assertEquals(80.0, $grade->getPointsEarned());

        $grade->setPointsEarned(95.5);
        $this->assertEquals(95.5, $grade->getPointsEarned());
    }

    /**
     * Test grade with zero points
     */
    public function testGradeWithZeroPoints(): void
    {
        $grade = new Grade(1, 2, 0.0);
        $this->assertEquals(0.0, $grade->getPointsEarned());
    }

    /**
     * Test grade with decimal points
     */
    public function testGradeWithDecimalPoints(): void
    {
        $grade = new Grade(1, 2, 87.75);
        $this->assertEquals(87.75, $grade->getPointsEarned());
    }

    /**
     * Test grade with feedback
     */
    public function testGradeWithFeedback(): void
    {
        $grade = new Grade(1, 2, 90.0, 'Great job! Keep up the good work.');
        $this->assertEquals('Great job! Keep up the good work.', $grade->getFeedback());
    }

    /**
     * Test grade without feedback
     */
    public function testGradeWithoutFeedback(): void
    {
        $grade = new Grade(1, 2, 85.0);
        $this->assertNull($grade->getFeedback());
    }
}
