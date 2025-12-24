<?php
namespace App\Repositories\Interfaces;

use App\Models\Enrollment;

interface EnrollmentRepositoryInterface
{
    public function testConnection(): array;
    public function findAll(): array;
    public function findById(int $id): ?Enrollment;
    public function findByStudentId(int $studentId): array;
    public function findByCourseId(int $courseId): array;
    public function findActiveEnrollmentsByStudentId(int $studentId): array;
    public function create(Enrollment $enrollment): ?Enrollment;
    public function update(Enrollment $enrollment): bool;
    public function delete(int $enrollmentId): bool;
}
