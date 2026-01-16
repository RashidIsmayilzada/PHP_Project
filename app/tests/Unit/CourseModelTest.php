<?php

namespace App\Tests\Unit;

use App\Models\Course;
use PHPUnit\Framework\TestCase;

class CourseModelTest extends TestCase
{
    /**
     * Test Course model construction with minimum required fields
     */
    public function testCourseConstructionWithMinimumFields(): void
    {
        $course = new Course(
            courseCode: 'CS101',
            courseName: 'Introduction to Computer Science',
            teacherId: 1
        );

        $this->assertNull($course->getCourseId());
        $this->assertEquals('CS101', $course->getCourseCode());
        $this->assertEquals('Introduction to Computer Science', $course->getCourseName());
        $this->assertEquals(1, $course->getTeacherId());
        $this->assertNull($course->getDescription());
        $this->assertNull($course->getCredits());
        $this->assertNull($course->getSemester());
        $this->assertNull($course->getCreatedAt());
        $this->assertNull($course->getUpdatedAt());
    }

    /**
     * Test Course model construction with all fields
     */
    public function testCourseConstructionWithAllFields(): void
    {
        $course = new Course(
            courseCode: 'CS101',
            courseName: 'Introduction to Computer Science',
            teacherId: 1,
            description: 'A comprehensive introduction to programming and computer science concepts.',
            credits: 3.0,
            semester: 'Fall 2025',
            courseId: 5,
            createdAt: '2025-01-01 10:00:00',
            updatedAt: '2025-01-15 14:30:00'
        );

        $this->assertEquals(5, $course->getCourseId());
        $this->assertEquals('CS101', $course->getCourseCode());
        $this->assertEquals('Introduction to Computer Science', $course->getCourseName());
        $this->assertEquals(1, $course->getTeacherId());
        $this->assertEquals('A comprehensive introduction to programming and computer science concepts.', $course->getDescription());
        $this->assertEquals(3.0, $course->getCredits());
        $this->assertEquals('Fall 2025', $course->getSemester());
        $this->assertEquals('2025-01-01 10:00:00', $course->getCreatedAt());
        $this->assertEquals('2025-01-15 14:30:00', $course->getUpdatedAt());
    }

    /**
     * Test setting course ID
     */
    public function testSetCourseId(): void
    {
        $course = new Course('CS101', 'Intro to CS', 1);
        $this->assertNull($course->getCourseId());

        $course->setCourseId(10);
        $this->assertEquals(10, $course->getCourseId());
    }

    /**
     * Test course with various credit values
     */
    public function testCourseWithDifferentCredits(): void
    {
        $course1 = new Course('CS101', 'Intro to CS', 1, null, 3.0);
        $this->assertEquals(3.0, $course1->getCredits());

        $course2 = new Course('CS201', 'Advanced CS', 1, null, 4.0);
        $this->assertEquals(4.0, $course2->getCredits());

        $course3 = new Course('CS301', 'Lab', 1, null, 1.5);
        $this->assertEquals(1.5, $course3->getCredits());
    }

    /**
     * Test course with description
     */
    public function testCourseWithDescription(): void
    {
        $description = 'This course covers fundamental programming concepts and algorithms.';
        $course = new Course('CS101', 'Intro to CS', 1, $description);
        $this->assertEquals($description, $course->getDescription());
    }

    /**
     * Test course without optional fields
     */
    public function testCourseWithoutOptionalFields(): void
    {
        $course = new Course('MATH101', 'Calculus I', 2);
        $this->assertNull($course->getDescription());
        $this->assertNull($course->getCredits());
        $this->assertNull($course->getSemester());
    }

    /**
     * Test course with semester
     */
    public function testCourseWithSemester(): void
    {
        $course = new Course('CS101', 'Intro to CS', 1, null, 3.0, 'Spring 2025');
        $this->assertEquals('Spring 2025', $course->getSemester());
    }
}
