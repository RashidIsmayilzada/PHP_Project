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
    private const VALID_STATUSES = ['active', 'inactive', 'completed', 'dropped'];

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
        return $this->enrollmentRepository->findActiveEnrollmentsByStudentId($studentId);
    }

    public function enrollStudent(int $studentId, int $courseId): ?Enrollment
    {
        if (!$this->isStudent($studentId) || !$this->courseExists($courseId)) {
            return null;
        }

        if ($this->hasEnrollmentForCourse($studentId, $courseId)) {
            return null;
        }

        $enrollment = new Enrollment($studentId, $courseId, 'active');
        $enrollment->setEnrollmentDate(date('Y-m-d H:i:s'));

        return $this->enrollmentRepository->create($enrollment);
    }

    public function updateEnrollmentStatus(int $enrollmentId, string $status): bool
    {
        if (!$this->isValidStatus($status)) {
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

    private function isStudent(int $studentId): bool
    {
        $student = $this->userRepository->findById($studentId);

        return $student !== null && $student->getRole() === 'student';
    }

    private function courseExists(int $courseId): bool
    {
        return $this->courseRepository->findById($courseId) !== null;
    }

    private function hasEnrollmentForCourse(int $studentId, int $courseId): bool
    {
        foreach ($this->enrollmentRepository->findByStudentId($studentId) as $enrollment) {
            if ($enrollment->getCourseId() === $courseId) {
                return true;
            }
        }

        return false;
    }

    private function isValidStatus(string $status): bool
    {
        return in_array($status, self::VALID_STATUSES, true);
    }
}
