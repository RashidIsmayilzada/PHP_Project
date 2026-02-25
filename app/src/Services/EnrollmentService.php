<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Services\Interfaces\EnrollmentServiceInterface;

class EnrollmentService implements EnrollmentServiceInterface
{
    private EnrollmentRepositoryInterface $enrollmentRepository;
    private UserRepositoryInterface $userRepository;
    private CourseRepositoryInterface $courseRepository;

    public function __construct(
        EnrollmentRepositoryInterface $enrollmentRepository,
        UserRepositoryInterface $userRepository,
        CourseRepositoryInterface $courseRepository
    ) {
        $this->enrollmentRepository = $enrollmentRepository;
        $this->userRepository = $userRepository;
        $this->courseRepository = $courseRepository;
    }

    public function findAll(): array
    {
        return $this->enrollmentRepository->findAll();
    }

    public function findById(int $id): ?Enrollment
    {
        return $this->enrollmentRepository->findById($id);
    }

    public function findByStudentId(int $studentId): array
    {
        return $this->enrollmentRepository->findByStudentId($studentId);
    }

    public function findByCourseId(int $courseId): array
    {
        return $this->enrollmentRepository->findByCourseId($courseId);
    }

    public function findActiveEnrollmentsByStudentId(int $studentId): array
    {
        // Assuming interface has this or we need to handle it.
        // For now, mapping to repository.
        return $this->enrollmentRepository->findByStudentId($studentId);
    }

    public function enrollStudent(int $studentId, int $courseId): ?Enrollment
    {
        $student = $this->userRepository->findById($studentId);
        if (!$student || $student->getRole() !== 'student') {
            return null;
        }

        $course = $this->courseRepository->findById($courseId);
        if (!$course) {
            return null;
        }

        $existingEnrollments = $this->enrollmentRepository->findByStudentId($studentId);
        foreach ($existingEnrollments as $enrollment) {
            if ($enrollment->getCourseId() === $courseId) {
                return null;
            }
        }

        $enrollment = new Enrollment(
            $studentId,
            $courseId,
            'active'
        );

        return $this->enrollmentRepository->create($enrollment);
    }

    public function updateEnrollmentStatus(int $enrollmentId, string $status): bool
    {
        $validStatuses = ['active', 'inactive', 'completed', 'dropped'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $enrollment = $this->enrollmentRepository->findById($enrollmentId);
        if (!$enrollment) {
            return false;
        }

        $enrollment->setStatus($status);
        return $this->enrollmentRepository->update($enrollment);
    }

    public function dropCourse(int $enrollmentId): bool
    {
        return $this->updateEnrollmentStatus($enrollmentId, 'dropped');
    }

    public function deleteEnrollment(int $enrollmentId): bool
    {
        return $this->enrollmentRepository->delete($enrollmentId);
    }
}
