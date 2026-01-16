<?php

namespace App\Services;

use App\Models\Course;
use App\Repositories\CourseRepository;
use App\Repositories\UserRepository;
use App\Services\Interfaces\CourseServiceInterface;

class CourseService implements CourseServiceInterface
{
    private CourseRepository $courseRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->courseRepository = new CourseRepository();
        $this->userRepository = new UserRepository();
    }

    // Pull every course from the database
    public function findAll(): array
    {
        return $this->courseRepository->findAll();
    }

    // Look up a specific course by its ID
    public function findById(int $id): ?Course
    {
        return $this->courseRepository->findById($id);
    }

    // Get all courses taught by a specific teacher
    public function findByTeacherId(int $teacherId): array
    {
        return $this->courseRepository->findByTeacherId($teacherId);
    }

    // Find courses that match a particular semester
    public function findBySemester(string $semester): array
    {
        return $this->courseRepository->findBySemester($semester);
    }

    // Retrieve the list of courses a student is enrolled in
    public function getCoursesForStudent(int $studentId): array
    {
        return $this->courseRepository->findByStudentId($studentId);
    }

    // Get courses assigned to a teacher
    public function getCoursesForTeacher(int $teacherId): array
    {
        return $this->courseRepository->findByTeacherId($teacherId);
    }

    // Create a new course after validating all the required data
    public function createCourse(array $courseData): ?Course
    {
        // Validate required fields
        if (empty($courseData['course_code']) || empty($courseData['course_name']) || empty($courseData['teacher_id'])) {
            return null;
        }

        // Validate teacher exists and is a teacher
        $teacher = $this->userRepository->findById($courseData['teacher_id']);
        if (!$teacher || $teacher->getRole() !== 'teacher') {
            return null;
        }

        // Validate credits if provided
        if (isset($courseData['credits']) && $courseData['credits'] <= 0) {
            return null;
        }

        // Create course object
        $course = new Course(
            $courseData['course_code'],
            $courseData['course_name'],
            $courseData['teacher_id'],
            $courseData['description'] ?? null,
            $courseData['credits'] ?? null,
            $courseData['semester'] ?? null
        );

        return $this->courseRepository->create($course);
    }

    // Update an existing course with new information
    public function updateCourse(Course $course, array $updateData): bool
    {
        // Validate teacher if changed
        if (isset($updateData['teacher_id'])) {
            $teacher = $this->userRepository->findById($updateData['teacher_id']);
            if (!$teacher || $teacher->getRole() !== 'teacher') {
                return false;
            }
        }

        // Validate credits if updated
        if (isset($updateData['credits']) && $updateData['credits'] <= 0) {
            return false;
        }

        // Create updated course object
        $updatedCourse = new Course(
            $updateData['course_code'] ?? $course->getCourseCode(),
            $updateData['course_name'] ?? $course->getCourseName(),
            $updateData['teacher_id'] ?? $course->getTeacherId(),
            $updateData['description'] ?? $course->getDescription(),
            $updateData['credits'] ?? $course->getCredits(),
            $updateData['semester'] ?? $course->getSemester(),
            $course->getCourseId(),
            $course->getCreatedAt(),
            $course->getUpdatedAt()
        );

        return $this->courseRepository->update($updatedCourse);
    }

    // Remove a course from the system along with its assignments and enrollments
    public function deleteCourse(int $courseId): bool
    {
        return $this->courseRepository->delete($courseId);
    }
}
