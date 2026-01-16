<?php

namespace App\Tests\Integration;

use App\Models\Course;
use App\Repositories\CourseRepository;
use PHPUnit\Framework\TestCase;

class CourseRepositoryTest extends TestCase
{
    private CourseRepository $courseRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->courseRepository = new CourseRepository();
    }

    /**
     * Test finding all courses
     */
    public function testFindAll(): void
    {
        $courses = $this->courseRepository->findAll();

        $this->assertIsArray($courses);
        $this->assertNotEmpty($courses);

        foreach ($courses as $course) {
            $this->assertInstanceOf(Course::class, $course);
            $this->assertNotNull($course->getCourseId());
            $this->assertNotEmpty($course->getCourseCode());
            $this->assertNotEmpty($course->getCourseName());
            $this->assertNotNull($course->getTeacherId());
        }
    }

    /**
     * Test finding course by ID
     */
    public function testFindById(): void
    {
        $allCourses = $this->courseRepository->findAll();

        if (empty($allCourses)) {
            $this->markTestSkipped('No courses in database to test');
        }

        $firstCourse = $allCourses[0];
        $courseId = $firstCourse->getCourseId();

        $course = $this->courseRepository->findById($courseId);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals($courseId, $course->getCourseId());
        $this->assertEquals($firstCourse->getCourseCode(), $course->getCourseCode());
        $this->assertEquals($firstCourse->getCourseName(), $course->getCourseName());
    }

    /**
     * Test finding course by non-existent ID returns null
     */
    public function testFindByIdNonExistent(): void
    {
        $course = $this->courseRepository->findById(999999);
        $this->assertNull($course);
    }

    /**
     * Test finding courses by teacher ID
     */
    public function testFindByTeacherId(): void
    {
        $allCourses = $this->courseRepository->findAll();

        if (empty($allCourses)) {
            $this->markTestSkipped('No courses in database to test');
        }

        $teacherId = $allCourses[0]->getTeacherId();
        $courses = $this->courseRepository->findByTeacherId($teacherId);

        $this->assertIsArray($courses);
        $this->assertNotEmpty($courses);

        foreach ($courses as $course) {
            $this->assertInstanceOf(Course::class, $course);
            $this->assertEquals($teacherId, $course->getTeacherId());
        }
    }

    /**
     * Test finding courses by student ID
     */
    public function testFindByStudentId(): void
    {
        // Assuming student ID 1 exists and is enrolled in courses
        $courses = $this->courseRepository->findByStudentId(1);

        $this->assertIsArray($courses);

        foreach ($courses as $course) {
            $this->assertInstanceOf(Course::class, $course);
            $this->assertNotNull($course->getCourseId());
        }
    }

    /**
     * Test that courses have valid course codes
     */
    public function testCoursesHaveValidCourseCodes(): void
    {
        $courses = $this->courseRepository->findAll();

        if (empty($courses)) {
            $this->markTestSkipped('No courses in database to test');
        }

        foreach ($courses as $course) {
            $courseCode = $course->getCourseCode();
            $this->assertNotEmpty($courseCode);
            $this->assertIsString($courseCode);
        }
    }

    /**
     * Test that courses have valid credits
     */
    public function testCoursesHaveValidCredits(): void
    {
        $courses = $this->courseRepository->findAll();

        if (empty($courses)) {
            $this->markTestSkipped('No courses in database to test');
        }

        foreach ($courses as $course) {
            $credits = $course->getCredits();
            if ($credits !== null) {
                $this->assertGreaterThan(0, $credits, 'Course credits should be positive');
            }
        }
    }

    /**
     * Test that course names are not empty
     */
    public function testCourseNamesAreNotEmpty(): void
    {
        $courses = $this->courseRepository->findAll();

        if (empty($courses)) {
            $this->markTestSkipped('No courses in database to test');
        }

        foreach ($courses as $course) {
            $this->assertNotEmpty($course->getCourseName());
        }
    }
}
