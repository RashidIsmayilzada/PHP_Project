<?php
namespace App\Repositories\Interfaces;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;

interface EnrollmentRepositoryInterface
{
    public function findAll(int $limit = 100, int $offset = 0): array;
    public function findById(int $id): ?Enrollment;
    public function findByStudentId(int $studentId, int $limit = 100, int $offset = 0): array;
    public function findByCourseId(int $courseId, int $limit = 100, int $offset = 0): array;
    public function findByStudentIdAndStatus(
        int $studentId,
        EnrollmentStatus $status,
        int $limit = 100,
        int $offset = 0
    ): array;
    public function findActiveEnrollmentsByStudentId(int $studentId, int $limit = 100, int $offset = 0): array;
    public function create(Enrollment $enrollment): ?Enrollment;
    public function update(Enrollment $enrollment): bool;
    public function delete(int $enrollmentId): bool;
}
