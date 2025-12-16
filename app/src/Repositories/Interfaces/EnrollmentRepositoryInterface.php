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
}
