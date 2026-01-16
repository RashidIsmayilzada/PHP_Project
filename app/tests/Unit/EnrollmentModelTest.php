<?php

namespace App\Tests\Unit;

use App\Models\Enrollment;
use PHPUnit\Framework\TestCase;

class EnrollmentModelTest extends TestCase
{
    /**
     * Test Enrollment model construction with minimum required fields
     */
    public function testEnrollmentConstructionWithMinimumFields(): void
    {
        $enrollment = new Enrollment(
            studentId: 1,
            courseId: 2
        );

        $this->assertNull($enrollment->getEnrollmentId());
        $this->assertEquals(1, $enrollment->getStudentId());
        $this->assertEquals(2, $enrollment->getCourseId());
        $this->assertEquals('active', $enrollment->getStatus());
        $this->assertTrue($enrollment->isActive());
        $this->assertNull($enrollment->getEnrollmentDate());
    }

    /**
     * Test Enrollment model construction with all fields
     */
    public function testEnrollmentConstructionWithAllFields(): void
    {
        $enrollment = new Enrollment(
            studentId: 1,
            courseId: 2,
            status: 'active',
            enrollmentId: 10,
            enrollmentDate: '2025-01-10 09:00:00'
        );

        $this->assertEquals(10, $enrollment->getEnrollmentId());
        $this->assertEquals(1, $enrollment->getStudentId());
        $this->assertEquals(2, $enrollment->getCourseId());
        $this->assertEquals('active', $enrollment->getStatus());
        $this->assertTrue($enrollment->isActive());
        $this->assertEquals('2025-01-10 09:00:00', $enrollment->getEnrollmentDate());
    }

    /**
     * Test enrollment with active status
     */
    public function testEnrollmentWithActiveStatus(): void
    {
        $enrollment = new Enrollment(1, 2, 'active');
        $this->assertEquals('active', $enrollment->getStatus());
        $this->assertTrue($enrollment->isActive());
    }

    /**
     * Test enrollment with inactive status
     */
    public function testEnrollmentWithInactiveStatus(): void
    {
        $enrollment = new Enrollment(1, 2, 'inactive');
        $this->assertEquals('inactive', $enrollment->getStatus());
        $this->assertFalse($enrollment->isActive());
    }

    /**
     * Test enrollment with dropped status
     */
    public function testEnrollmentWithDroppedStatus(): void
    {
        $enrollment = new Enrollment(1, 2, 'dropped');
        $this->assertEquals('dropped', $enrollment->getStatus());
        $this->assertFalse($enrollment->isActive());
    }

    /**
     * Test enrollment with completed status
     */
    public function testEnrollmentWithCompletedStatus(): void
    {
        $enrollment = new Enrollment(1, 2, 'completed');
        $this->assertEquals('completed', $enrollment->getStatus());
        $this->assertFalse($enrollment->isActive());
    }

    /**
     * Test setting enrollment ID
     */
    public function testSetEnrollmentId(): void
    {
        $enrollment = new Enrollment(1, 2);
        $this->assertNull($enrollment->getEnrollmentId());

        $enrollment->setEnrollmentId(5);
        $this->assertEquals(5, $enrollment->getEnrollmentId());
    }

    /**
     * Test enrollment with enrollment date
     */
    public function testEnrollmentWithEnrollmentDate(): void
    {
        $enrollment = new Enrollment(
            1,
            2,
            'active',
            null,
            '2025-01-15 10:30:00'
        );
        $this->assertEquals('2025-01-15 10:30:00', $enrollment->getEnrollmentDate());
    }

    /**
     * Test enrollment without enrollment date
     */
    public function testEnrollmentWithoutEnrollmentDate(): void
    {
        $enrollment = new Enrollment(1, 2);
        $this->assertNull($enrollment->getEnrollmentDate());
    }

    /**
     * Test default status is active
     */
    public function testDefaultStatusIsActive(): void
    {
        $enrollment = new Enrollment(1, 2);
        $this->assertEquals('active', $enrollment->getStatus());
        $this->assertTrue($enrollment->isActive());
    }

    /**
     * Test multiple enrollments for same student in different courses
     */
    public function testMultipleEnrollmentsForSameStudent(): void
    {
        $enrollment1 = new Enrollment(1, 10);
        $enrollment2 = new Enrollment(1, 20);
        $enrollment3 = new Enrollment(1, 30);

        $this->assertEquals(1, $enrollment1->getStudentId());
        $this->assertEquals(1, $enrollment2->getStudentId());
        $this->assertEquals(1, $enrollment3->getStudentId());

        $this->assertEquals(10, $enrollment1->getCourseId());
        $this->assertEquals(20, $enrollment2->getCourseId());
        $this->assertEquals(30, $enrollment3->getCourseId());
    }

    /**
     * Test multiple enrollments for different students in same course
     */
    public function testMultipleEnrollmentsForSameCourse(): void
    {
        $enrollment1 = new Enrollment(1, 100);
        $enrollment2 = new Enrollment(2, 100);
        $enrollment3 = new Enrollment(3, 100);

        $this->assertEquals(100, $enrollment1->getCourseId());
        $this->assertEquals(100, $enrollment2->getCourseId());
        $this->assertEquals(100, $enrollment3->getCourseId());

        $this->assertEquals(1, $enrollment1->getStudentId());
        $this->assertEquals(2, $enrollment2->getStudentId());
        $this->assertEquals(3, $enrollment3->getStudentId());
    }
}
