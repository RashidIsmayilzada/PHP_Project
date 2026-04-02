<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\CourseServiceInterface;

class CourseService implements CourseServiceInterface
{
    private CourseRepositoryInterface $courseRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        CourseRepositoryInterface $courseRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->userRepository = $userRepository;
    }

    public function findAll(): array
    {
        return $this->courseRepository->findAll();
    }

    public function findById(int $id): ?Course
    {
        return $this->courseRepository->findById($id);
    }

    public function findByTeacherId(int $teacherId): array
    {
        return $this->courseRepository->findByTeacherId($teacherId);
    }

    public function findBySemester(string $semester): array
    {
        return $this->courseRepository->findBySemester($semester);
    }

    public function getCoursesForStudent(int $studentId): array
    {
        return $this->courseRepository->findByStudentId($studentId);
    }

    public function getCoursesForTeacher(int $teacherId): array
    {
        return $this->courseRepository->findByTeacherId($teacherId);
    }

    public function createCourse(array $courseData): ?Course
    {
        if (!$this->hasValidCourseData($courseData)) {
            return null;
        }

        if (!$this->isTeacher((int)$courseData['teacher_id'])) {
            return null;
        }

        return $this->courseRepository->create($this->buildCourse($courseData));
    }

    public function updateCourse(Course $course, array $updateData): bool
    {
        if (!$this->canUpdateCourse($updateData)) {
            return false;
        }

        $this->applyCourseUpdates($course, $updateData);

        return $this->courseRepository->update($course);
    }

    public function deleteCourse(int $courseId): bool
    {
        return $this->courseRepository->delete($courseId);
    }

    private function hasValidCourseData(array $courseData): bool
    {
        if (empty($courseData['course_code']) || empty($courseData['course_name']) || empty($courseData['teacher_id'])) {
            return false;
        }

        return !isset($courseData['credits']) || (float)$courseData['credits'] > 0;
    }

    private function isTeacher(int $teacherId): bool
    {
        $teacher = $this->userRepository->findById($teacherId);

        return $teacher !== null && $teacher->getRole() === 'teacher';
    }

    private function buildCourse(array $courseData): Course
    {
        $credits = isset($courseData['credits']) && $courseData['credits'] !== ''
            ? (float)$courseData['credits']
            : null;

        return new Course(
            $courseData['course_code'],
            $courseData['course_name'],
            (int)$courseData['teacher_id'],
            $courseData['description'] ?? null,
            $credits,
            $courseData['semester'] ?? null
        );
    }

    private function canUpdateCourse(array $updateData): bool
    {
        if (isset($updateData['teacher_id']) && !$this->isTeacher((int)$updateData['teacher_id'])) {
            return false;
        }

        return !isset($updateData['credits']) || (float)$updateData['credits'] > 0;
    }

    private function applyCourseUpdates(Course $course, array $updateData): void
    {
        if (isset($updateData['teacher_id'])) {
            $course->setTeacherId((int)$updateData['teacher_id']);
        }

        if (isset($updateData['course_code'])) {
            $course->setCourseCode($updateData['course_code']);
        }

        if (isset($updateData['course_name'])) {
            $course->setCourseName($updateData['course_name']);
        }

        if (array_key_exists('description', $updateData)) {
            $course->setDescription($updateData['description']);
        }

        if (array_key_exists('credits', $updateData)) {
            $course->setCredits($updateData['credits'] === '' ? null : (float)$updateData['credits']);
        }

        if (array_key_exists('semester', $updateData)) {
            $course->setSemester($updateData['semester']);
        }
    }
}
