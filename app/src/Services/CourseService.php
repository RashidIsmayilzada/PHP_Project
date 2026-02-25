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
        // Note: Assuming Repository has this method if added to interface
        // If not in interface, we should add it or handle it.
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
        if (empty($courseData['course_code']) || empty($courseData['course_name']) || empty($courseData['teacher_id'])) {
            return null;
        }

        $teacher = $this->userRepository->findById((int)$courseData['teacher_id']);
        if (!$teacher || $teacher->getRole() !== 'teacher') {
            return null;
        }

        if (isset($courseData['credits']) && $courseData['credits'] <= 0) {
            return null;
        }

        $course = new Course(
            $courseData['course_code'],
            $courseData['course_name'],
            (int)$courseData['teacher_id'],
            $courseData['description'] ?? null,
            $courseData['credits'] ? (float)$courseData['credits'] : null,
            $courseData['semester'] ?? null
        );

        return $this->courseRepository->create($course);
    }

    public function updateCourse(Course $course, array $updateData): bool
    {
        if (isset($updateData['teacher_id'])) {
            $teacher = $this->userRepository->findById((int)$updateData['teacher_id']);
            if (!$teacher || $teacher->getRole() !== 'teacher') {
                return false;
            }
        }

        if (isset($updateData['credits']) && $updateData['credits'] <= 0) {
            return false;
        }

        return $this->courseRepository->update($course);
    }

    public function deleteCourse(int $courseId): bool
    {
        return $this->courseRepository->delete($courseId);
    }
}
